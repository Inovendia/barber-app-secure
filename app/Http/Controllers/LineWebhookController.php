<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Reservation;
use App\Services\LineRichMenuService;
use App\Services\LineNotificationService;

use LINE\Clients\MessagingApi\Api\MessagingApiApi;
use LINE\Clients\MessagingApi\Model\ReplyMessageRequest;
use LINE\Clients\MessagingApi\Model\TextMessage;

class LineWebhookController extends Controller
{
    public function handle(
        Request $request,
        LineRichMenuService $menu,
        LineNotificationService $notify,
        MessagingApiApi $line  // AppServiceProvider で DI
    ) {
        \Log::debug('LINE Webhook Event', $request->all());

        // 1) 署名検証（公式の手順：HMAC-SHA256 を base64）
        // 1) 署名検証（複数のチャネルシークレットを許可）
        $signature = $request->header('x-line-signature') ?? '';
        $body = $request->getContent();

        $valid = false;
        $secrets = [
            env('LINE_CHANNEL_SECRET'),       // 本番
            env('LINE_CHANNEL_SECRET_TEST'),  // テスト
            // 追加するなら: env('LINE_CHANNEL_SECRET_SHOP1'), ...
        ];

        foreach ($secrets as $secret) {
            if (!$secret) continue; // 空はスキップ
            $hash = base64_encode(hash_hmac('sha256', $body, $secret, true));
            if (hash_equals($signature, $hash)) {
                $valid = true;
                break;
            }
        }

        if (!$valid) {
            Log::warning('LINE Webhook: invalid signature', [
                'signature' => $signature,
                'secrets'   => array_filter($secrets),
            ]);
            return response('invalid signature', 401);
        }


        // 2) イベント処理
        $payload = $request->json()->all();
        Log::info('LINE Webhook received', $payload);

        $destination = $payload['destination'] ?? null;
        $shop = null;
        if ($destination) {
            $shop = \App\Models\Shop::where('line_channel_id', $destination)->first();
            Log::debug('Webhook resolved shop', [
                'destination' => $destination,
                'shop_id'     => $shop?->id,
                'shop_name'   => $shop?->name,
            ]);
        }
        $events = $payload['events'] ?? [];

        foreach ($events as $event) {
            try {
                $type       = $event['type']             ?? '';
                $userId     = $event['source']['userId'] ?? null;
                $replyToken = $event['replyToken']       ?? null;

                if (!$userId) {
                    continue;
                }

                // follow: 初回は NORMAL をリンク
                if ($type === 'follow') {
                    $menu->linkNormal($userId, env('LINE_RICHMENU_NORMAL_ID'));
                    continue;
                }

                // message(text)
                if ($type === 'message' && ($event['message']['type'] ?? '') === 'text') {
                    $userMessage = trim($event['message']['text'] ?? '');

                    if ($userMessage === '予約確認') {
                        $reservation = Reservation::where('line_user_id', $userId)
                            ->where('status', 'confirmed')
                            ->latest('reserved_at')
                            ->first();

                        $messageText = $reservation
                            ? "ご予約内容\n日時: {$reservation->reserved_at}\nメニュー: {$reservation->menu}"
                            : "現在、ご予約はありません。";

                            if ($replyToken && $shop?->line_access_token) {
                                $config = new Configuration();
                                $config->setAccessToken($shop->line_access_token);
                                $lineClient = new MessagingApiApi(config: $config);
                                $lineClient->replyMessage(new ReplyMessageRequest([
                                    'replyToken' => $replyToken,
                                    'messages'   => [new TextMessage([
                                        'type' => 'text',
                                        'text' => $messageText,
                                    ])],
                                ]));
                            }
                    }
                    continue;
                }

                // postback
                if ($type === 'postback') {
                    parse_str($event['postback']['data'] ?? '', $data);

                    // 履歴から予約（VIP）
                    if (($data['action'] ?? '') === 'vip_quick') {
                        // 前回の確定予約（必要なら値の利用）
                        $last = Reservation::where('line_user_id', $userId)
                            ->where('status', 'confirmed')
                            ->latest('reserved_at')
                            ->first();

                        $url = url('/reserve/form?quick=1');
                        $notify->notifyUser($userId, "前回と同じ条件で予約できます：\n{$url}");
                    }

                    // 予約確認（リッチメニューから）
                    if (($data['action'] ?? '') === 'verify') {
                        $reservation = Reservation::where('line_user_id', $userId)
                            ->where('status', 'confirmed')
                            ->latest('reserved_at')
                            ->first();

                        if ($reservation) {
                            $url = route('reserve.verify') . '?token=' . urlencode($reservation->line_token);
                            $messageText = "✅ ご予約内容\n\n"
                                . "日時：{$reservation->reserved_at}\n"
                                . "メニュー：{$reservation->menu}\n\n"
                                . "▼ キャンセル・確認はこちら：\n{$url}";
                        } else {
                            $messageText = "現在ご予約は登録されていません。";
                        }

                        if ($replyToken) {
                            $line->replyMessage(new ReplyMessageRequest([
                                'replyToken' => $replyToken,
                                'messages'   => [new TextMessage([
                                    'type' => 'text',
                                    'text' => $messageText,
                                ])],
                            ]));
                        }
                    }
                }

            } catch (\LINE\Clients\MessagingApi\ApiException $e) {
                // LINE API からのエラーはログに残して継続
                Log::error('LINE Messaging API error', [
                    'status' => $e->getCode(),
                    'body'   => $e->getResponseBody(),
                    'headers'=> $e->getResponseHeaders(),
                ]);
                // ここで例外を投げ直さない → webhook 自体は 200 を返す
            } catch (\Throwable $e) {
                Log::error('LINE Webhook handler error', ['e' => $e]);
            }
        }

        // 3) 常に 2xx を速やかに返す（タイムアウト/再送を避ける）
        return response()->json(['status' => 'ok'], 200);
    }
}
