<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Shop;

class ShopController extends Controller
{
    public function edit()
    {
        $shop = Auth::guard('admin')->user()->shop;
        return view('admin.shop_edit', compact('shop'));
    }

    public function update(Request $request)
    {
        // ログイン中の管理者に紐づく店舗を取得
        $shop = Auth::guard('admin')->user()->shop;

        // 定休日をカンマ区切りの文字列に変換
        $closedDays = $request->input('closed_days', []);
        $shop->closed_days = implode(',', $closedDays);

        $shop->name = $request->input('name');
        $shop->address = $request->input('address');
        $shop->phone = $request->input('phone');

        $shop->business_start = $request->input('business_start');
        $shop->business_end   = $request->input('business_end');
        $shop->break_start    = $request->input('break_start');
        $shop->break_end      = $request->input('break_end');

        $shop->save();

        return back()->with('status', '店舗情報を更新しました。');
    }

}
