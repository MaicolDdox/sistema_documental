<?php

namespace Database\Factories;

use App\Models\ResearchProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResearchProductDataAuthorizationFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'research_product_id' => ResearchProduct::factory(),
            'authorized' => fake()->boolean(),
            'authorized_at' => fake()->dateTime(),
            'notes' => fake()->text(),
        ];
    }
}
