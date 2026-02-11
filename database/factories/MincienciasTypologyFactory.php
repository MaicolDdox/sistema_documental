<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MincienciasTypologyFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'description' => fake()->text(),
            'code' => fake()->regexify('[A-Za-z0-9]{50}'),
        ];
    }
}
