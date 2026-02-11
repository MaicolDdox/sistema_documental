<?php

namespace Database\Factories;

use App\Models\ResearchGroup;
use App\Models\ResearchProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResearchGroupMinuteFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'research_group_id' => ResearchGroup::factory(),
            'research_product_id' => ResearchProduct::factory(),
            'file_path' => fake()->regexify('[A-Za-z0-9]{500}'),
            'description' => fake()->text(),
            'minute_date' => fake()->date(),
            'minute_type' => fake()->regexify('[A-Za-z0-9]{100}'),
        ];
    }
}
