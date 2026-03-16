<?php

use App\Http\Controllers\DirectorInvestigacion\DocumentoGrupoController;
use App\Http\Controllers\DirectorInvestigacion\InvestigadorController;
use App\Http\Controllers\DirectorInvestigacion\ProductoRevisionController;
use App\Http\Controllers\DirectorInvestigacion\ReporteGrupoController;
use App\Http\Controllers\DirectorInvestigacion\MacroproyectoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas para el rol: Director de Grupo de Investigación
|--------------------------------------------------------------------------
| Middleware: auth + ensure.active + role:director_investigacion
| Prefijo   : /director
| Nombres   : director.*
|
| Scope crítico: todos los controllers usan el trait DirectorContext
| para resolver y filtrar por research_group_id del director.
*/

Route::middleware(['auth', 'ensure.active', 'role:director_investigacion'])
    ->prefix('director')
    ->name('director.')
    ->group(function () {

        // ── Dashboard ────────────────────────────────────────────────
        Route::get('/', function () {
            return view('director_investigacion.dashboard');
        })->name('dashboard');

        // ── Investigadores ───────────────────────────────────────────
        Route::prefix('investigadores')->name('investigadores.')->group(function () {
            Route::get('/',                          [InvestigadorController::class, 'index'])->name('index');
            Route::get('/create',                    [InvestigadorController::class, 'create'])->name('create');
            Route::post('/',                         [InvestigadorController::class, 'store'])->name('store');
            Route::patch('/{investigador}/estado',   [InvestigadorController::class, 'toggleEstado'])->name('toggle-estado');
            Route::patch('/{investigador}/rol',      [InvestigadorController::class, 'cambiarRol'])->name('cambiar-rol');
            Route::delete('/{investigador}/desvincular', [InvestigadorController::class, 'desvincular'])->name('desvincular');
        });

        // ── Revisión de Productos ────────────────────────────────────
        Route::prefix('productos')->name('productos.')->group(function () {
            Route::get('/',                            [ProductoRevisionController::class, 'index'])->name('index');
            Route::get('/{producto}',                  [ProductoRevisionController::class, 'show'])->name('show');
            Route::patch('/{producto}/aprobar',        [ProductoRevisionController::class, 'aprobar'])->name('aprobar');
            Route::patch('/{producto}/rechazar',       [ProductoRevisionController::class, 'rechazar'])->name('rechazar');
            Route::patch('/{producto}/en-revision',    [ProductoRevisionController::class, 'cambiarAEnRevision'])->name('en-revision');
        });

        // ── Documentos del Grupo ─────────────────────────────────────
        Route::prefix('documentos')->name('documentos.')->group(function () {
            Route::get('/',                   [DocumentoGrupoController::class, 'index'])->name('index');
            Route::post('/',                  [DocumentoGrupoController::class, 'store'])->name('store');
            Route::delete('/{documento}',     [DocumentoGrupoController::class, 'destroy'])->name('destroy');
        });

        // ── Reportes ─────────────────────────────────────────────────
        Route::prefix('reportes')->name('reportes.')->group(function () {
            Route::get('/',          [ReporteGrupoController::class, 'index'])->name('index');
            Route::post('/exportar', [ReporteGrupoController::class, 'exportar'])->name('exportar');
        });

        // ── Catálogo de Macroproyectos ───────────────────────────────
        Route::resource('macroproyectos', MacroproyectoController::class);
        Route::patch('macroproyectos/{macroproyecto}/activar', [MacroproyectoController::class, 'activar'])
            ->name('macroproyectos.activar');
        Route::patch('macroproyectos/{macroproyecto}/desactivar', [MacroproyectoController::class, 'desactivar'])
            ->name('macroproyectos.desactivar');
    });
