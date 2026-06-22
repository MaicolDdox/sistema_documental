<?php

namespace App\Concerns;

use App\Models\User;
use App\Enums\GeneroEnum;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

trait ProfileValidationRules
{
    /**
     * Reglas de validación para el perfil de usuario (users + people).
     *
     * @return array<string, array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>>
     */
    protected function profileRules(?int $userId = null, ?int $personId = null): array
    {
        return [
            'primer_nombre'       => ['required', 'string', 'max:100'],
            'segundo_nombre'      => ['nullable', 'string', 'max:100'],
            'primer_apellido'     => ['required', 'string', 'max:100'],
            'segundo_apellido'    => ['nullable', 'string', 'max:100'],
            'email'               => $this->emailRules($userId),
            'telefono'            => ['nullable', 'string', 'max:20'],
            'celular'             => ['nullable', 'digits_between:7,15'],
            'genero'              => ['nullable', new Enum(GeneroEnum::class)],
            'eps'                 => ['nullable', 'string', 'max:100'],
            'email_institucional' => [
                'nullable',
                'email',
                'max:255',
                $personId === null
                    ? Rule::unique('people', 'email_institucional')
                    : Rule::unique('people', 'email_institucional')->ignore($personId),
            ],
            'entity_position_id'  => ['nullable', 'exists:entity_positions,id'],
            'linkage_type_id'     => ['nullable', 'exists:linkage_types,id'],
            'training_program_id' => ['nullable', 'exists:training_programs,id'],
        ];
    }

    /**
     * Reglas de validación para emails de usuario.
     *
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>
     */
    protected function emailRules(?int $userId = null): array
    {
        return [
            'required',
            'string',
            'email',
            'max:255',
            $userId === null
                ? Rule::unique(User::class)
                : Rule::unique(User::class)->ignore($userId),
        ];
    }
}
