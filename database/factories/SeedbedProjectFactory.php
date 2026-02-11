<?php

namespace Database\Factories;

use App\Models\ProjectModality;
use App\Models\ResearchLine;
use App\Models\ResearchType;
use App\Models\Seedbed;
use App\Models\TechnologyLine;
use App\Models\ThematicArea;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SeedbedProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'description' => fake()->text(),
            'seedbed_id' => Seedbed::factory(),
            'advisor_id' => User::factory(),
            'research_line_id' => ResearchLine::factory(),
            'technology_line_id' => TechnologyLine::factory(),
            'thematic_area_id' => ThematicArea::factory(),
            'project_modality_id' => ProjectModality::factory(),
            'research_type_id' => ResearchType::factory(),
            'start_date' => fake()->date(),
            'end_date' => fake()->date(),
            'status' => fake()->randomElement(["proposal","in_development","finished","on_hold","canceled"]),
            'linked_to_macro_project' => fake()->boolean(),
            'macro_project_code' => fake()->regexify('[A-Za-z0-9]{100}'),
            'macro_project_name' => fake()->regexify('[A-Za-z0-9]{300}'),
        ];
    }
}
