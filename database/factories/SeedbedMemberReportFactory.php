<?php

namespace Database\Factories;

use App\Models\Apprentice;
use App\Models\Seedbed;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SeedbedMemberReportFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'seedbed_id' => Seedbed::factory(),
            'apprentice_id' => Apprentice::factory(),
            'advisor_id' => User::factory(),
            'member_type' => fake()->randomElement(["apprentice","advisor","collaborator"]),
            'period' => fake()->regexify('[A-Za-z0-9]{50}'),
            'notes' => fake()->text(),
            'report_date' => fake()->date(),
        ];
    }
}
