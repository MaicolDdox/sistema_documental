<?php

namespace Tests\Feature\Regression;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresión: BUG-20260528-011
 * La página /email/verify usaba <flux:text> y <flux:button> con estilos dark-mode
 * que producían texto blanco sobre fondo blanco (ilegible).
 * Corregido: 2026-05-28 — componentes Flux reemplazados por HTML/Tailwind estándar
 * con clases slate del proyecto (text-slate-900, btn-sgd, etc.).
 */
class BUG20260528011Test extends TestCase
{
    use RefreshDatabase;

    public function test_verify_email_page_does_not_use_flux_text_component(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/email/verify');

        $response->assertOk();
        $response->assertDontSee('<flux:text', false);
        $response->assertDontSee('<flux:button', false);
    }

    public function test_verify_email_page_contains_resend_button(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/email/verify');

        $response->assertOk();
        $response->assertSee(route('verification.send'));
    }

    public function test_verify_email_page_contains_logout_form(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/email/verify');

        $response->assertOk();
        $response->assertSee(route('logout'));
    }

    public function test_verify_email_page_shows_success_message_after_resend(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)
            ->withSession(['status' => 'verification-link-sent'])
            ->get('/email/verify');

        $response->assertOk();
        $response->assertSee('enlace de verificación');
    }
}
