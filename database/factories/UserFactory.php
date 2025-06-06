<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'line_user_id' => $this->faker->uuid,
            'name' => $this->faker->name,
            'phone' => $this->faker->phoneNumber,
        ];
    }
}
