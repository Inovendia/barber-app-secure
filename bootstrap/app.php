<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'resolve.shop' => \App\Http\Middleware\ResolveShop::class,
        ]);

        // ✅ 未認証アクセス時（authミドルウェアが検出）にリダイレクト先を指定
        $middleware->redirectGuestsTo(function ($request) {
            // 管理者エリアなら /admin/login に
            if ($request->is('admin/*')) {
                return route('admin.login');
            }

            // それ以外は（ユーザー用ログインが無ければ）同じく /admin/login に
            return '/admin/login';
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
