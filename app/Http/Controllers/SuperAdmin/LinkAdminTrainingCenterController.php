<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LinkAdminTrainingCenterController extends Controller
{
    private const ADMIN_ROLE_NAMES = ['administrador_sistema', 'admin'];

    /**
     * Administradores aún sin centro asignado (training_center_id null).
     */
    private function assignableAdministradores()
    {
        return User::query()
            ->whereNull('training_center_id')
            ->whereHas('roles', fn ($r) => $r->whereIn('name', self::ADMIN_ROLE_NAMES))
            ->whereDoesntHave('roles', fn ($r) => $r->where('name', 'super_administrador'))
            ->with('person')
            ->orderBy('email')
            ->get();
    }

    /**
     * Centros que aún no tienen un administrador del sistema vinculado (excluye super).
     */
    private function centrosDisponiblesParaVincular()
    {
        return TrainingCenter::query()
            ->whereDoesntHave('users', function ($q) {
                $q->whereHas('roles', fn ($r) => $r->whereIn('name', self::ADMIN_ROLE_NAMES))
                    ->whereDoesntHave('roles', fn ($r) => $r->where('name', 'super_administrador'));
            })
            ->orderBy('nombre')
            ->get();
    }

    /** ¿Este centro ya tiene al menos un administrador del sistema (no super)? */
    private function centroYaTieneAdministrador(int $trainingCenterId): bool
    {
        return User::query()
            ->where('training_center_id', $trainingCenterId)
            ->whereHas('roles', fn ($r) => $r->whereIn('name', self::ADMIN_ROLE_NAMES))
            ->whereDoesntHave('roles', fn ($r) => $r->where('name', 'super_administrador'))
            ->exists();
    }

    public function index(): View
    {
        $centers = TrainingCenter::query()
            ->with([
                'users' => function ($q) {
                    $q->whereHas('roles', fn ($r) => $r->whereIn('name', self::ADMIN_ROLE_NAMES))
                        ->whereDoesntHave('roles', fn ($r) => $r->where('name', 'super_administrador'))
                        ->with('person');
                },
            ])
            ->orderBy('nombre')
            ->get();

        $centrosDisponibles = $this->centrosDisponiblesParaVincular();
        $administradores = $this->assignableAdministradores();

        return view('super-admin.link-admin-center', compact('centers', 'centrosDisponibles', 'administradores'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'training_center_id' => ['required', 'exists:training_centers,id'],
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $user = User::with('roles')->findOrFail($validated['user_id']);

        if ($user->hasRole('super_administrador')) {
            return back()->withErrors(['user_id' => 'No puedes vincular al super administrador a un centro desde aquí.'])->withInput();
        }

        if (! $user->hasAnyRole(self::ADMIN_ROLE_NAMES)) {
            return back()->withErrors(['user_id' => 'El usuario seleccionado debe tener rol de administrador del sistema.'])->withInput();
        }

        if ($user->training_center_id !== null) {
            return back()->withErrors(['user_id' => 'Este administrador ya tiene un centro de formación asignado.'])->withInput();
        }

        $centerId = (int) $validated['training_center_id'];
        if ($this->centroYaTieneAdministrador($centerId)) {
            return back()->withErrors(['training_center_id' => 'Este centro ya tiene un administrador del sistema vinculado.'])->withInput();
        }

        $user->update([
            'training_center_id' => $centerId,
        ]);

        return redirect()
            ->route('super-admin.centros-administradores')
            ->with('success', 'Centro de formación vinculado al administrador correctamente.');
    }
}
