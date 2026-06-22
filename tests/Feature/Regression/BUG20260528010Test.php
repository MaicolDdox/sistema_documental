<?php

namespace Tests\Feature\Regression;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresión: BUG-20260528-010
 * Fortify::verifyEmailView() no estaba registrado en FortifyServiceProvider::configureViews().
 * Al activar el middleware 'verified', cualquier usuario sin email verificado recibía
 * BindingResolutionException al ser redirigido a /email/verify.
 * Corregido: 2026-05-28 — agregado Fortify::verifyEmailView() en FortifyServiceProvider.
 */
class BUG20260528010Test extends TestCase
{
    use RefreshDatabase;

    public function test_verify_email_page_renders_without_binding_exception(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/email/verify');

        $response->assertOk();
    }

    public function test_unverified_user_accessing_dashboard_does_not_get_500(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        // Verificación opcional: no debe explotar con 500 ni redirigir a email/verify
        $this->assertNotEquals(500, $response->status());
        $this->assertNotEquals(route('verification.notice'), $response->headers->get('Location'));
    }

    public function test_resend_verification_email_endpoint_accepts_post(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)
            ->post('/email/verification-notification');

        // Redirige de vuelta con status de envío
        $response->assertRedirect();
    }
}
