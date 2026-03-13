<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            \Illuminate\Support\Facades\Route::middleware('web')
                ->group(base_path('routes/director_semilleros.php'));
            \Illuminate\Support\Facades\Route::middleware('web')
                ->group(base_path('routes/lider_semillero.php'));
            \Illuminate\Support\Facades\Route::middleware('web')
                ->group(base_path('routes/admin.php'));
            \Illuminate\Support\Facades\Route::middleware('web')
                ->group(base_path('routes/asesor_semillero.php'));
            \Illuminate\Support\Facades\Route::middleware('web')
                ->group(base_path('routes/director_investigacion.php'));
            \Illuminate\Support\Facades\Route::middleware('web')
                ->group(base_path('routes/investigador.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'ensure.active' => \App\Http\Middleware\EnsureUserIsActive::class,
            'redirect.director' => \App\Http\Middleware\RedirectDirectorToModule::class,
            'no.back' => \App\Http\Middleware\PreventBackHistory::class,
        ]);

        // Aplica no-cache a todas las rutas web para evitar que el botón "atrás"
        // muestre páginas protegidas tras cerrar sesión
        $middleware->web(append: [
            \App\Http\Middleware\PreventBackHistory::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
