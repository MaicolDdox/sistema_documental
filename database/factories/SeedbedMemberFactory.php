<?php

namespace Database\Factories;

use App\Models\Seedbed;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SeedbedMemberFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'seedbed_id' => Seedbed::factory(),
            'user_id' => User::factory(),
            'seedbed_role' => fake()->randomElement(["leader","advisor","collaborator"]),
            'joined_on' => fake()->date(),
            'left_on' => fake()->date(),
            'status' => fake()->randomElement(["active","inactive"]),
        ];
    }
}
