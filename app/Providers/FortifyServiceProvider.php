<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Enums\EstadoEnum;
use App\Http\Responses\LoginResponse;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Sobrescribir la respuesta de login para redirección por rol
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureActions();
        $this->configureViews();
        $this->configureAuthentication();
        $this->configureRateLimiting();
    }

    /**
     * Configure Fortify actions.
     */
    private function configureActions(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::createUsersUsing(CreateNewUser::class);
    }

    /**
     * Configure Fortify views.
     */
    private function configureViews(): void
    {
        Fortify::loginView(fn () => view('livewire.auth.login'));
        Fortify::twoFactorChallengeView(fn () => view('livewire.auth.two-factor-challenge'));
        Fortify::confirmPasswordView(fn () => view('livewire.auth.confirm-password'));
        Fortify::resetPasswordView(fn () => view('livewire.auth.reset-password'));
        Fortify::requestPasswordResetLinkView(fn () => view('livewire.auth.forgot-password'));
    }

    /**
     * Configure custom authentication logic:
     * - Login with tipo_documento + numero_documento
     * - Block inactive users BEFORE checking password
     */
    private function configureAuthentication(): void
    {
        Fortify::authenticateUsing(function (Request $request) {
            $tipoDoc   = $request->input('tipo_documento');
            $numDoc    = $request->input('numero_documento');

            // 1. Buscar usuario por tipo_documento + numero_documento
            $user = User::where('tipo_documento', $tipoDoc)
                        ->where('numero_documento', $numDoc)
                        ->first();

            // 2. Si no existe el usuario, retornar null
            if (! $user) {
                return null;
            }

            // 3. Verificar estado ANTES de la contraseña
            if ($user->estado !== EstadoEnum::Activo) {
                throw ValidationException::withMessages([
                    'numero_documento' => [__('Tu cuenta no está activa. Contacta al administrador.')],
                ]);
            }

            // 4. Verificar contraseña
            if (Hash::check($request->input('password'), $user->password)) {
                return $user;
            }

            return null;
        });
    }

    /**
     * Configure rate limiting.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = $request->input('numero_documento') . '|' . $request->ip();

            return Limit::perMinute(5)->by($throttleKey);
        });
    }
}
