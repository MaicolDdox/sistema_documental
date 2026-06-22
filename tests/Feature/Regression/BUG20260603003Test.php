<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Tests\TestCase;

/**
 * Regresión: BUG-2026-06-03-03
 * El correo de verificación de email no llegaba.
 * Causas: routeNotificationForMail() enviaba a email_institucional (distinto del hash),
 * sendEmailVerificationNotification() no tenía try/catch y APP_URL apuntaba a localhost.
 * Corregido: 2026-06-03
 */
class BUG20260603003Test extends TestCase
{
    use RefreshDatabase;

    public function test_send_email_verification_notification_dispara_la_notificacion(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create([
            'email'  => 'test@sena.edu.co',
            'estado' => EstadoEnum::Activo,
        ]);

        $user->sendEmailVerificationNotification();

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_route_notification_for_mail_usa_email_del_usuario(): void
    {
        $user = User::factory()->create([
            'email'  => 'usuario@sena.edu.co',
            'estado' => EstadoEnum::Activo,
        ]);

        $destino = $user->routeNotificationForMail();

        $this->assertSame('usuario@sena.edu.co', $destino);
    }

    public function test_send_email_verification_notification_captura_excepcion_y_loguea(): void
    {
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function (string $message, array $context) {
                return str_contains($message, 'fallo al enviar email de verificación')
                    && isset($context['user_id'])
                    && isset($context['error']);
            });

        $user = User::factory()->unverified()->create(['estado' => EstadoEnum::Activo]);

        // Forzar fallo haciendo que notify() lance excepción
        $userMock = $this->getMockBuilder(User::class)
            ->onlyMethods(['notify'])
            ->getMock();

        $userMock->id    = $user->id;
        $userMock->email = $user->email;
        $userMock->expects($this->once())
            ->method('notify')
            ->willThrowException(new \RuntimeException('SMTP connection failed'));

        $userMock->sendEmailVerificationNotification();
    }
}
