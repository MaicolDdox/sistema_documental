<?php

namespace Database\Factories;

use App\Models\ResearchProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResearchProductAuthorFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'research_product_id' => ResearchProduct::factory(),
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'mobile' => fake()->regexify('[A-Za-z0-9]{20}'),
            'author_order' => fake()->numberBetween(-10000, 10000),
        ];
    }
}
