<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Session;

class AdminPasswordResetNoEmailController extends Controller
{
    /**
     * Step1: ID（email）入力画面
     */
    public function showEmailForm()
    {
        return view('admin.passwords.noemail_forgot');
    }

    /**
     * Step2: email照合 → セッション保存 → リセット画面へ
     */
    public function checkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $admin = Admin::where('email', $request->email)->first();

        if (!$admin) {
            return back()->withErrors(['email' => '登録されているID（メールアドレス）ではありません']);
        }

        // セッションに email を保存
        Session::put('admin_reset_email', $admin->email);

        return redirect()->route('admin.password.reset.form');
    }

    /**
     * Step3: パスワード再設定フォーム
     */
    public function showResetForm()
    {
        // セッションから email を取得
        $email = Session::get('admin_reset_email');

        if (!$email) {
            return redirect()->route('admin.password.forgot')
                             ->withErrors(['email' => '最初にIDを入力してください']);
        }

        return view('admin.passwords.noemail_reset', compact('email'));
    }

    /**
     * Step4: パスワード更新処理
     */
    public function reset(Request $request)
    {
        $request->validate([
            'password' => 'required|confirmed|min:6'
        ]);

        $email = Session::get('admin_reset_email');

        if (!$email) {
            return redirect()->route('admin.password.forgot')
                             ->withErrors(['email' => 'ID入力からやり直してください']);
        }

        $admin = Admin::where('email', $email)->firstOrFail();
        $admin->password = $request->password;
        $admin->is_password_changed = true;
        $admin->save();

        // セッションクリア
        Session::forget('admin_reset_email');

        return redirect()->route('admin.login')
                         ->with('status', 'パスワードを更新しました');
    }
}
