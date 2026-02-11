<?php

namespace Database\Factories;

use App\Models\ResearchProduct;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResearchProductReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'research_product_id' => ResearchProduct::factory(),
            'director_id' => User::factory(),
            'decision' => fake()->randomElement(["approved","rejected","requires_changes"]),
            'notes' => fake()->text(),
            'reviewed_at' => fake()->dateTime(),
        ];
    }
}
