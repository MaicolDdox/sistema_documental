<?php

namespace App\Livewire\Admin\Users;

use App\Enums\EstadoEnum;
use App\Enums\TipoDocumentoEnum;
use App\Models\User;
use App\Support\RoleModuleLinks;
use App\Support\TrainingCenterAccess;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Livewire\Component;

class UserEdit extends Component
{
    public User $user;

    // User fields
    public ?int $training_center_id = null;
    public string $email = '';
    public string $tipo_documento = '';
    public string $numero_documento = '';
    public string $estado = '';

    // Person fields
    public string $primer_nombre = '';
    public string $segundo_nombre = '';
    public string $primer_apellido = '';
    public string $segundo_apellido = '';
    public string $genero = '';
    public string $telefono = '';
    public string $celular = '';
    public string $eps = '';
    public string $email_institucional = '';

    // Role
    public string $role = '';

    // Optional relations
    public ?int $entity_position_id = null;
    public ?int $linkage_type_id = null;
    public ?int $training_program_id = null;

    public function mount(User $user): void
    {
        $this->user = $user->load('person', 'roles');

        $auth = auth()->user();
        if (! TrainingCenterAccess::isSuperAdmin($auth)) {
            if ($user->hasRole('super_administrador')) {
                abort(403);
            }
            if ($user->hasRole('administrador_sistema') && $auth->id !== $user->id) {
                abort(403);
            }
        }

        $this->training_center_id = $user->training_center_id;
        $this->email = $user->email ?? '';
        $this->tipo_documento = $user->tipo_documento?->value ?? '';
        $this->numero_documento = (string) $user->numero_documento;
        $this->estado = $user->estado->value;

        if ($user->person) {
            $this->primer_nombre = $user->person->primer_nombre ?? '';
            $this->segundo_nombre = $user->person->segundo_nombre ?? '';
            $this->primer_apellido = $user->person->primer_apellido ?? '';
            $this->segundo_apellido = $user->person->segundo_apellido ?? '';
            $this->genero = $user->person->genero?->value ?? '';
            $this->telefono = $user->person->telefono ?? '';
            $this->celular = $user->person->celular ?? '';
            $this->eps = $user->person->eps ?? '';
            $this->email_institucional = $user->person->email_institucional ?? '';
            $this->entity_position_id = $user->person->entity_position_id;
            $this->linkage_type_id = $user->person->linkage_type_id;
            $this->training_program_id = $user->person->training_program_id;
        }

        $roleNames = $user->roles->pluck('name')->all();
        $this->role = ($user->primary_role_name && in_array($user->primary_role_name, $roleNames, true))
            ? $user->primary_role_name
            : (RoleModuleLinks::pickPrimaryRoleNameFromNames($roleNames)
                ?? $user->roles->first()?->name
                ?? '');
    }

    public function update(): void
    {
        $auth = auth()->user();

        $trainingCenterRules = ['nullable', Rule::exists('training_centers', 'id')->where('activo', true)];
        if (TrainingCenterAccess::roleRequiresTrainingCenter($this->role)) {
            $trainingCenterRules = ['required', Rule::exists('training_centers', 'id')->where('activo', true)];
        }
        $allowedCenters = TrainingCenterAccess::allowedCenterIdsForSave($auth);
        if ($allowedCenters !== null) {
            $trainingCenterRules[] = Rule::in($allowedCenters);
        }

        $roleRules = ['required', 'string', 'exists:roles,name'];
        if ($auth && ! TrainingCenterAccess::isSuperAdmin($auth)) {
            $forbidden = ['super_administrador', 'administrador_sistema'];
            if ($auth->id === $this->user->id && $this->user->hasRole('administrador_sistema')) {
                $forbidden = ['super_administrador'];
            }
            $roleRules[] = Rule::notIn($forbidden);
        }

        Validator::make([
            'email' => $this->email ?: null,
            'tipo_documento' => $this->tipo_documento,
            'numero_documento' => $this->numero_documento,
            'primer_nombre' => $this->primer_nombre,
            'primer_apellido' => $this->primer_apellido,
            'email_institucional' => $this->email_institucional ?: null,
            'role' => $this->role,
            'estado' => $this->estado,
            'training_center_id' => $this->training_center_id,
        ], [
            'email' => ['nullable', 'email', 'max:255', Rule::unique(User::class)->ignore($this->user->id)],
            'tipo_documento' => ['required', Rule::enum(TipoDocumentoEnum::class)],
            'numero_documento' => ['required', 'integer', Rule::unique('users', 'numero_documento')->ignore($this->user->id)],
            'primer_nombre' => ['required', 'string', 'max:100'],
            'primer_apellido' => ['required', 'string', 'max:100'],
            'email_institucional' => ['nullable', 'email', 'max:255', Rule::unique('people', 'email_institucional')->ignore($this->user->person?->id)],
            'role' => $roleRules,
            'estado' => ['required', Rule::enum(EstadoEnum::class)],
            'training_center_id' => $trainingCenterRules,
        ])->validate();

        if ($allowedCenters !== null) {
            $this->training_center_id = $allowedCenters[0];
        }

        $this->user->training_center_id = $this->training_center_id;
        TrainingCenterAccess::validateCentroBoundRoleAssignment($this->user, $this->role, $auth, 'training_center_id');

        // Update user
        $this->user->update([
            'training_center_id' => $this->training_center_id,
            'primary_role_name' => $this->role,
            'email' => $this->email ?: null,
            'tipo_documento' => $this->tipo_documento,
            'numero_documento' => $this->numero_documento,
            'estado' => $this->estado,
        ]);

        // Update person
        if ($this->user->person) {
            $this->user->person->update([
                'entity_position_id' => $this->entity_position_id,
                'linkage_type_id' => $this->linkage_type_id,
                'training_program_id' => $this->training_program_id,
                'primer_nombre' => $this->primer_nombre,
                'segundo_nombre' => $this->segundo_nombre,
                'primer_apellido' => $this->primer_apellido,
                'segundo_apellido' => $this->segundo_apellido,
                'genero' => $this->genero ?: null,
                'telefono' => $this->telefono,
                'celular' => $this->celular,
                'eps' => $this->eps,
                'email_institucional' => $this->email_institucional ?: null,
            ]);
        }

        // Rol principal: solo asegurar que el rol elegido esté asignado (no syncRoles: conserva el resto).
        if (! $this->user->hasRole($this->role)) {
            $this->user->assignRole($this->role);
        }
        $this->user->load('roles');

        session()->flash('status', 'Usuario actualizado exitosamente.');
    }

    public function render()
    {
        $auth = auth()->user();
        $includeAdminRol = $auth->id === $this->user->id && $this->user->hasRole('administrador_sistema');

        return view('livewire.admin.users.user-edit', [
            'tiposDocumento' => TipoDocumentoEnum::cases(),
            'roles' => TrainingCenterAccess::rolesForUserForm($auth, $includeAdminRol),
            'trainingCenters' => TrainingCenterAccess::centersForSelect($auth),
            'estados' => EstadoEnum::cases(),
            'centerSelectReadonly' => TrainingCenterAccess::isCentroAdmin($auth) && $auth->training_center_id,
        ]);
    }
}
