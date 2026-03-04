<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Enums\EstadoEnum;
use App\Enums\TipoDocumentoEnum;
use App\Models\Person;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Valida y crea un nuevo usuario con su perfil de persona.
     * Usado exclusivamente por el Admin desde el panel de administración.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            // Datos de users
            'training_center_id' => ['nullable', 'exists:training_centers,id'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique(User::class)],
            'tipo_documento' => ['required', Rule::enum(TipoDocumentoEnum::class)],
            'numero_documento' => ['required', 'integer', 'unique:users,numero_documento'],
            'password' => $this->passwordRules(),

            // Datos de people
            'primer_nombre' => ['required', 'string', 'max:100'],
            'segundo_nombre' => ['nullable', 'string', 'max:100'],
            'primer_apellido' => ['required', 'string', 'max:100'],
            'segundo_apellido' => ['nullable', 'string', 'max:100'],
            'genero' => ['nullable', 'string'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'celular' => ['nullable', 'string', 'max:20'],
            'eps' => ['nullable', 'string', 'max:255'],
            'email_institucional' => ['nullable', 'email', 'max:255', Rule::unique(Person::class)],

            // Rol (Spatie)
            'role' => ['required', 'string', 'exists:roles,name'],

            // Relaciones opcionales
            'entity_position_id' => ['nullable', 'exists:entity_positions,id'],
            'linkage_type_id' => ['nullable', 'exists:linkage_types,id'],
            'training_program_id' => ['nullable', 'exists:training_programs,id'],
        ])->validate();

        return DB::transaction(function () use ($input) {
            // Crear usuario con estado inactivo por defecto
            $user = User::create([
                'training_center_id' => $input['training_center_id'] ?? null,
                'email' => $input['email'] ?? null,
                'tipo_documento' => $input['tipo_documento'],
                'numero_documento' => $input['numero_documento'],
                'password' => $input['password'],
                'estado' => EstadoEnum::Inactivo,
            ]);

            // Crear perfil de persona
            Person::create([
                'user_id' => $user->id,
                'entity_position_id' => $input['entity_position_id'] ?? null,
                'linkage_type_id' => $input['linkage_type_id'] ?? null,
                'training_program_id' => $input['training_program_id'] ?? null,
                'primer_nombre' => $input['primer_nombre'],
                'segundo_nombre' => $input['segundo_nombre'] ?? '',
                'primer_apellido' => $input['primer_apellido'],
                'segundo_apellido' => $input['segundo_apellido'] ?? '',
                'genero' => $input['genero'] ?? null,
                'telefono' => $input['telefono'] ?? null,
                'celular' => $input['celular'] ?? null,
                'eps' => $input['eps'] ?? null,
                'email_institucional' => $input['email_institucional'] ?? null,
            ]);

            // Asignar rol con Spatie
            $user->assignRole($input['role']);

            return $user;
        });
    }
}
