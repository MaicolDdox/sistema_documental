<?php

namespace Database\Factories;

use App\Models\TrainingCenter;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrainingProgramFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'program_code' => fake()->regexify('[A-Za-z0-9]{50}'),
            'program_type' => fake()->randomElement(["technologist","technician","complementary","specialization","other"]),
            'other_program_type' => fake()->regexify('[A-Za-z0-9]{100}'),
            'training_center_id' => TrainingCenter::factory(),
            'status' => fake()->randomElement(["active","inactive"]),
        ];
    }
}
