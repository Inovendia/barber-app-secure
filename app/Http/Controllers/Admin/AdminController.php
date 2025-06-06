<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\Shop;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\AdminCreatedMail;

class AdminController extends Controller
{
    public function create()
    {
        $shops = Shop::all();
        return view('admin.admins.create', compact('shops'));
    }



    public function store(Request $request)
    {
        $request->validate([
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:admins,email',
            'shop_name' => 'required|string|max:255',
            'shop_address' => 'nullable|string|max:255',
            'shop_phone' => 'nullable|string|max:20',
        ]);

        // 店舗を作成
        $shop = Shop::create([
            'name' => $request->shop_name,
            'address' => $request->shop_address,
            'phone' => $request->shop_phone,
            'business_start' => '09:00:00',
            'business_end' => '18:00:00',
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $generatedPassword = Str::random(10);

        // 管理者を作成
        $admin = Admin::create([
            'name' => $request->admin_name,
            'email' => $request->admin_email,
            'password' => $generatedPassword,
            'shop_id' => $shop->id,
        ]);

        \Log::info('📩 管理者登録試行: ' . $request->admin_email);
        Mail::to($admin->email)->send(new AdminCreatedMail($admin, $generatedPassword));

        return redirect()->route('admin.admins.create')
            ->with('success', '管理者と店舗を登録しました（初期パスワードはメールで送信済みです）');
    }

}
