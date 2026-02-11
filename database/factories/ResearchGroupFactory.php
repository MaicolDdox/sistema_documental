<?php

namespace Database\Factories;

use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResearchGroupFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'group_code' => fake()->regexify('[A-Za-z0-9]{50}'),
            'name' => fake()->name(),
            'description' => fake()->text(),
            'logo_path' => fake()->regexify('[A-Za-z0-9]{255}'),
            'director_id' => User::factory(),
            'training_center_id' => TrainingCenter::factory(),
            'status' => fake()->randomElement(["active","inactive","under_review"]),
            'founded_on' => fake()->date(),
        ];
    }
}
