<?php

namespace App\Actions\Fortify;

use App\Models\Person;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class SendPasswordResetLink
{
    /**
     * Envía el enlace de recuperación de contraseña buscando
     * en users.email Y people.email_institucional.
     */
    public function __invoke(Request $request): mixed
    {
        $request->validate([
            'email' => ['required', 'string'],
        ]);

        $loginField = $request->input('email');

        // 1. Buscar por users.email
        $user = User::where('email', $loginField)->first();

        // 2. Si no se encuentra, buscar por people.email_institucional
        if (! $user) {
            $person = Person::where('email_institucional', $loginField)->first();
            $user = $person?->user;
        }

        // 3. Si se encontró el usuario, enviar el token de reset
        if ($user && $user->email) {
            // Usamos el broker estándar pero con el email del user
            $status = Password::broker()->sendResetLink(
                ['email' => $user->email]
            );
        } else {
            $status = Password::INVALID_USER;
        }

        // 4. Retornar respuesta apropiada
        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', __($status));
        }

        return back()->withErrors([
            'email' => [__($status)],
        ]);
    }
}
