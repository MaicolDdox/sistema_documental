<?php

namespace App\Http\Responses;

use App\Support\RoleModuleLinks;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    /**
     * Redirección post-login: mismo criterio que Livewire Login (rol principal guardado o prioridad).
     */
    public function toResponse($request): Response
    {
        $user = Auth::user();
        $user->load('roles');

        return redirect()->intended(RoleModuleLinks::dashboardUrlForUser($user));
    }
}
