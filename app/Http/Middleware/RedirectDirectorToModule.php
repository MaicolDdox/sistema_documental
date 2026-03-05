<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectDirectorToModule
{
    /**
     * Redirige al director de semilleros desde /dashboard a su módulo /director-semilleros
     * para que use el layout correcto con el menú (PRINCIPAL, GESTIÓN SEMILLEROS, USUARIOS).
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->hasRole('director_semilleros')) {
            return redirect()->to('/director-semilleros', 302);
        }

        return $next($request);
    }
}
