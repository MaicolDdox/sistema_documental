<?php

use App\Http\Controllers\InvestigadorAsociado\EvidenciaController;
use App\Http\Controllers\InvestigadorAsociado\EstadoProductoController;
use App\Http\Controllers\InvestigadorAsociado\ProductoController;
use App\Http\Controllers\InvestigadorAsociado\ProyectoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas para el rol: Investigador Asociado al Grupo de Investigación
|--------------------------------------------------------------------------
| Middleware: auth + ensure.active + role:investigador_asociado
| Prefijo   : /investigador
| Nombres   : investigador.*
|
| Scope crítico: todos los controllers usan el trait InvestigadorContext
| para resolver y filtrar por research_group_id del investigador.
*/

use App\Http\Controllers\InvestigadorAsociado\ReporteController;

Route::middleware(['auth', 'ensure.active', 'training.center', 'role:investigador_asociado'])
    ->prefix('investigador')
    ->name('investigador.')
    ->group(function () {

        // ── Dashboard ────────────────────────────────────────────────
        Route::get('/', function () {
            return view('investigador.dashboard');
        })->name('dashboard');

        // ── Proyectos ────────────────────────────────────────────────
        Route::prefix('proyectos')->name('proyectos.')->group(function () {
            Route::get('/',          [ProyectoController::class, 'index'])->name('index');
            Route::get('/create',    [ProyectoController::class, 'create'])->name('create');
            Route::post('/',         [ProyectoController::class, 'store'])->name('store');
            Route::get('/{proyecto}',       [ProyectoController::class, 'show'])->name('show');
            Route::get('/{proyecto}/edit',  [ProyectoController::class, 'edit'])->name('edit');
            Route::put('/{proyecto}',       [ProyectoController::class, 'update'])->name('update');
            Route::delete('/{proyecto}',    [ProyectoController::class, 'destroy'])->name('destroy');

            // Utilidad AJAX: retorna autores del proyecto en JSON
            Route::get('/{proyecto}/autores', [ProyectoController::class, 'autores'])->name('autores');

            // ── Evidencias de proyecto ────────────────────────────────
            Route::post('/{proyecto}/evidencias', [EvidenciaController::class, 'storeProyecto'])->name('evidencias.store');

            // ── Finalizar proyecto ────────────────────────────────────
            Route::patch('/{proyecto}/finalizar', [ProyectoController::class, 'finalizar'])->name('finalizar');

            // ── Descargar evidencia de proyecto ───────────────────────
            Route::get('/evidencias/{evidencia}/download', [EvidenciaController::class, 'downloadProyecto'])->name('evidencias.download');
        });

        // ── Productos (GroupProducts) ─────────────────────────────────
        Route::prefix('productos')->name('productos.')->group(function () {
            // Bandeja de semilleros y formalización
            Route::get('/bandeja',   [ProductoController::class, 'bandejaSemilleros'])->name('bandeja');
            Route::get('/formalizar/{producto}', [ProductoController::class, 'formalizarSemillero'])->name('formalizar');

            Route::get('/',          [ProductoController::class, 'index'])->name('index');
            Route::get('/create',    [ProductoController::class, 'create'])->name('create');
            Route::post('/',         [ProductoController::class, 'store'])->name('store');
            Route::get('/{producto}',       [ProductoController::class, 'show'])->name('show');
            Route::get('/{producto}/edit',  [ProductoController::class, 'edit'])->name('edit');
            Route::put('/{producto}',       [ProductoController::class, 'update'])->name('update');
            Route::delete('/{producto}',    [ProductoController::class, 'destroy'])->name('destroy');

            // ── Evidencias de producto ────────────────────────────────
            Route::post('/{producto}/evidencias', [EvidenciaController::class, 'storeProducto'])->name('evidencias.store');
        });

        // ── Eliminación de evidencias (rutas planas para DELETE por ID) ──
        Route::prefix('evidencias')->name('evidencias.')->group(function () {
            Route::delete('/producto/{evidencia}', [EvidenciaController::class, 'destroyProducto'])->name('producto.destroy');
            Route::delete('/proyecto/{evidencia}', [EvidenciaController::class, 'destroyProyecto'])->name('proyecto.destroy');
        });

        // ── Estado y seguimiento de productos ────────────────────────
        Route::get('/estados', [EstadoProductoController::class, 'index'])->name('estados.index');

        // ── Reportes ──────────────────────────────────────────────────
        Route::prefix('reportes')->name('reportes.')->group(function () {
            Route::get('/',            [ReporteController::class, 'index'])->name('index');
            Route::get('/estado',      [ReporteController::class, 'productosPorEstado'])->name('estado');
            Route::get('/exportar-csv',[ReporteController::class, 'exportarCsv'])->name('exportar.csv');
            Route::get('/exportar-pdf',[ReporteController::class, 'exportarPdf'])->name('exportar.pdf');
        });
    });
