<?php

namespace App\Http\Middleware;

use App\Support\ActiveRoleContext;
use App\Support\RoleModuleLinks;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * FEAT-20260830-001 (Fase 2): aísla el acceso a un módulo por rol activo,
 * no solo por tener el rol asignado. Un usuario con 2+ roles ya puede
 * (via Spatie `role:`) entrar a las rutas de cualquiera de sus roles
 * asignados; este middleware exige además que ese rol sea el ACTIVO en la
 * sesión actual (App\Support\ActiveRoleContext), o rechaza con 403.
 *
 * Uso: ->middleware('active_role:lider_semillero') o, para un prefijo
 * compartido por varios roles, ->middleware('active_role:administrador_sistema,super_administrador')
 *
 * NOTA (Fase 1): esta clase se registra pero todavía no se aplica a ningún
 * grupo de rutas — se activa módulo por módulo en la Fase 2.
 */
class EnsureActiveRole
{
    public function handle(Request $request, Closure $next, string ...$allowedRoles): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $active = ActiveRoleContext::current();

        if ($active !== null && in_array($active, $allowedRoles, true)) {
            return $next($request);
        }

        $labels = array_map(fn (string $r) => RoleModuleLinks::labelForRoleName($r), $allowedRoles);

        abort(403, 'Este módulo pertenece a otro de tus roles ('.implode(' / ', $labels).'). Cambia tu rol activo desde el selector de roles para entrar aquí.');
    }
}
