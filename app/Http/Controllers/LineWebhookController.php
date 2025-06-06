<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Reservation;
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;

class LineWebhookController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('Webhook受信:', $request->all());

        $events = $request->input('events', []);

        foreach ($events as $event) {
            if ($event['type'] === 'message' && $event['message']['type'] === 'text') {
                $userMessage = trim($event['message']['text']);
                $replyToken = $event['replyToken'];
                $lineUserId = $event['source']['userId'];

                if ($userMessage === '予約確認') {
                    $this->replyReservationInfo($replyToken, $lineUserId);
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }

    private function replyReservationInfo(string $replyToken, string $lineUserId)
    {
        $reservation = Reservation::where('line_user_id', $lineUserId)
            ->where('status', 'confirmed')
            ->latest('reserved_at')
            ->first();

        if (! $reservation) {
            $message = '現在、確認できるご予約はありません。';
        } else {
            $url = route('reserve.verify', ['token' => $reservation->line_token]);
            $message = "✅ ご予約内容\n\n"
                . "日時：{$reservation->reserved_at}\n"
                . "メニュー：{$reservation->menu}\n\n"
                . "▼ キャンセル・確認はこちら：\n{$url}";
        }

        $httpClient = new CurlHTTPClient(config('services.line.channel_access_token'));
        $bot = new LINEBot($httpClient, ['channelSecret' => config('services.line.channel_secret')]);

        $bot->replyText($replyToken, $message);
    }
}
