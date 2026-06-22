<?php

namespace Tests\Feature\Regression;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresión: BUG-20260528-009
 * La verificación de email es OPCIONAL (no bloquea el acceso).
 * Los usuarios sin email verificado pueden usar el sistema normalmente
 * y ven un banner recordatorio descartable en cada página.
 * Actualizado: 2026-05-28 — middleware 'verified' removido, banner agregado al layout.
 */
class BUG20260528009Test extends TestCase
{
    use RefreshDatabase;

    public function test_unverified_user_can_access_dashboard(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        // No debe redirigir a email/verify — la verificación es opcional
        $this->assertNotEquals(500, $response->status());
        $this->assertNotEquals(route('verification.notice'), $response->headers->get('Location'));
    }

    public function test_verified_user_can_access_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
    }

    public function test_unverified_user_can_access_settings(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/settings/profile');

        // Sin bloqueo — puede acceder a settings sin verificar
        $this->assertNotEquals(route('verification.notice'), $response->headers->get('Location'));
    }

    public function test_verification_notice_route_exists(): void
    {
        $this->assertNotNull(
            app('router')->getRoutes()->getByName('verification.notice'),
            'La ruta verification.notice debe existir (Fortify emailVerification)'
        );
    }

    public function test_verification_send_route_exists(): void
    {
        $this->assertNotNull(
            app('router')->getRoutes()->getByName('verification.send'),
            'La ruta verification.send debe existir (Fortify emailVerification)'
        );
    }
}
