<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Enums\TipoDocumentoEnum;
use App\Models\User;
use App\Services\Admin\NotificacionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Regresión: BUG-20260609-009
 * El bloque Mail::to()->send() + catch { Log::error() } estaba copy-pasted
 * en 5 controladores distintos, sin centralización. Un cambio en el
 * comportamiento ante fallo de correo debía replicarse manualmente en todos.
 * Corregido: 2026-06-09 — centralizado en NotificacionService.
 */
class BUG20260609009Test extends TestCase
{
    use RefreshDatabase;

    private function crearUsuario(): User
    {
        $user = User::factory()->create([
            'email'          => 'dest@test.com',
            'estado'         => EstadoEnum::Activo,
            'tipo_documento' => TipoDocumentoEnum::CedulaCiudadana,
        ]);

        $user->person()->create([
            'primer_nombre'       => 'Juan',
            'primer_apellido'     => 'Pérez',
            'genero'              => 'masculino',
            'celular'             => 300000000,
            'eps'                 => 'Sura',
            'email_institucional' => 'dest@test.com',
        ]);

        return $user->fresh(['person']);
    }

    public function test_enviar_credenciales_no_lanza_excepcion_si_mail_falla(): void
    {
        Mail::fake();

        // Simular fallo forzando una excepción en Mail::send
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP connection failed'));

        $service = app(NotificacionService::class);
        $user    = $this->crearUsuario();

        // No debe propagar la excepción — NotificacionService la captura
        $this->expectNotToPerformAssertions();

        try {
            $service->enviarCredenciales($user, 'password123');
        } catch (\Throwable $e) {
            $this->fail("NotificacionService::enviarCredenciales no debe propagar excepciones: {$e->getMessage()}");
        }
    }

    public function test_enviar_credenciales_llama_mail_cuando_no_hay_error(): void
    {
        Mail::fake();

        $service = app(NotificacionService::class);
        $user    = $this->crearUsuario();

        $service->enviarCredenciales($user, 'password123');

        Mail::assertSent(\App\Mail\CredencialesAcceso::class, fn ($mail) =>
            $mail->hasTo('dest@test.com')
        );
    }

    public function test_enviar_contrasena_restablecida_no_lanza_excepcion_si_mail_falla(): void
    {
        Mail::fake();

        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP timeout'));

        $service = app(NotificacionService::class);
        $user    = $this->crearUsuario();

        $lanzó = false;
        try {
            $service->enviarContrasenaRestablecida($user, 'nueva_pass123', 'Director Prueba');
        } catch (\Throwable) {
            $lanzó = true;
        }

        $this->assertFalse($lanzó, 'NotificacionService::enviarContrasenaRestablecida no debe propagar excepciones de correo');
    }
}
