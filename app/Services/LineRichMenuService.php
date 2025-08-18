<?php

// app/Services/LineRichMenuService.php
namespace App\Services;

use LINE\Clients\MessagingApi\Api\MessagingApiApi;
use LINE\Clients\MessagingApi\Model\CreateRichMenuAliasRequest;
use App\Models\Reservation;

class LineRichMenuService
{
    public function __construct(private MessagingApiApi $api) {}

    public function linkNormal(string $userId, string $normalId): void
    {
        $this->api->linkRichMenuIdToUser($userId, $normalId);
    }

    public function linkVip(string $userId, string $vipId): void
    {
        $this->api->linkRichMenuIdToUser($userId, $vipId);
    }

    public function linkByHistory(string $userId, string $normalId, string $vipId): void
    {
        $count = Reservation::where('line_user_id', $userId)->count();
        $this->api->linkRichMenuIdToUser($userId, $count > 0 ? $vipId : $normalId);
    }

    // セットアップ（必要なら）
    public function createAndAlias(array $normalJson, string $normalImg, array $vipJson, string $vipImg): array
    {
        $nId = $this->api->createRichMenu($normalJson)->getRichMenuId();
        $this->api->setRichMenuImage($nId, fopen($normalImg, 'r'), 'image/png');
        $this->api->createRichMenuAlias(new CreateRichMenuAliasRequest([
            'richMenuAliasId' => 'normal', 'richMenuId' => $nId,
        ]));

        $vId = $this->api->createRichMenu($vipJson)->getRichMenuId();
        $this->api->setRichMenuImage($vId, fopen($vipImg, 'r'), 'image/png');
        $this->api->createRichMenuAlias(new CreateRichMenuAliasRequest([
            'richMenuAliasId' => 'vip', 'richMenuId' => $vId,
        ]));

        $this->api->setDefaultRichMenu($nId);
        return ['normal' => $nId, 'vip' => $vId];
    }
}
