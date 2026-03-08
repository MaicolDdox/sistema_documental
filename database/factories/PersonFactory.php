<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Person>
 */
class PersonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'entity_position_id' => 1,    // Debe existir o pasar override
            'linkage_type_id' => 1,       // Debe existir o pasar override
            'training_program_id' => 1,   // Debe existir o pasar override
            'primer_nombre' => fake()->firstName(),
            'segundo_nombre' => fake()->firstName(),
            'primer_apellido' => fake()->lastName(),
            'segundo_apellido' => fake()->lastName(),
            'genero' => fake()->randomElement(['masculino', 'femenino', 'prefiero no decirlo']),
            'telefono' => fake()->numberBetween(1000000, 9999999),
            'celular' => fake()->numberBetween(300000000, 399999999),
            'eps' => fake()->company(),
            'email_institucional' => fake()->unique()->safeEmail(),
        ];
    }
}
