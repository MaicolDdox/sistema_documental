<?php

use App\Http\Controllers\DirectorSemilleros\DashboardController;
use App\Http\Controllers\DirectorSemilleros\DocumentoSemilleroController;
use App\Http\Controllers\DirectorSemilleros\LiderSemilleroController;
use App\Http\Controllers\DirectorSemilleros\ReporteSemilleroController;
use App\Http\Controllers\DirectorSemilleros\RevisionProductoController;
use App\Http\Controllers\DirectorSemilleros\SemilleroController;
use App\Http\Controllers\DirectorSemilleros\VinculacionSemilleroLiderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas para el rol Director de Semilleros
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'ensure.active', 'training.center', 'role:director_semilleros', 'active_role:director_semilleros'])
    ->prefix('director-semilleros')
    ->name('dir-sem.')
    ->group(function () {

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Módulo Semilleros
        Route::resource('semilleros', SemilleroController::class);
        Route::post('semilleros/{semillero}/toggle-estado', [SemilleroController::class, 'toggleEstado'])->name('semilleros.toggle-estado');
        Route::post('semilleros/{semillero}/reasignar-lider', [SemilleroController::class, 'reasignarLider'])->name('semilleros.reasignar-lider');

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

        // Módulo Productos: revisión definitiva (2ª etapa, rediseño de roles)
        Route::get('productos', [RevisionProductoController::class, 'index'])->name('productos.index');
        Route::patch('productos/{evidencia}/aprobar', [RevisionProductoController::class, 'aprobar'])->name('productos.aprobar');
        Route::patch('productos/{evidencia}/rechazar', [RevisionProductoController::class, 'rechazar'])->name('productos.rechazar');
        Route::get('productos/{evidencia}/descargar', [RevisionProductoController::class, 'descargar'])->name('productos.descargar');

        // Evidencias de proyecto (tab dentro del detalle del semillero)
        Route::get('evidencias/{evidencia}/descargar', [SemilleroController::class, 'descargarEvidencia'])->name('evidencias.descargar');

        // Documentos por semillero (tab dentro del detalle del semillero, sin listado propio)
        Route::post('documentos', [DocumentoSemilleroController::class, 'store'])->name('documentos.store');
        Route::delete('documentos/{documento}', [DocumentoSemilleroController::class, 'destroy'])->name('documentos.destroy');
        Route::get('documentos/{documento}/descargar', [SemilleroController::class, 'descargarDocumento'])->name('documentos.descargar');

        // Reportes (tab dentro del detalle del semillero, sin listado propio)
        Route::post('reportes/exportar', [ReporteSemilleroController::class, 'exportar'])->name('reportes.exportar');
    });
