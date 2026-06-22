<?php

namespace App\Livewire\Admin\Users;

use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use App\Enums\EstadoEnum;
use App\Models\User;
use App\Support\TrainingCenterAccess;

class UserIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $filterEstado = '';

    #[Url]
    public string $filterRole = '';

    /**
     * Toggle del estado activo/inactivo de un usuario.
     */
    public function toggleEstado(int $userId): void
    {
        $user = TrainingCenterAccess::scopeUserQueryForList(User::query(), auth()->user())
            ->findOrFail($userId);

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
