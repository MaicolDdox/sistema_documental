<?php

use App\Http\Controllers\DirectorSemilleros\AsignarRolController;
use App\Http\Controllers\DirectorSemilleros\DashboardController;
use App\Http\Controllers\DirectorSemilleros\DocumentoSemilleroController;
use App\Http\Controllers\DirectorSemilleros\LiderSemilleroController;
use App\Http\Controllers\DirectorSemilleros\ReporteSemilleroController;
use App\Http\Controllers\DirectorSemilleros\SemilleroController;
use App\Http\Controllers\DirectorSemilleros\VinculacionSemilleroLiderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas para el rol Director de Semilleros
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'ensure.active', 'training.center', 'role:director_semilleros'])
    ->prefix('director-semilleros')
    ->name('dir-sem.')
    ->group(function () {
        
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Módulo Semilleros
        Route::resource('semilleros', SemilleroController::class);
        Route::post('semilleros/{semillero}/toggle-estado', [SemilleroController::class, 'toggleEstado'])->name('semilleros.toggle-estado');
        Route::post('semilleros/{semillero}/reasignar-lider', [SemilleroController::class, 'reasignarLider'])->name('semilleros.reasignar-lider');

        // Asignar Roles (vista reutilizada, solo Líder de Semillero)
        Route::get('asignar-roles', [AsignarRolController::class, 'index'])->name('asignar-roles.index');
        Route::post('asignar-roles', [AsignarRolController::class, 'store'])->name('asignar-roles.store');

        // Módulo Líderes de Semillero
        Route::get('lideres', [LiderSemilleroController::class, 'index'])->name('lideres.index');
        Route::get('lideres/create', [LiderSemilleroController::class, 'create'])->name('lideres.create');
        Route::post('lideres', [LiderSemilleroController::class, 'store'])->name('lideres.store');
        Route::get('lideres/{lider}', [LiderSemilleroController::class, 'show'])->name('lideres.show');
        Route::get('lideres/{lider}/edit', [LiderSemilleroController::class, 'edit'])->name('lideres.edit');
        Route::put('lideres/{lider}', [LiderSemilleroController::class, 'update'])->name('lideres.update');
        Route::post('lideres/{lider}/toggle-estado', [LiderSemilleroController::class, 'toggleEstado'])->name('lideres.toggle-estado');
        Route::delete('lideres/{lider}', [LiderSemilleroController::class, 'destroy'])->name('lideres.destroy');
        Route::get('vinculaciones', [VinculacionSemilleroLiderController::class, 'index'])->name('vinculaciones.index');
        Route::put('vinculaciones/{semillero}', [VinculacionSemilleroLiderController::class, 'update'])->name('vinculaciones.update');

        // Módulo Documentos Institucionales
        Route::resource('documentos', DocumentoSemilleroController::class)->only(['index', 'create', 'store', 'destroy']);

        // Módulo Reportes
        Route::get('reportes', [ReporteSemilleroController::class, 'index'])->name('reportes.index');
        Route::post('reportes/exportar', [ReporteSemilleroController::class, 'exportar'])->name('reportes.exportar');
    });
