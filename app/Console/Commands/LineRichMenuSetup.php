<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use LINE\Clients\MessagingApi\Api\MessagingApiApi;
use LINE\Clients\MessagingApi\Configuration;
use LINE\Clients\MessagingApi\Model\CreateRichMenuAliasRequest;

class LineRichMenuSetup extends Command
{
    protected $signature = 'line:richmenu:setup
        {--normalJson=storage/app/line/richmenus/normal.json}
        {--vipJson=storage/app/line/richmenus/vip.json}
        {--normalImg=public/line/richmenu-normal.png}
        {--vipImg=public/line/richmenu-vip.png}';

    protected $description = 'Create rich menus (NORMAL/VIP), upload images, set aliases, set default';

    public function handle()
    {
        $config = Configuration::getDefaultConfiguration()
            ->setAccessToken(config('services.line.channel_access_token'));
        $api = new MessagingApiApi(null, $config);

        // NORMAL
        $normal = json_decode(file_get_contents($this->option('normalJson')), true);
        $normalId = $api->createRichMenu($normal)->getRichMenuId();
        $api->setRichMenuImage($normalId, fopen($this->option('normalImg'), 'r'), 'image/png');
        $api->createRichMenuAlias(new CreateRichMenuAliasRequest([
            'richMenuAliasId' => 'normal',
            'richMenuId' => $normalId
        ]));

        // VIP
        $vip = json_decode(file_get_contents($this->option('vipJson')), true);
        $vipId = $api->createRichMenu($vip)->getRichMenuId();
        $api->setRichMenuImage($vipId, fopen($this->option('vipImg'), 'r'), 'image/png');
        $api->createRichMenuAlias(new CreateRichMenuAliasRequest([
            'richMenuAliasId' => 'vip',
            'richMenuId' => $vipId
        ]));

        // デフォルトをNORMALに
        $api->setDefaultRichMenu($normalId);

        $this->info("NORMAL: $normalId (alias: normal)");
        $this->info("VIP   : $vipId (alias: vip)");
    }
}
