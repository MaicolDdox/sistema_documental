<?php

namespace App\Livewire\Admin\Users;

use App\Enums\EstadoEnum;
use App\Enums\TipoDocumentoEnum;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Spatie\Permission\Models\Role;

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

        $this->role = $user->roles->first()?->name ?? '';
    }

    public function update(): void
    {
        Validator::make([
            'email' => $this->email ?: null,
            'tipo_documento' => $this->tipo_documento,
            'numero_documento' => $this->numero_documento,
            'primer_nombre' => $this->primer_nombre,
            'primer_apellido' => $this->primer_apellido,
            'email_institucional' => $this->email_institucional ?: null,
            'role' => $this->role,
            'estado' => $this->estado,
        ], [
            'email' => ['nullable', 'email', 'max:255', Rule::unique(User::class)->ignore($this->user->id)],
            'tipo_documento' => ['required', Rule::enum(TipoDocumentoEnum::class)],
            'numero_documento' => ['required', 'integer', Rule::unique('users', 'numero_documento')->ignore($this->user->id)],
            'primer_nombre' => ['required', 'string', 'max:100'],
            'primer_apellido' => ['required', 'string', 'max:100'],
            'email_institucional' => ['nullable', 'email', 'max:255', Rule::unique('people', 'email_institucional')->ignore($this->user->person?->id)],
            'role' => ['required', 'string', 'exists:roles,name'],
            'estado' => ['required', Rule::enum(EstadoEnum::class)],
        ])->validate();

        // Update user
        $this->user->update([
            'training_center_id' => $this->training_center_id,
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

        // Sync role
        $this->user->syncRoles([$this->role]);

        session()->flash('status', 'Usuario actualizado exitosamente.');
    }

    public function render()
    {
        return view('livewire.admin.users.user-edit', [
            'tiposDocumento' => TipoDocumentoEnum::cases(),
            'roles' => Role::all(),
            'trainingCenters' => TrainingCenter::orderBy('nombre')->get(),
            'estados' => EstadoEnum::cases(),
        ]);
    }
}
