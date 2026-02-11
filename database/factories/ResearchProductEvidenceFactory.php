<?php

namespace Database\Factories;

use App\Models\ResearchProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResearchProductEvidenceFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'research_product_id' => ResearchProduct::factory(),
            'file_path' => fake()->regexify('[A-Za-z0-9]{500}'),
            'file_type' => fake()->regexify('[A-Za-z0-9]{50}'),
            'file_size' => fake()->numberBetween(-10000, 10000),
        ];
    }
}
