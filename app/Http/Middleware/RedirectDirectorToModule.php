<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectDirectorToModule
{
    /**
     * Redirige director de semilleros y líder de semillero desde /dashboard a su módulo
     * para que usen el layout correcto con su menú.
     *
     * BUG-20260813-050: antes decidía con hasRole() suelto (director_semilleros
     * primero, luego lider_semillero), ignorando si el usuario tenía además
     * un rol de mayor prioridad real (p. ej. super_administrador).
     * BUG-20260813-056: y decidir con el rol PRINCIPAL seguía siendo un
     * problema una vez agregado el rol activo (multi-rol) — este middleware
     * corre sobre /dashboard, así que un usuario cuyo rol activo fuera otro
     * distinto a su principal terminaba redirigido igual a
     * /director-semilleros o /lider-semillero sin pasar por roles.switch,
     * y el aislamiento de la Fase 2 lo bloqueaba con 403. Ahora usa el ROL
     * ACTIVO (ActiveRoleContext::current(), que cae al principal si no hay
     * uno válido en sesión).
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        $user->load('roles');

        // Limpiar caché de permisos para que hasRole() use datos actuales
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $primary = \App\Support\ActiveRoleContext::current();

        if ($primary === 'director_semilleros') {
            return redirect()->to('/director-semilleros', 302);
        }
        if ($primary === 'lider_semillero') {
            return redirect()->to('/lider-semillero', 302);
        }

        return $next($request);
    }
}
