<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        // ──────────────────────────────────────────────────────────────────
        // Gate::before: Super Admin y Admin del Sistema tienen acceso total
        // ──────────────────────────────────────────────────────────────────
        Gate::before(function ($user, $ability) {
            return $user->hasAnyRole(['super_administrador', 'administrador_sistema']) ? true : null;
        });

        // El rediseño de roles usa autorización por permiso (Spatie) o por
        // dueño del recurso directamente en los controladores, no Policies
        // de modelo — las políticas anteriores (Director/Grupo/Producto/
        // GroupProduct/Proyecto) quedaron ligadas a entidades que ya no
        // existen (ResearchGroup, Product, GroupProduct) y se eliminaron.
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null
        );
    }
}
