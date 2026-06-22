<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Mail\ContrasenaRestablecida;
use App\Mail\CredencialesAcceso;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificacionService
{
    /**
     * Envía las credenciales de acceso por correo al usuario.
     * No lanza excepciones: registra en log si falla.
     */
    public function enviarCredenciales(User $user, string $plainPassword): void
    {
        if (! $user->email) {
            return;
        }

        try {
            $user->loadMissing('person');
            Mail::to($user->email)
                ->send(new CredencialesAcceso($user, $plainPassword, config('app.url')));
        } catch (\Throwable $e) {
            Log::error('NotificacionService: fallo al enviar credenciales', [
                'usuario_id' => $user->id,
                'email'      => $user->email,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notifica al usuario que su contraseña fue restablecida por un administrador.
     * No lanza excepciones: registra en log si falla.
     */
    public function enviarContrasenaRestablecida(User $user, string $nuevaPassword, string $restablecidaPor): void
    {
        if (! $user->email) {
            return;
        }

        try {
            Mail::to($user->email)
                ->send(new ContrasenaRestablecida($user, $nuevaPassword, $restablecidaPor, config('app.url')));
        } catch (\Throwable $e) {
            Log::error('NotificacionService: fallo al enviar notificación de contraseña restablecida', [
                'usuario_id' => $user->id,
                'email'      => $user->email,
                'error'      => $e->getMessage(),
            ]);
        }
    }
}
