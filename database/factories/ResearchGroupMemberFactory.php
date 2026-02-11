<?php

namespace Database\Factories;

use App\Models\ResearchGroup;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResearchGroupMemberFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'research_group_id' => ResearchGroup::factory(),
            'researcher_id' => User::factory(),
            'joined_on' => fake()->date(),
            'left_on' => fake()->date(),
            'status' => fake()->randomElement(["active","inactive"]),
            'notes' => fake()->text(),
        ];
    }
}
