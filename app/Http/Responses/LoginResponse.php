<?php

namespace App\Http\Responses;

use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    /**
     * Redirección post-login basada en el rol del usuario (Spatie).
     */
    public function toResponse($request): Response
    {
        $user = Auth::user();

        $home = match (true) {
            $user->hasRole('administrador_sistema'),
            $user->hasRole('admin') => '/admin/dashboard',
            $user->hasRole('director_investigacion'),
            $user->hasRole('investigador_asociado') => '/research/dashboard',
            $user->hasRole('director_semilleros') => '/director-semilleros',
            $user->hasRole('lider_semillero') => '/lider-semillero',
            $user->hasRole('asesor') => '/seedlings',
            default => '/dashboard',
        };

        if ($user->hasRole('director_semilleros')) {
            return redirect()->to('/director-semilleros');
        }

        if ($user->hasRole('lider_semillero')) {
            return redirect()->to('/lider-semillero');
        }

        return redirect()->intended($home);
    }
}
