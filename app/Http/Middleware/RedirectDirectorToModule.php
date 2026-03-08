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

        if ($user->hasRole('director_semilleros')) {
            return redirect()->to('/director-semilleros', 302);
        }
        if ($user->hasRole('lider_semillero')) {
            return redirect()->to('/lider-semillero', 302);
        }

        return $next($request);
    }
}
