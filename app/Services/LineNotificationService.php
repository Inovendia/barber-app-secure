<?php

namespace App\Services;

use LINE\Clients\MessagingApi\Api\MessagingApiApi;
use LINE\Clients\MessagingApi\Model\PushMessageRequest;
use LINE\Clients\MessagingApi\Model\TextMessage;
use LINE\Clients\MessagingApi\Configuration;
use GuzzleHttp\Client;

class LineNotificationService
{
    protected MessagingApiApi $messagingApi;

    public function __construct()
    {
        $config = Configuration::getDefaultConfiguration()
            ->setAccessToken(config('services.line.channel_access_token'));

        $client = new Client();
        $this->messagingApi = new MessagingApiApi($client, $config);
    }

    public function notifyUser(string $userId, string $message): void
    {
        $textMessage = new TextMessage([
            'type' => 'text',
            'text' => $message,
        ]);

        $pushMessageRequest = new PushMessageRequest([
            'to' => $userId,
            'messages' => [$textMessage],
        ]);

        $this->messagingApi->pushMessage($pushMessageRequest);
    }

    public function notifyAdmin(string $message): void
    {
        $adminUserId = config('services.line.admin_user_id');
        if ($adminUserId) {
            $this->notifyUser($adminUserId, $message);
        }
    }
}
