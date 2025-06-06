<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Shop;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Admin>
 */
class AdminFactory extends Factory
{
    public function definition(): array
    {
        return [
            'shop_id' => Shop::factory(), // 🔥 店舗も同時に作成して紐づけ
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password'), // 🔐 固定パスワード（ログインテスト用）
        ];
    }
}
