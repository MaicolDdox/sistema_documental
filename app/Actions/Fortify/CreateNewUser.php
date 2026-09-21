<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Enums\EstadoEnum;
use App\Enums\TipoDocumentoEnum;
use App\Models\Person;
use App\Models\User;
use App\Services\Admin\RoleAssignmentService;
use App\Support\RoleAssignmentMatrix;
use App\Support\TrainingCenterAccess;
use Illuminate\Support\Facades\Auth;
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
        $auth = Auth::user();
        $assignableRoles = RoleAssignmentMatrix::assignableRolesFor($auth);

        $trainingCenterRules = ['nullable', Rule::exists('training_centers', 'id')->where('activo', true)];
        $roleName = $input['role'] ?? '';
        if (TrainingCenterAccess::roleRequiresTrainingCenter($roleName)) {
            $trainingCenterRules = ['required', Rule::exists('training_centers', 'id')->where('activo', true)];
        }

        // Centro "real" que tendrá el usuario nuevo: el propio del actor si
        // no es super_administrador (se le ignora cualquier centro que haya
        // enviado, ver más abajo), o el que el super_administrador eligió en
        // el request. Los catálogos (entity_position_id, etc.) deben
        // validarse contra ESE centro, no contra uno arbitrario.
        $allowedCenterIds = TrainingCenterAccess::allowedCenterIdsForSave($auth);
        $catalogCenterId = $allowedCenterIds !== null ? $allowedCenterIds[0] : ($input['training_center_id'] ?? null);

        Validator::make($input, [
            // Datos de users
            'training_center_id' => $trainingCenterRules,
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

            // Rol (Spatie) — restringido a la matriz de creación exclusiva del actor
            'role' => ['required', 'string', 'exists:roles,name', Rule::in($assignableRoles)],

            // Relaciones opcionales — catálogos vinculados por centro
            'entity_position_id' => ['nullable', Rule::exists('entity_positions', 'id')->where('training_center_id', $catalogCenterId)],
            'linkage_type_id' => ['nullable', Rule::exists('linkage_types', 'id')->where('training_center_id', $catalogCenterId)],
            'training_program_id' => ['nullable', Rule::exists('training_programs', 'id')->where('training_center_id', $catalogCenterId)],
        ])->validate();

        // Reforma GDI/SDI: ya no existe ningún rol asignable desde aquí sin
        // centro de formación propio (co_investigador_gdi y co_investigador_sdi
        // también lo exigen ahora) — se hereda siempre el del actor.
        if ($auth && ! TrainingCenterAccess::isSuperAdmin($auth)) {
            $allowed = TrainingCenterAccess::allowedCenterIdsForSave($auth);
            if ($allowed !== null) {
                $input['training_center_id'] = $allowed[0];
            }
        }

        return DB::transaction(function () use ($input, $auth) {
            // Crear usuario con estado inactivo por defecto
            $user = User::create([
                'training_center_id' => $input['training_center_id'] ?? null,
                'created_by_user_id' => $auth?->id(),
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

            // Asignar rol con Spatie — BUG-20260914-006: centralizado en RoleAssignmentService.
            app(RoleAssignmentService::class)->assign($user, $input['role'], $auth, 'training_center_id');
            $user->forceFill(['primary_role_name' => $input['role']])->saveQuietly();

            return $user;
        });
    }
}
