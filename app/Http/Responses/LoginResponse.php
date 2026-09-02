<?php

namespace App\Http\Responses;

use App\Support\ActiveRoleContext;
use App\Support\RoleModuleLinks;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    /**
     * Redirección post-login: mismo criterio que Livewire Login (rol principal guardado o prioridad).
     * Este es el punto de login que usa el flujo 2FA de Fortify (distinto del
     * Livewire Login normal) — también inicializa el rol activo en sesión.
     */
    public function toResponse($request): Response
    {
        $user = Auth::user();
        $user->load('roles');

        ActiveRoleContext::initializeForUser($user);

        return redirect()->intended(RoleModuleLinks::dashboardUrlForUser($user));
    }
}
