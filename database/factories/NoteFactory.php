<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class NoteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'shop_id' => \App\Models\Shop::factory(),
            'content' => $this->faker->realText(50),
            'created_by' => $this->faker->randomElement(['staff', 'system']),
        ];
    }
}
