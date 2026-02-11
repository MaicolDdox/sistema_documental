<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TrainingCenterFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'code' => fake()->regexify('[A-Za-z0-9]{50}'),
            'city' => fake()->city(),
            'department' => fake()->regexify('[A-Za-z0-9]{100}'),
        ];
    }
}
