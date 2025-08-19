<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;

use LINE\Clients\MessagingApi\Configuration;
use LINE\Clients\MessagingApi\Api\MessagingApiApi;
use LINE\Clients\MessagingApi\Api\MessagingApiBlobApi;
use LINE\Clients\MessagingApi\Model\CreateRichMenuAliasRequest;
use LINE\Clients\MessagingApi\Model\RichMenuRequest;

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
        $accessToken = config('services.line.channel_access_token');
        if (!$accessToken) {
            $this->error('services.line.channel_access_token が未設定です。');
            return Command::FAILURE;
        }

        $config = Configuration::getDefaultConfiguration()->setAccessToken($accessToken);
        $client = new Client();

        // API クライアント
        $api  = new MessagingApiApi($client, $config);       // JSON系
        $blob = new MessagingApiBlobApi($client, $config);   // 画像などバイナリ系

        // -------- NORMAL --------
        $normal = new RichMenuRequest(json_decode(file_get_contents(base_path($this->option('normalJson'))), true));
        $normalId = $api->createRichMenu($normal)->getRichMenuId();

        $normalImgPath = base_path($this->option('normalImg'));
        $normalMime = @mime_content_type($normalImgPath) ?: 'image/png';
        $blob->setRichMenuImage(
            $normalId,
            new \SplFileObject($normalImgPath),
            null,        // hostIndex
            [],          // variables
            $normalMime  // Content-Type (image/png or image/jpeg)
        );

        $api->updateRichMenuAlias('normal', new CreateRichMenuAliasRequest([
            'richMenuAliasId' => 'normal',
            'richMenuId'      => $normalId,
        ]));


        // -------- VIP --------
        $vip = new RichMenuRequest(json_decode(file_get_contents(base_path($this->option('vipJson'))), true));
        $vipId = $api->createRichMenu($vip)->getRichMenuId();

        $vipImgPath = base_path($this->option('vipImg'));
        $vipMime = @mime_content_type($vipImgPath) ?: 'image/png';
        $blob->setRichMenuImage(
            $vipId,                      // ← バグ防止：vipId を使う
            new \SplFileObject($vipImgPath),
            null,
            [],
            $vipMime
        );

        $api->updateRichMenuAlias('vip', new CreateRichMenuAliasRequest([
            'richMenuAliasId' => 'vip',
            'richMenuId'      => $vipId,
        ]));


        // デフォルトは NORMAL
        $api->setDefaultRichMenu($normalId);

        $this->info("NORMAL created: $normalId (alias: normal)");
        $this->info("VIP created   : $vipId (alias: vip)");

        return Command::SUCCESS;
    }
}
