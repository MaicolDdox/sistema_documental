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

        // ──────────────────────────────────────────────────────────────────
        // Gate::before: Super Admin y Admin del Sistema tienen acceso total
        // ──────────────────────────────────────────────────────────────────
        Gate::before(function ($user, $ability) {
            return $user->hasAnyRole(['super_administrador', 'administrador_sistema']) ? true : null;
        });

        // ──────────────────────────────────────────────────────────────────
        // Políticas de modelos
        // ──────────────────────────────────────────────────────────────────

        // Director de Investigación
        Gate::policy(ResearchGroup::class, DirectorPolicy::class);

        // Investigador Asociado — GroupProduct (view, update, delete, subirEvidencia)
        // NOTA: Solo puede haber UNA política por modelo/clase. ProductoPolicy cubre
        // las acciones del Investigador sobre sus propios GroupProducts.
        Gate::policy(GroupProduct::class, ProductoPolicy::class);

        // Project
        Gate::policy(Project::class, ProyectoPolicy::class);

        // ResearchGroup (extiende DirectorPolicy — el último registrado prevalece)
        Gate::policy(ResearchGroup::class, GrupoPolicy::class);

        // Habilidad 'revisar' para el Director de Investigación sobre GroupProduct.
        // Se define como Gate::define() porque Gate::policy() no admite dos policies
        // por el mismo modelo. El DirectorPolicy se llama directamente aquí.
        Gate::define('revisar', function (User $user, GroupProduct $producto) {
            return (new GroupProductPolicy())->revisar($user, $producto);
        });
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
