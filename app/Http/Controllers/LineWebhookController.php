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

            // 3) VIPメニューの「履歴から予約」 → 簡易リンクを通知
            if ($type === 'postback') {
                parse_str($event['postback']['data'] ?? '', $data);
                if (($data['action'] ?? '') === 'vip_quick') {
                    // 直近予約を元にクエリ生成（例）
                    $last = Reservation::where('line_user_id', $userId)
                        ->where('status', 'confirmed')
                        ->latest('reserved_at')
                        ->first();

                    // 実運用では $last のメニュー/所要時間をクエリに埋めてプリセット
                    $url = url('/reserve/form?quick=1');

                    $notify->notifyUser($userId, "前回と同じ条件で予約できます：\n{$url}");
                }
                continue;
            }
        }

        return response('OK', 200);
    }

    /**
     * 直近の確定予約から返信文を作る
     */
    private function buildReservationMessage(string $lineUserId): string
    {
        $reservation = Reservation::where('line_user_id', $lineUserId)
            ->where('status', 'confirmed')
            ->latest('reserved_at')
            ->first();

        if (! $reservation) {
            return '現在、確認できるご予約はありません。';
        }

        // ここはあなたの実装に合わせて調整（token/メニュー項目名など）
        $url = route('reserve.verify', ['token' => $reservation->line_token]);

        return "✅ ご予約内容\n\n"
            . "日時：{$reservation->reserved_at}\n"
            . "メニュー：{$reservation->menu}\n\n"
            . "▼ キャンセル・確認はこちら：\n{$url}";
    }
}
