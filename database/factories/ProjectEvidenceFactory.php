<?php

namespace Database\Factories;

use App\Models\SeedbedProject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectEvidenceFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'seedbed_project_id' => SeedbedProject::factory(),
            'user_id' => User::factory(),
            'evidence_name' => fake()->regexify('[A-Za-z0-9]{200}'),
            'file_path' => fake()->regexify('[A-Za-z0-9]{500}'),
            'file_type' => fake()->randomElement(["pdf","excel","word","image","video","other"]),
            'description' => fake()->text(),
        ];
    }
}
