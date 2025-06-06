<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // 追加：URLファサード
use App\Services\LineNotificationService; // 追加：LineNotificationServiceのインポート

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // LineNotificationServiceのインスタンスをシングルトンで登録
        $this->app->singleton(LineNotificationService::class, function ($app) {
            return new LineNotificationService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // HTTPSを強制（フォームやasset、route()出力などに影響）
        if (env('FORCE_HTTPS', false)) {
            URL::forceScheme('https');
        }
    }
}
