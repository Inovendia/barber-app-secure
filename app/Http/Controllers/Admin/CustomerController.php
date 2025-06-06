<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Note;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function create()
    {
        return view('admin.customers.customer_create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'note'  => 'nullable|string|max:1000',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $admin = Auth::guard('admin')->user(); // 👈 管理者情報を取得

        $customer = Customer::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'note' => $request->note,
            'shop_id' => $admin->shop_id, // ✅ 店舗情報を付与
        ]);

        // 顧客メモが入力されている場合は notes テーブルにも保存
        if (!empty($request->note)) {
            Note::create([
                'customer_id' => $customer->id,
                'shop_id' => $admin->shop_id,
                'content' => $request->note,
                'created_by' => $admin->name ?? '管理者',
            ]);
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('customer_images', 'public');

                $customer->images()->create([
                    'image_path' => 'customer_images/' . basename($path),
                ]);
            }
        }

        return redirect()->route('admin.dashboard')->with('success', '新規顧客を登録しました');
    }

}
