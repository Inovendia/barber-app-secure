<?php

namespace App\Services;

use LINE\Clients\MessagingApi\Configuration;
use LINE\Clients\MessagingApi\Api\MessagingApiApi;
use LINE\Clients\MessagingApi\Model\PushMessageRequest;
use LINE\Clients\MessagingApi\Model\TextMessage;
use App\Models\Shop;

class LineNotificationService
{
    /**
     * 店舗ごとのアクセストークンで MessagingApiApi を生成
     */
    private function getMessagingApi(string $accessToken): MessagingApiApi
    {
        $config = new Configuration();
        $config->setAccessToken($accessToken);
        return new MessagingApiApi(null, $config);
    }

    /**
     * ユーザーに通知
     */
    public function notifyUser(Shop $shop, string $userId, string $message): void
    {
        $messagingApi = $this->getMessagingApi($shop->line_access_token);

        \Log::debug('LINE push start', [
            'shop_id' => $shop->id,
            'to' => $userId,
            'access_token' => substr($shop->line_access_token, 0, 10) . '...' // 確認用
        ]);

        $text = new TextMessage(['type' => 'text', 'text' => $message]);
        $req  = new PushMessageRequest([
            'to' => $userId,
            'messages' => [$text]
        ]);
        $messagingApi->pushMessage($req);
    }

    /**
     * 管理者に通知
     */
    public function notifyAdmin(Shop $shop, string $message): void
    {
        if ($adminUserId = config('services.line.admin_user_id')) {
            $this->notifyUser($shop, $adminUserId, $message);
        }
    }
}
