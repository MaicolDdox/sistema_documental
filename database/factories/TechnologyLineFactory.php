<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TechnologyLineFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'description' => fake()->text(),
        ];
    }
}
