<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Reservation;

use App\Services\LineRichMenuService;
use App\Services\LineNotificationService;

// ★ 新SDK
use LINE\Clients\MessagingApi\Api\MessagingApiApi;
use LINE\Clients\MessagingApi\Model\ReplyMessageRequest;
use LINE\Clients\MessagingApi\Model\TextMessage;

class LineWebhookController extends Controller
{
    /**
     * Webhookエンドポイント
     * - follow: 初回ユーザーは NORMAL をリンク
     * - message(text: "予約確認"): 直近の予約情報を reply
     * - postback(action=vip_quick): 履歴から予約の簡易リンクを push（または reply）
     */
    public function handle(
        Request $request,
        LineRichMenuService $menu,
        LineNotificationService $notify,
        MessagingApiApi $line  // AppServiceProviderでDI済み
    ) {
        Log::info('LINE Webhook received', $request->all());
        $events = $request->input('events', []);

        foreach ($events as $event) {
            $type   = $event['type']             ?? '';
            $userId = $event['source']['userId'] ?? null;
            $replyToken = $event['replyToken']   ?? null;

            if (!$userId) {
                continue;
            }

            // 1) 初回フォロー → NORMAL をリンク
            if ($type === 'follow') {
                $menu->linkNormal($userId, env('LINE_RICHMENU_NORMAL_ID'));
                continue;
            }

            // 2) テキスト「予約確認」 → 直近確定予約を返信
            if ($type === 'message' && ($event['message']['type'] ?? '') === 'text') {
                $userMessage = trim($event['message']['text'] ?? '');

                if ($userMessage === '予約確認') {
                    $messageText = $this->buildReservationMessage($userId);
                    if ($replyToken) {
                        $line->replyMessage(new ReplyMessageRequest([
                            'replyToken' => $replyToken,
                            'messages'   => [new TextMessage(['type' => 'text', 'text' => $messageText])]
                        ]));
                    }
                }
                continue;
            }

            // 3) postback アクション
            if ($type === 'postback') {
                parse_str($event['postback']['data'] ?? '', $data);

                // VIPメニューの「履歴から予約」
                if (($data['action'] ?? '') === 'vip_quick') {
                    $last = Reservation::where('line_user_id', $userId)
                        ->where('status', 'confirmed')
                        ->latest('reserved_at')
                        ->first();

                    $url = url('/reserve/form?quick=1');

                    $notify->notifyUser($userId, "前回と同じ条件で予約できます：\n{$url}");
                }

                // 🆕 予約確認（リッチメニューから）
                if (($data['action'] ?? '') === 'verify') {
                    $reservation = Reservation::where('line_user_id', $userId)
                        ->where('status', 'confirmed')
                        ->latest('reserved_at')
                        ->first();

                    if ($reservation) {
                        $url = route('reserve.verify', ['token' => $reservation->line_token]);
                        $messageText = "✅ ご予約内容\n\n"
                            . "日時：{$reservation->reserved_at}\n"
                            . "メニュー：{$reservation->menu}\n\n"
                            . "▼ キャンセル・確認はこちら：\n{$url}";
                    } else {
                        $messageText = "現在ご予約は登録されていません。";
                    }

                    if ($replyToken) {
                        $line->replyMessage(new \LINE\Clients\MessagingApi\Model\ReplyMessageRequest([
                            'replyToken' => $replyToken,
                            'messages'   => [new \LINE\Clients\MessagingApi\Model\TextMessage([
                                'type' => 'text',
                                'text' => $messageText
                            ])]
                        ]));
                    }
                }

                continue;
            }
        }

        return response('OK', 200);
    }

    protected $bot;

    public function __construct()
    {
        $this->bot = app('line-bot'); // ServiceProviderでbind済み想定
    }

    public function handle(Request $request)
    {
        $events = $request->input('events', []);

        foreach ($events as $event) {
            $replyToken = $event['replyToken'] ?? null;

            if (($event['type'] ?? '') === 'postback') {
                $data = $event['postback']['data'] ?? '';
                if ($data === 'reservation_check') {
                    $userId = $event['source']['userId'] ?? null;

                    // ユーザーの最新予約を取得
                    $reservation = Reservation::where('line_user_id', $userId)
                        ->latest('reserved_at')
                        ->first();

                    $messageText = $reservation
                        ? "ご予約内容\n日時: {$reservation->reserved_at}\nメニュー: {$reservation->menu}"
                        : "現在、ご予約はありません。";

                    $this->bot->replyMessage(new ReplyMessageRequest([
                        'replyToken' => $replyToken,
                        'messages' => [
                            new TextMessage([
                                'type' => 'text',
                                'text' => $messageText,
                            ]),
                        ],
                    ]));
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
