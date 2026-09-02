<?php

namespace App\Http\Controllers;

use App\Support\ActiveRoleContext;
use App\Support\RoleModuleLinks;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * FEAT-20260830-001: cambio de "rol activo" para usuarios multi-rol.
 * Reutiliza el selector "Roles y Módulos" ya existente en el header
 * (resources/views/components/app-layout.blade.php) — este controlador es
 * el que hace que esos enlaces realmente cambien el contexto en sesión en
 * vez de solo navegar a la URL del otro módulo.
 */
class RoleSwitchController extends Controller
{
    public function switch(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'string'],
        ]);

        $user = Auth::user();

        if (! ActiveRoleContext::switchTo($user, $validated['role'])) {
            abort(403, 'No tienes ese rol asignado.');
        }

        // BUG-20260813-056: si urlForRoleName() no reconociera el rol (no
        // debería pasar hoy — los 6 roles del sistema están mapeados), caer
        // a route('dashboard') sería volver a depender de esa ruta
        // genérica; en su lugar se intenta primero resolver por el rol
        // activo recién actualizado.
        $url = RoleModuleLinks::urlForRoleName($validated['role'])
            ?? RoleModuleLinks::urlForRoleName(ActiveRoleContext::current())
            ?? route('dashboard');

        return redirect()->to($url);
    }
}
