<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

use GuzzleHttp\Client;
use LINE\Clients\MessagingApi\Api\MessagingApiApi;
use LINE\Clients\MessagingApi\Configuration;

use App\Services\LineNotificationService;
use App\Services\LineRichMenuService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // 1) LINE Messaging API クライアント（共通）をシングルトンで登録
        $this->app->singleton(MessagingApiApi::class, function () {
            $config = Configuration::getDefaultConfiguration()
                ->setAccessToken(config('services.line.channel_access_token'));
            return new MessagingApiApi(new Client(), $config);
        });

        // 2) 通知サービス（既存）をDI化して登録
        $this->app->singleton(LineNotificationService::class, function ($app) {
            return new LineNotificationService(
                $app->make(MessagingApiApi::class)
            );
        });

        // 3) リッチメニューサービス（新規）を登録
        $this->app->singleton(LineRichMenuService::class, function ($app) {
            return new LineRichMenuService(
                $app->make(MessagingApiApi::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (env('FORCE_HTTPS', false)) {
            URL::forceScheme('https');
        }
    }
}
