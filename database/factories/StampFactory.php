<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StampFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'shop_id' => \App\Models\Shop::factory(),
            'visit_date' => $this->faker->dateTimeBetween('-2 months', 'now'),
            'reward_claimed' => $this->faker->boolean(20), // 20%の確率でtrue
        ];
    }
}

