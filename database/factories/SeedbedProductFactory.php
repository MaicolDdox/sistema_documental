<?php

namespace Database\Factories;

use App\Models\SeedbedProject;
use Illuminate\Database\Eloquent\Factories\Factory;

class SeedbedProductFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'seedbed_project_id' => SeedbedProject::factory(),
            'name' => fake()->name(),
            'product_type' => fake()->randomElement(["article","presentation","prototype","software","certificate","patent","registration","other"]),
            'description' => fake()->text(),
            'file_path' => fake()->regexify('[A-Za-z0-9]{500}'),
            'obtained_on' => fake()->date(),
            'status' => fake()->randomElement(["in_progress","completed","published"]),
        ];
    }
}
