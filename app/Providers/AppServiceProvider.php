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

        // ──────────────────────────────────────────────────────────────────
        // Policies de modelo — reintroducidas en BUG-20260914-004
        // ──────────────────────────────────────────────────────────────────
        // Las Policies anteriores se eliminaron por quedar ligadas a entidades
        // ya removidas (ResearchGroup, GroupProduct). Se reintroducen ahora
        // para centralizar la regla de multi-tenancy por training_center_id
        // tras detectar duplicación manual que causó los bugs 001-003 del
        // 2026-09-14. Gate::before (línea 32) ya otorga acceso total a
        // super_administrador y administrador_sistema; estas Policies solo
        // se ejecutan para los demás roles. Se registran explícitamente
        // (en vez de depender de auto-discovery) para que el mapeo quede
        // documentado y auditable desde un único lugar.
        Gate::policy(\App\Models\Seedling::class, \App\Policies\SeedlingPolicy::class);
        Gate::policy(\App\Models\Project::class, \App\Policies\ProjectPolicy::class);
        Gate::policy(\App\Models\GrupoInvestigacion::class, \App\Policies\GrupoInvestigacionPolicy::class);
        Gate::policy(\App\Models\User::class, \App\Policies\UserPolicy::class);
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
