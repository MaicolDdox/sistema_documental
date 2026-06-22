<?php

namespace Tests\Feature\Auth;

use App\Enums\EstadoEnum;
use App\Livewire\Auth\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;
use Livewire\Livewire;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
    }

    public function test_users_can_authenticate_with_numero_documento(): void
    {
        $user = User::factory()->create();

        $response = $this->post(route('login.store'), [
            'tipo_documento' => $user->tipo_documento->value,
            'numero_documento' => $user->numero_documento,
            'password' => 'password',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertAuthenticated();
    }

    public function test_users_can_authenticate_with_tipo_documento_and_password(): void
    {
        $user = User::factory()->create();

        $response = $this->post(route('login.store'), [
            'tipo_documento' => $user->tipo_documento->value,
            'numero_documento' => $user->numero_documento,
            'password' => 'password',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertAuthenticated();
    }

    public function test_inactive_users_cannot_authenticate(): void
    {
        $user = User::factory()->inactive()->create();

        $response = $this->post(route('login.store'), [
            'tipo_documento' => $user->tipo_documento->value,
            'numero_documento' => $user->numero_documento,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('numero_documento');
        $this->assertGuest();
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $response = $this->post(route('login.store'), [
            'tipo_documento' => $user->tipo_documento->value,
            'numero_documento' => $user->numero_documento,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_with_two_factor_enabled_are_redirected_to_two_factor_challenge(): void
    {
        if (! Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two-factor authentication is not enabled.');
        }

        $user = User::factory()->withTwoFactor()->create();

        $response = $this->post(route('login.store'), [
            'tipo_documento' => $user->tipo_documento->value,
            'numero_documento' => $user->numero_documento,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('two-factor.login'));
        $this->assertGuest();
    }

    public function test_livewire_login_redirects_users_with_two_factor_enabled_to_challenge(): void
    {
        $user = User::factory()->withTwoFactor()->create();

        Livewire::test(Login::class)
            ->set('tipo_documento', $user->tipo_documento->value)
            ->set('numero_documento', $user->numero_documento)
            ->set('password', 'password')
            ->call('login')
            ->assertRedirect(route('two-factor.login'));

        $this->assertGuest();
        $this->assertEquals($user->id, session('login.id'));
    }

    public function test_livewire_login_authenticates_directly_when_two_factor_disabled(): void
    {
        $user = User::factory()->create();

        Livewire::test(Login::class)
            ->set('tipo_documento', $user->tipo_documento->value)
            ->set('numero_documento', $user->numero_documento)
            ->set('password', 'password')
            ->call('login');

        $this->assertAuthenticatedAs($user);
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('home'));
        $this->assertGuest();
    }
}
