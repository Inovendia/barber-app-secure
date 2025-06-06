<?php

namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class AdminChangePasswordController extends Controller
{
    public function showForm()
    {
        return view('admin.change_password');
    }

    public function update(Request $request)
    {
        $request->validate([
            'password' => 'required|confirmed|min:6',
        ]);

        $admin = Auth::guard('admin')->user();

        $admin->password = $request->password;
        $admin->is_password_changed = true;
        $admin->save();

        Auth::guard('admin')->login($admin);

        return redirect()->route('admin.dashboard')->with('success', 'パスワードを変更しました');
    }
}
