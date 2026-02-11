<?php

namespace Database\Factories;

use App\Models\Seedbed;
use App\Models\SeedbedProject;
use App\Models\TrainingCohort;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApprenticeFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'full_name' => fake()->regexify('[A-Za-z0-9]{200}'),
            'document_type' => fake()->randomElement(["cc","ti","foreign_id"]),
            'document_number' => fake()->regexify('[A-Za-z0-9]{50}'),
            'gender' => fake()->randomElement(["male","female","other"]),
            'birth_date' => fake()->date(),
            'phone' => fake()->phoneNumber(),
            'mobile' => fake()->regexify('[A-Za-z0-9]{20}'),
            'email' => fake()->safeEmail(),
            'blood_type' => fake()->regexify('[A-Za-z0-9]{10}'),
            'health_provider' => fake()->regexify('[A-Za-z0-9]{100}'),
            'training_cohort_id' => TrainingCohort::factory(),
            'support_type' => fake()->regexify('[A-Za-z0-9]{200}'),
            'seedbed_id' => Seedbed::factory(),
            'seedbed_project_id' => SeedbedProject::factory(),
            'project_file_path' => fake()->regexify('[A-Za-z0-9]{500}'),
            'status' => fake()->randomElement(["active","inactive","graduated","withdrawn"]),
            'admission_date' => fake()->date(),
            'withdrawal_date' => fake()->date(),
        ];
    }
}
