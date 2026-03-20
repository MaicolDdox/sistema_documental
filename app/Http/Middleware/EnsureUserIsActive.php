<?php

namespace App\Http\Middleware;

use App\Enums\EstadoEnum;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Bloquea el acceso si:
     * - El usuario está inactivo (estado), o
     * - El usuario tiene centro de formación y ese centro está desactivado.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        if ($user->estado !== EstadoEnum::Activo) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->withErrors([
                'email' => __('Tu cuenta ha sido desactivada. Contacta al administrador.'),
            ]);
        }

        // Los administradores del sistema no se bloquean por estado del centro de formación
        if ($user->hasRole('administrador_sistema') || $user->hasRole('admin')) {
            return $next($request);
        }

        // Si el usuario tiene centro asignado y ese centro está desactivado, no puede acceder
        if ($user->training_center_id) {
            $center = $user->trainingCenter;
            if ($center && !$center->activo) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login')->withErrors([
                    'email' => __('Tu centro de formación está desactivado. No puedes acceder al sistema. Contacta al administrador.'),
                ]);
            }
        }

        return $next($request);
    }
}
