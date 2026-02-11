<?php

namespace Database\Factories;

use App\Models\EngagementType;
use App\Models\EntityPosition;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PersonFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'document_type' => fake()->randomElement(["cc","ti","passport","foreign_id"]),
            'document_number' => fake()->regexify('[A-Za-z0-9]{50}'),
            'gender' => fake()->randomElement(["male","female","other"]),
            'phone' => fake()->phoneNumber(),
            'mobile' => fake()->regexify('[A-Za-z0-9]{20}'),
            'entity_position_id' => EntityPosition::factory(),
            'engagement_type_id' => EngagementType::factory(),
            'training_center_id' => TrainingCenter::factory(),
            'status' => fake()->randomElement(["active","inactive","suspended"]),
        ];
    }
}
