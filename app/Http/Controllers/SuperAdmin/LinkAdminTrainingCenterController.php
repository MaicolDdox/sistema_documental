<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\TrainingCenter;
use App\Models\User;
use App\Support\SystemAdminCenterLink;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LinkAdminTrainingCenterController extends Controller
{
    /**
     * Administradores aún sin centro asignado (training_center_id null o 0).
     */
    private function assignableAdministradores()
    {
        [$roleIds, $roleNames] = SystemAdminCenterLink::webRoleIdsAndNames();

        $base = User::query()
            ->where(function (Builder $q) {
                $q->whereNull('training_center_id')
                    ->orWhere('training_center_id', 0);
            });

        return SystemAdminCenterLink::applyAdminSistemaNoSuperScope($base, $roleIds, $roleNames)
            ->with('person')
            ->orderBy('email')
            ->get();
    }

    /**
     * Centros que aún no tienen un administrador del sistema vinculado (excluye super).
     */
    private function centrosDisponiblesParaVincular()
    {
        [$roleIds, $roleNames] = SystemAdminCenterLink::webRoleIdsAndNames();

        return TrainingCenter::query()
            ->whereDoesntHave('users', function ($q) use ($roleIds, $roleNames) {
                SystemAdminCenterLink::applyAdminSistemaNoSuperScope($q, $roleIds, $roleNames);
            })
            ->orderBy('nombre')
            ->get();
    }

    public function index(): View
    {
        [$roleIds, $roleNames] = SystemAdminCenterLink::webRoleIdsAndNames();

        $centers = TrainingCenter::query()
            ->with([
                'users' => function ($q) use ($roleIds, $roleNames) {
                    SystemAdminCenterLink::applyAdminSistemaNoSuperScope($q, $roleIds, $roleNames)
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

        [, $roleNames] = SystemAdminCenterLink::webRoleIdsAndNames();
        $esAdminSistema = $user->hasAnyRole($roleNames)
            || in_array($user->primary_role_name, $roleNames, true);
        if (! $esAdminSistema) {
            return back()->withErrors(['user_id' => 'El usuario seleccionado debe tener rol de administrador del sistema.'])->withInput();
        }

        if ($user->training_center_id !== null && (int) $user->training_center_id !== 0) {
            return back()->withErrors(['user_id' => 'Este administrador ya tiene un centro de formación asignado.'])->withInput();
        }

        $centerId = (int) $validated['training_center_id'];
        if (SystemAdminCenterLink::trainingCenterHasSystemAdmin($centerId)) {
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
