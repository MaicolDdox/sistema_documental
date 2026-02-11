<?php

namespace Database\Factories;

use App\Models\MincienciasTypology;
use App\Models\ProductType;
use App\Models\ResearchGroup;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResearchProductFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'investigator_id' => User::factory(),
            'research_group_id' => ResearchGroup::factory(),
            'project_origin_type' => fake()->randomElement(["sgps","installed_capacity","formative_research","center_initiative","allied_entity_articulation"]),
            'origin_project_code' => fake()->regexify('[A-Za-z0-9]{100}'),
            'minciencias_typology_id' => MincienciasTypology::factory(),
            'product_type_id' => ProductType::factory(),
            'name' => fake()->name(),
            'description' => fake()->text(),
            'publication_year' => fake()->year(),
            'training_program_name' => fake()->regexify('[A-Za-z0-9]{200}'),
            'has_repository' => fake()->boolean(),
            'repository_url' => fake()->regexify('[A-Za-z0-9]{500}'),
            'review_status' => fake()->randomElement(["draft","submitted","in_review","approved","rejected"]),
            'submitted_at' => fake()->dateTime(),
            'reviewed_at' => fake()->dateTime(),
        ];
    }
}
