<?php

namespace App\Http\Controllers\DirectorSemilleros;

use App\Enums\EstadoEnum;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\RoleModuleLinks;
use App\Support\TrainingCenterAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AsignarRolController extends Controller
{
    /**
     * Vista de asignación de roles (solo Líder de Semillero según @can).
     */
    public function index()
    {
        $this->authorize('usuarios.crear_lider_semillero');

        $user = Auth::user();

        $usuarios = $this->queryUsuariosSinRolElegiblesParaDirector($user)->get();

        $rolesAsignables = collect();
        if (Auth::user()->can('usuarios.crear_lider_semillero')) {
            $rolesAsignables->push((object) ['name' => 'lider_semillero', 'label' => 'Líder de Semillero']);
        }

        return view('director_semilleros.asignar_roles', compact('usuarios', 'rolesAsignables'));
    }

    /**
     * Guardar asignación de rol (solo lider_semillero permitido para director).
     */
    public function store(Request $request)
    {
        $this->authorize('usuarios.crear_lider_semillero');

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'rol'     => ['required', 'string', 'in:lider_semillero'],
        ]);

        $currentUser = Auth::user();
        $usuario = $this->queryUsuariosSinRolElegiblesParaDirector($currentUser)
            ->whereKey($validated['user_id'])
            ->firstOrFail();

        TrainingCenterAccess::validateCentroBoundRoleAssignment($usuario, $validated['rol'], $currentUser);
        $hadRoles = $usuario->roles()->exists();
        RoleModuleLinks::lockPrimaryRoleBeforeAddingRole($usuario);
        $usuario->assignRole($validated['rol']);
        if (! $hadRoles) {
            $usuario->refresh();
            $usuario->forceFill(['primary_role_name' => $validated['rol']])->saveQuietly();
        }

        return redirect()->route('dir-sem.asignar-roles.index')
            ->with('success', 'Rol asignado correctamente.');
    }

    /**
     * Usuarios del centro sin rol Spatie, excluyendo aprendices registrados por el asesor
     * (inactivos vinculados a un semillero como integrantes — seedling_members).
     * El director/admin suele crear usuarios activos o pendientes sin vínculo de integrante.
     */
    private function queryUsuariosSinRolElegiblesParaDirector(User $director)
    {
        return User::query()
            ->with('person')
            ->when(
                $director->training_center_id,
                fn ($q) => $q->where('training_center_id', $director->training_center_id),
                fn ($q) => $q->whereRaw('0 = 1')
            )
            ->whereDoesntHave('roles')
            ->where(function ($q) {
                $q->where('estado', '!=', EstadoEnum::Inactivo)
                    ->orWhereDoesntHave('seedlings');
            })
            ->orderBy('numero_documento');
    }
}
