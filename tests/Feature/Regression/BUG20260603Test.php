<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Enums\TipoDocumentoEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Regresión: BUG-2026-06-03-01 y BUG-2026-06-03-02
 * BUG-01: Email de credenciales mostraba "Correo de acceso" en lugar de número de documento.
 * BUG-02: Formularios de creación de usuario no tenían campo tipo_documento (hardcodeado a CedulaCiudadana).
 * Corregido: 2026-06-03
 */
class BUG20260603Test extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────────────────────────
    // BUG-2026-06-03-01 — Plantilla correo credenciales
    // ─────────────────────────────────────────────────

    public function test_credenciales_email_muestra_numero_documento_no_email(): void
    {
        $user = User::factory()->create([
            'numero_documento' => '12345678',
            'email'            => 'test@example.com',
            'tipo_documento'   => TipoDocumentoEnum::CedulaCiudadana,
            'estado'           => EstadoEnum::Activo,
        ]);

        $mailable = new \App\Mail\CredencialesAcceso($user, 'password123', 'http://localhost');

        $mailable->assertSeeInHtml('12345678');
        $mailable->assertSeeInHtml('Número de documento');
        $mailable->assertSeeInHtml('test@example.com');
        $mailable->assertDontSeeInHtml('Correo de acceso');
    }

    // ─────────────────────────────────────────────────
    // BUG-2026-06-03-02 — tipo_documento requerido en store
    // ─────────────────────────────────────────────────

    public function test_crear_usuario_admin_sin_tipo_documento_falla_validacion(): void
    {
        Role::create(['name' => 'administrador_sistema']);
        $admin = User::factory()->create(['estado' => EstadoEnum::Activo]);
        $admin->assignRole('administrador_sistema');

        $response = $this->actingAs($admin)->post(route('admin.usuarios.store'), [
            'nombre'           => 'Juan',
            'apellido'         => 'Pérez',
            // tipo_documento intencionalmente omitido
            'numero_documento' => '99887766',
            'email'            => 'nuevo@test.com',
            'password'         => 'secret123',
        ]);

        $response->assertSessionHasErrors('tipo_documento');
        $this->assertDatabaseMissing('users', ['numero_documento' => '99887766']);
    }

    public function test_crear_usuario_admin_guarda_tipo_documento_seleccionado(): void
    {
        Mail::fake();

        Role::firstOrCreate(['name' => 'administrador_sistema']);
        $admin = User::factory()->create(['estado' => EstadoEnum::Activo]);
        $admin->assignRole('administrador_sistema');

        $this->actingAs($admin)->post(route('admin.usuarios.store'), [
            'nombre'           => 'Ana',
            'apellido'         => 'López',
            'tipo_documento'   => TipoDocumentoEnum::Pasaporte->value,
            'numero_documento' => '77665544',
            'email'            => 'ana@test.com',
            'password'         => 'secret123',
        ]);

        $this->assertDatabaseHas('users', [
            'numero_documento' => '77665544',
            'tipo_documento'   => TipoDocumentoEnum::Pasaporte->value,
        ]);
    }
}
