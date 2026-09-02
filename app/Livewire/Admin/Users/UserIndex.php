<?php

namespace App\Livewire\Admin\Users;

use App\Enums\EstadoEnum;
use App\Models\User;
use App\Support\TrainingCenterAccess;
use App\Support\UserOwnershipAccess;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class UserIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $filterEstado = '';

    #[Url]
    public string $filterRole = '';

    public function mount(): void
    {
        $auth = auth()->user();
        if (! TrainingCenterAccess::isSuperAdmin($auth)
            && ! $auth->hasAnyRole(['administrador_sistema', 'director_semilleros', 'lider_semillero'])
        ) {
            abort(403);
        }
    }

    /**
     * Toggle del estado activo/inactivo de un usuario.
     * Regla uno-a-uno: solo quien creó la cuenta (o Súper Administrador) puede hacerlo.
     */
    public function toggleEstado(int $userId): void
    {
        $user = TrainingCenterAccess::scopeUserQueryForList(User::query(), auth()->user())
            ->findOrFail($userId);

        if (! UserOwnershipAccess::canManage(auth()->user(), $user)) {
            abort(403, 'Solo quien creó esta cuenta puede gestionarla.');
        }

        $user->estado = $user->estado === EstadoEnum::Activo
            ? EstadoEnum::Inactivo
            : EstadoEnum::Activo;

        $user->save();
    }

    public function render()
    {
        $users = TrainingCenterAccess::scopeUserQueryForList(
            User::with(['person', 'roles']),
            auth()->user()
        )
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('email', 'like', "%{$this->search}%")
                        ->orWhere('numero_documento', 'like', "%{$this->search}%")
                        ->orWhereHas('person', function ($pq) {
                            $pq->where('primer_nombre', 'like', "%{$this->search}%")
                                ->orWhere('primer_apellido', 'like', "%{$this->search}%")
                                ->orWhere('email_institucional', 'like', "%{$this->search}%");
                        });
                });
            })
            ->when($this->filterEstado, function ($query) {
                $query->where('estado', $this->filterEstado);
            })
            ->when($this->filterRole, function ($query) {
                $query->whereHas('roles', function ($q) {
                    $q->where('name', $this->filterRole);
                });
            })
            ->latest()
            ->paginate(15);

        return view('livewire.admin.users.user-index', compact('users'));
    }
}
