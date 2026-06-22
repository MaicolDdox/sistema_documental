<?php

namespace Tests\Feature\Regression;

use App\Livewire\Auth\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Livewire\Livewire;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

/**
 * Regresión: BUG-20260616-01
 * Descripción: el login Livewire (wire:submit) nunca pasaba por el middleware
 * RedirectIfTwoFactorAuthenticatable de Fortify, por lo que un usuario con 2FA
 * confirmado entraba directo al dashboard sin que se le exigiera el código OTP
 * ni el recovery code.
 * Corregido: 2026-06-16
 */
class BUG20260616001Test extends TestCase
{
    use RefreshDatabase;

    public function test_full_two_factor_login_cycle_authenticates_with_valid_otp_code(): void
    {
        $engine = new Google2FA();
        $secret = $engine->generateSecretKey();

        $user = User::factory()->create([
            'two_factor_secret' => Crypt::encrypt($secret),
            'two_factor_recovery_codes' => Crypt::encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);

        // Paso 1: el login Livewire detecta el 2FA confirmado y redirige al reto,
        // sin autenticar todavía.
        Livewire::test(Login::class)
            ->set('tipo_documento', $user->tipo_documento->value)
            ->set('numero_documento', $user->numero_documento)
            ->set('password', 'password')
            ->call('login')
            ->assertRedirect(route('two-factor.login'));

        $this->assertGuest();
        $this->assertEquals($user->id, session('login.id'));

        // Paso 2: completar el reto con un código OTP válido sí autentica al usuario.
        $validCode = $engine->getCurrentOtp($secret);

        $response = $this->post(route('two-factor.login.store'), [
            'code' => $validCode,
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }

    public function test_full_two_factor_login_cycle_rejects_invalid_otp_code(): void
    {
        $engine = new Google2FA();
        $secret = $engine->generateSecretKey();

        $user = User::factory()->create([
            'two_factor_secret' => Crypt::encrypt($secret),
            'two_factor_recovery_codes' => Crypt::encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);

        Livewire::test(Login::class)
            ->set('tipo_documento', $user->tipo_documento->value)
            ->set('numero_documento', $user->numero_documento)
            ->set('password', 'password')
            ->call('login');

        $this->post(route('two-factor.login.store'), [
            'code' => '000000',
        ]);

        $this->assertGuest();
    }
}
