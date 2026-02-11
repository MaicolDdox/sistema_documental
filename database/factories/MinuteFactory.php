<?php

namespace Database\Factories;

use App\Models\Seedbed;
use Illuminate\Database\Eloquent\Factories\Factory;

class MinuteFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'seedbed_id' => Seedbed::factory(),
            'document_name' => fake()->regexify('[A-Za-z0-9]{200}'),
            'file_path' => fake()->regexify('[A-Za-z0-9]{500}'),
            'description' => fake()->text(),
            'document_date' => fake()->date(),
        ];
    }
}
