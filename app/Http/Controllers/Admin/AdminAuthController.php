<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    public function showLoginForm()
    {   echo("gogo")
        return view('admin.login');
    }

    public function login(Request $request)
    {   echo("test")
        $credentials = $request->only('email', 'password');

        $ok = Auth::guard('admin')->attempt($credentials);

        if ($ok) {
            $request->session()->regenerate();

            // 🔽 ここでログイン中の管理者を取得
            $admin = Auth::guard('admin')->user();

            // 🔽 is_password_changed が false ならパスワード変更画面へ
            if (! $admin->is_password_changed) {
                return redirect()->route('admin.password.form'); // 後でルート定義する
            }

            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors([
            'email' => 'ログイン情報が正しくありません',
        ])->onlyInput('email');
    }


    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/admin/login');
    }
}
