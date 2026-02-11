<?php

namespace Database\Factories;

use App\Models\Seedbed;
use App\Models\SeedbedProject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SeedbedProgressReportFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'seedbed_id' => Seedbed::factory(),
            'leader_id' => User::factory(),
            'seedbed_project_id' => SeedbedProject::factory(),
            'report_date' => fake()->date(),
            'reported_period' => fake()->regexify('[A-Za-z0-9]{50}'),
            'progress_percentage' => fake()->randomFloat(2, 0, 999.99),
            'notes' => fake()->text(),
            'recommendations' => fake()->text(),
            'status' => fake()->randomElement(["draft","submitted","reviewed","approved"]),
        ];
    }
}
