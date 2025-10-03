<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;

use LINE\Clients\MessagingApi\Configuration;
use LINE\Clients\MessagingApi\Api\MessagingApiApi;
use LINE\Clients\MessagingApi\Api\MessagingApiBlobApi;
use LINE\Clients\MessagingApi\Model\CreateRichMenuAliasRequest;
use LINE\Clients\MessagingApi\Model\RichMenuRequest;
use App\Models\Shop;

class LineRichMenuSetup extends Command
{
    protected $signature = 'line:richmenu:setup
        {--shop= : Shop ID to setup richmenu}
        {--normalJson=storage/app/line/richmenus/normal.json}
        {--vipJson=storage/app/line/richmenus/vip.json}
        {--normalImg=public/line/richmenu-normal.png}
        {--vipImg=public/line/richmenu-vip.png}';

    protected $description = 'Create rich menus (NORMAL/VIP), upload images, set aliases, set default';

    public function handle()
{
    $shopId = $this->option('shop');
    if (!$shopId) {
        $this->error('--shop オプションで店舗IDを指定してください');
        return Command::FAILURE;
    }

    $shop = Shop::find($shopId);
    if (!$shop) {
        $this->error("Shop {$shopId} が見つかりません");
        return Command::FAILURE;
    }

    $accessToken = $shop->line_access_token;
    if (!$accessToken) {
        $this->error("Shop {$shopId} の line_access_token が未設定です");
        return Command::FAILURE;
    }

    $config = Configuration::getDefaultConfiguration()->setAccessToken($accessToken);
    $client = new Client();

    $api  = new MessagingApiApi($client, $config);
    $blob = new MessagingApiBlobApi($client, $config);

    // -------- NORMAL --------
    $normal = new RichMenuRequest(json_decode(file_get_contents(base_path($this->option('normalJson'))), true));
    $normalId = $api->createRichMenu($normal)->getRichMenuId();

    $normalImgPath = base_path($this->option('normalImg'));
    $normalMime = @mime_content_type($normalImgPath) ?: 'image/png';
    $blob->setRichMenuImage(
        $normalId,
        new \SplFileObject($normalImgPath),
        null,
        [],
        $normalMime
    );

    $api->createRichMenuAlias(new CreateRichMenuAliasRequest([
        'richMenuAliasId' => 'normal',
        'richMenuId'      => $normalId,
    ]));

    // -------- VIP --------
    $vip = new RichMenuRequest(json_decode(file_get_contents(base_path($this->option('vipJson'))), true));
    $vipId = $api->createRichMenu($vip)->getRichMenuId();

    $vipImgPath = base_path($this->option('vipImg'));
    $vipMime = @mime_content_type($vipImgPath) ?: 'image/png';
    $blob->setRichMenuImage(
        $vipId,
        new \SplFileObject($vipImgPath),
        null,
        [],
        $vipMime
    );

    $api->createRichMenuAlias(new CreateRichMenuAliasRequest([
        'richMenuAliasId' => 'vip',
        'richMenuId'      => $vipId,
    ]));

    // デフォルトを NORMAL に設定
    $api->setDefaultRichMenu($normalId);

    $this->info("Shop {$shopId} ({$shop->name}) にリッチメニューを設定しました");
    $this->info("NORMAL created: $normalId (alias: normal)");
    $this->info("VIP created   : $vipId (alias: vip)");

    return Command::SUCCESS;
}

}
