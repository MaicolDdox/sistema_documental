<?php

namespace Database\Factories;

use App\Models\ResearchGroup;
use App\Models\ResearchProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResearchProductReportFactory extends Factory
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
            'report_type' => fake()->regexify('[A-Za-z0-9]{100}'),
            'description' => fake()->text(),
            'report_date' => fake()->date(),
        ];
    }
}
