<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ShopFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->company . '店',
            'address' => $this->faker->address,
            'phone' => $this->faker->phoneNumber,
        ];
    }
}
