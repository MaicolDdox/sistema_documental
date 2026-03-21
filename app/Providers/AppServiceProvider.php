<?php

namespace App\Providers;

use App\Models\GroupProduct;
use App\Models\Project;
use App\Models\ResearchGroup;
use App\Models\User;
use App\Policies\DirectorPolicy;
use App\Policies\GroupProductPolicy;
use App\Policies\GrupoPolicy;
use App\Policies\ProductoPolicy;
use App\Policies\ProyectoPolicy;
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

        Gate::before(function ($user, $ability) {
            return $user->hasAnyRole(['super_administrador', 'administrador_sistema']) ? true : null;
        });

        // Registrar políticas del módulo Director de Investigación
        Gate::policy(ResearchGroup::class, DirectorPolicy::class);
        Gate::policy(GroupProduct::class, GroupProductPolicy::class);

        // Registrar políticas del módulo Investigador Asociado
        Gate::policy(Project::class, ProyectoPolicy::class);
        Gate::policy(GroupProduct::class, ProductoPolicy::class); // extiende la del Director
        Gate::policy(ResearchGroup::class, GrupoPolicy::class); // extiende la del Director
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
