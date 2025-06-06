<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * ログに報告しない例外のリスト
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * バリデーション例外などでフラッシュしない入力のキー
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * 例外レポートの登録。
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * 未認証時の処理をオーバーライド。
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        // APIリクエストならJSONで返す
        if ($request->expectsJson()) {
            return response()->json(['message' => $exception->getMessage()], 401);
        }

        // どのガードか判定し、リダイレクト先を分岐
        $guard = $exception->guards()[0] ?? null;

        switch ($guard) {
            case 'admin':
                $login = route('admin.login');
                break;
            default:
                $login = route('login');
                break;
        }

        return redirect()->guest($login);
    }
}
