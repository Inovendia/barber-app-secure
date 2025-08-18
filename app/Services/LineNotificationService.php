<?php

// app/Services/LineNotificationService.php
namespace App\Services;

use LINE\Clients\MessagingApi\Api\MessagingApiApi;
use LINE\Clients\MessagingApi\Model\PushMessageRequest;
use LINE\Clients\MessagingApi\Model\TextMessage;

class LineNotificationService
{
    public function __construct(private MessagingApiApi $messagingApi) {}

    public function notifyUser(string $userId, string $message): void
    {
        $text = new TextMessage(['type' => 'text', 'text' => $message]);
        $req  = new PushMessageRequest(['to' => $userId, 'messages' => [$text]]);
        $this->messagingApi->pushMessage($req);
    }

    public function notifyAdmin(string $message): void
    {
        if ($adminUserId = config('services.line.admin_user_id')) {
            $this->notifyUser($adminUserId, $message);
        }
    }
}

