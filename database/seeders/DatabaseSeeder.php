<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Shop;
use App\Models\Reservation;
use App\Models\Admin;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ✅ Admin（+ shop）を5件作成
        $admins = Admin::factory()->count(5)->create();

        // ✅ ユーザー10人を作成
        User::factory()->count(10)->create();

        // ✅ 管理者が持っている最初の店舗を使って予約など作成
        $shop = $admins->first()->shop;

        // ✅ ユーザー1人目に予約を付ける
        $user = User::first();

        Reservation::create([
            'user_id' => $user->id,
            'shop_id' => $shop->id,
            'menu' => 'カット＆シャンプー',
            'reserved_at' => now()->addDays(3),
            'status' => 'confirmed',
        ]);

        // ✅ 全ユーザーに対して、スタンプとメモを追加（shopは共通）
        foreach (User::all() as $user) {
            \App\Models\Stamp::factory()->count(2)->create([
                'user_id' => $user->id,
                'shop_id' => $shop->id,
            ]);

            \App\Models\Note::factory()->count(1)->create([
                'user_id' => $user->id,
                'shop_id' => $shop->id,
                'created_by' => 'staff',
            ]);
        }
    }
}
