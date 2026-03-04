<?php

use App\Http\Controllers\DirectorSemilleros\DocumentoSemilleroController;
use App\Http\Controllers\DirectorSemilleros\LiderSemilleroController;
use App\Http\Controllers\DirectorSemilleros\ReporteSemilleroController;
use App\Http\Controllers\DirectorSemilleros\SemilleroController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas para el rol Director de Semilleros
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:director_semilleros'])
    ->prefix('director-semilleros')
    ->name('dir-sem.')
    ->group(function () {
        
        // Vista de dashboard (por ahora apunta a un método estático o al index de semilleros)
        Route::get('/', function () {
            // Como no hay DashboardController especificado, renderizamos una vista o redirigimos a semilleros.
            // Para el alcance, usar una ruta con closure o un controller. La instrucción pide un dashboard.blade.php
            // "resources/views/director_semilleros/dashboard.blade.php:" con accesos rápidos
            // Redirigiremos a SemilleroController@dashboard si se prefiere, o dejaremos closure.
            return view('director_semilleros.dashboard');
        })->name('dashboard');

        // Módulo Semilleros
        Route::resource('semilleros', SemilleroController::class);
        Route::post('semilleros/{semillero}/toggle-estado', [SemilleroController::class, 'toggleEstado'])->name('semilleros.toggle-estado');
        Route::post('semilleros/{semillero}/reasignar-lider', [SemilleroController::class, 'reasignarLider'])->name('semilleros.reasignar-lider');

        // Módulo Líderes de Semillero
        Route::get('lideres', [LiderSemilleroController::class, 'index'])->name('lideres.index');
        Route::get('lideres/create', [LiderSemilleroController::class, 'create'])->name('lideres.create');
        Route::post('lideres', [LiderSemilleroController::class, 'store'])->name('lideres.store');

        // Módulo Documentos Institucionales
        Route::resource('documentos', DocumentoSemilleroController::class)->only(['index', 'create', 'store', 'destroy']);

        // Módulo Reportes
        Route::get('reportes', [ReporteSemilleroController::class, 'index'])->name('reportes.index');
        Route::post('reportes/exportar', [ReporteSemilleroController::class, 'exportar'])->name('reportes.exportar');
    });
