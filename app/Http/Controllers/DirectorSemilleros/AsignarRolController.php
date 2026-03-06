<?php

namespace App\Http\Controllers\DirectorSemilleros;

use App\Http\Controllers\Controller;
use App\Models\User;
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

        $usuarios = User::with('person')
            ->when($user->training_center_id, fn ($q) => $q->where('training_center_id', $user->training_center_id))
            ->whereDoesntHave('roles')
            ->orderBy('numero_documento')
            ->get();

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
        $usuario = User::when($currentUser->training_center_id, fn ($q) => $q->where('training_center_id', $currentUser->training_center_id))
            ->findOrFail($validated['user_id']);

        $usuario->assignRole($validated['rol']);

        return redirect()->route('dir-sem.asignar-roles.index')
            ->with('success', 'Rol asignado correctamente.');
    }
}
