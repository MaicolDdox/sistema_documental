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
     * Bloquea el acceso a usuarios con estado = inactivo.
     * Si un usuario fue desactivado después de iniciar sesión,
     * este middleware cierra su sesión en el siguiente request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->estado !== EstadoEnum::Activo) {
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => __('Tu cuenta ha sido desactivada. Contacta al administrador.'),
            ]);
        }

        return $next($request);
    }
}
