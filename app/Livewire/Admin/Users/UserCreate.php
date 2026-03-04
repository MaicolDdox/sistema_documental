<?php

namespace App\Livewire\Admin\Users;

use App\Actions\Fortify\CreateNewUser;
use App\Enums\TipoDocumentoEnum;
use App\Models\TrainingCenter;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class UserCreate extends Component
{
    // User fields
    public ?int $training_center_id = null;
    public string $email = '';
    public string $tipo_documento = '';
    public string $numero_documento = '';
    public string $password = '';
    public string $password_confirmation = '';

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

    /**
     * Crea el usuario usando la acción de Fortify reescrita.
     */
    public function store(CreateNewUser $creator): void
    {
        $user = $creator->create([
            'training_center_id' => $this->training_center_id,
            'email' => $this->email ?: null,
            'tipo_documento' => $this->tipo_documento,
            'numero_documento' => $this->numero_documento,
            'password' => $this->password,
            'password_confirmation' => $this->password_confirmation,
            'primer_nombre' => $this->primer_nombre,
            'segundo_nombre' => $this->segundo_nombre,
            'primer_apellido' => $this->primer_apellido,
            'segundo_apellido' => $this->segundo_apellido,
            'genero' => $this->genero ?: null,
            'telefono' => $this->telefono,
            'celular' => $this->celular,
            'eps' => $this->eps,
            'email_institucional' => $this->email_institucional ?: null,
            'role' => $this->role,
            'entity_position_id' => $this->entity_position_id,
            'linkage_type_id' => $this->linkage_type_id,
            'training_program_id' => $this->training_program_id,
        ]);

        session()->flash('status', "Usuario {$user->person->primer_nombre} {$user->person->primer_apellido} creado exitosamente con estado inactivo.");

        $this->redirect(route('admin.users.index'));
    }

    public function render()
    {
        return view('livewire.admin.users.user-create', [
            'tiposDocumento' => TipoDocumentoEnum::cases(),
            'roles' => Role::all(),
            'trainingCenters' => TrainingCenter::orderBy('nombre')->get(),
        ]);
    }
}
