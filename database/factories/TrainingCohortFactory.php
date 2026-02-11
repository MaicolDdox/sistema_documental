<?php

namespace Database\Factories;

use App\Models\TrainingProgram;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrainingCohortFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'cohort_number' => fake()->regexify('[A-Za-z0-9]{50}'),
            'training_program_id' => TrainingProgram::factory(),
            'start_date' => fake()->date(),
            'end_date' => fake()->date(),
            'shift' => fake()->regexify('[A-Za-z0-9]{50}'),
            'status' => fake()->randomElement(["active","finished","canceled"]),
        ];
    }
}
