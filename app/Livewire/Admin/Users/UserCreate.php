<?php

namespace App\Livewire\Admin\Users;

use App\Actions\Fortify\CreateNewUser;
use App\Enums\TipoDocumentoEnum;
use App\Mail\CredencialesAcceso;
use App\Support\TrainingCenterAccess;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

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

    // Opciones de notificación
    public bool $enviar_credenciales = false;
    public string $password_plain = '';

    // Optional relations
    public ?int $entity_position_id = null;
    public ?int $linkage_type_id = null;
    public ?int $training_program_id = null;

    public function mount(): void
    {
        $auth = auth()->user();
        if (TrainingCenterAccess::isCentroAdmin($auth) && $auth->training_center_id) {
            $this->training_center_id = (int) $auth->training_center_id;
        }
    }

    /**
     * Crea el usuario usando la acción de Fortify reescrita.
     */
    public function store(CreateNewUser $creator): void
    {
        $this->password_plain = $this->password;

        $user = $creator->create([
            'training_center_id'    => $this->training_center_id,
            'email'                 => $this->email ?: null,
            'tipo_documento'        => $this->tipo_documento,
            'numero_documento'      => $this->numero_documento,
            'password'              => $this->password,
            'password_confirmation' => $this->password_confirmation,
            'primer_nombre'         => $this->primer_nombre,
            'segundo_nombre'        => $this->segundo_nombre,
            'primer_apellido'       => $this->primer_apellido,
            'segundo_apellido'      => $this->segundo_apellido,
            'genero'                => $this->genero ?: null,
            'telefono'              => $this->telefono,
            'celular'               => $this->celular,
            'eps'                   => $this->eps,
            'email_institucional'   => $this->email_institucional ?: null,
            'role'                  => $this->role,
            'entity_position_id'    => $this->entity_position_id,
            'linkage_type_id'       => $this->linkage_type_id,
            'training_program_id'   => $this->training_program_id,
        ]);

        if ($this->enviar_credenciales && $user->email) {
            try {
                Mail::to($user->email)
                    ->send(new CredencialesAcceso($user, $this->password_plain, config('app.url')));
            } catch (\Throwable $e) {
                Log::error('UserCreate: fallo al enviar credenciales por correo', [
                    'usuario_id' => $user->id,
                    'email'      => $user->email,
                    'error'      => $e->getMessage(),
                ]);
            }
        }

        $nombre   = "{$user->person->primer_nombre} {$user->person->primer_apellido}";
        $mensaje  = $this->enviar_credenciales
            ? "Usuario {$nombre} creado. Se enviaron las credenciales por correo."
            : "Usuario {$nombre} creado exitosamente con estado inactivo.";

        session()->flash('status', $mensaje);
        $this->redirect(route('admin.users.index'));
    }

    public function render()
    {
        $auth = auth()->user();

        return view('livewire.admin.users.user-create', [
            'tiposDocumento' => TipoDocumentoEnum::cases(),
            'roles' => TrainingCenterAccess::rolesForUserForm($auth, false),
            'trainingCenters' => TrainingCenterAccess::centersForSelect($auth),
            'centerSelectReadonly' => TrainingCenterAccess::isCentroAdmin($auth) && $auth->training_center_id,
        ]);
    }
}
