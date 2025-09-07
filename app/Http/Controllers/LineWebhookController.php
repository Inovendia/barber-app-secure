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
        // 1) 署名検証（公式の手順：HMAC-SHA256 を base64）
        $signature = $request->header('x-line-signature') ?? '';
        $channelSecret = env('LINE_CHANNEL_SECRET'); // Messaging API のチャネルシークレット
        $body = $request->getContent();
        $hash = base64_encode(hash_hmac('sha256', $body, $channelSecret, true));

        if (!hash_equals($signature, $hash)) {
            Log::warning('LINE Webhook: invalid signature');
            // 署名不一致は処理せずエラーで終了（公式推奨）
            return response('invalid signature', 401); // :contentReference[oaicite:6]{index=6}
        }

        // 2) イベント処理
        $payload = $request->json()->all();
        Log::info('LINE Webhook received', $payload);
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

                        if ($replyToken) {
                            $line->replyMessage(new ReplyMessageRequest([
                                'replyToken' => $replyToken,
                                'messages'   => [new TextMessage([
                                    'type' => 'text',
                                    'text' => $messageText,
                                ])],
                            ])); // 返信 API（公式ガイドに準拠）:contentReference[oaicite:7]{index=7}
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
