<?php

use App\Http\Controllers\LiderSemillero\ArchivosSemilleroController;
use App\Http\Controllers\LiderSemillero\DashboardController;
use App\Http\Controllers\LiderSemillero\DocInternaController;
use App\Http\Controllers\LiderSemillero\InfoSemilleroController;
use App\Http\Controllers\LiderSemillero\LiderProyectoController;
use App\Http\Controllers\LiderSemillero\ProductosController;
use App\Http\Controllers\LiderSemillero\ProyectosController;
use App\Http\Controllers\LiderSemillero\ReporteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas para el rol Líder de Semillero
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'ensure.active', 'training.center', 'role:lider_semillero', 'active_role:lider_semillero'])
    ->prefix('lider-semillero')
    ->name('lider-sem.')
    ->group(function () {

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Info del Semillero (solo consulta)
        Route::get('info-semillero', [InfoSemilleroController::class, 'edit'])->name('info-semillero');

        // Líderes de Proyecto (matriz de creación exclusiva del rediseño de roles)
        Route::get('lider-proyecto', [LiderProyectoController::class, 'index'])->name('lider-proyecto.index');
        Route::get('lider-proyecto/crear', [LiderProyectoController::class, 'create'])->name('lider-proyecto.create');
        Route::post('lider-proyecto', [LiderProyectoController::class, 'store'])->name('lider-proyecto.store');
        Route::post('lider-proyecto/{liderProyecto}/toggle-estado', [LiderProyectoController::class, 'toggleEstado'])->name('lider-proyecto.toggle-estado');

        // Proyectos del semillero: crear/editar (exclusivo del Líder de Semillero)
        Route::get('proyectos', [ProyectosController::class, 'index'])->name('proyectos');
        Route::get('proyectos/crear', [ProyectosController::class, 'create'])->name('proyectos.create');
        Route::post('proyectos', [ProyectosController::class, 'store'])->name('proyectos.store');
        Route::get('proyectos/{proyecto}/editar', [ProyectosController::class, 'edit'])->name('proyectos.edit');
        Route::put('proyectos/{proyecto}', [ProyectosController::class, 'update'])->name('proyectos.update');
        Route::get('proyectos/{proyecto}', [ProyectosController::class, 'show'])->name('proyectos.show');
        Route::get('evidencias/{evidencia}/descargar', [ProyectosController::class, 'descargarEvidencia'])->name('evidencias.descargar');

        // Productos del semillero: revisión de 1ra etapa (evidencia de producto final)
        Route::get('productos', [ProductosController::class, 'index'])->name('productos');
        Route::patch('productos/{evidencia}/aprobar', [ProductosController::class, 'aprobar'])->name('productos.aprobar');
        Route::patch('productos/{evidencia}/rechazar', [ProductosController::class, 'rechazar'])->name('productos.rechazar');
        Route::get('productos/{evidencia}/descargar', [ProductosController::class, 'descargar'])->name('productos.descargar');

        // Archivos del semillero (tabla: seedling_files)
        Route::get('archivos', [ArchivosSemilleroController::class, 'index'])->name('archivos');
        Route::post('archivos', [ArchivosSemilleroController::class, 'store'])->name('archivos.store');
        Route::get('archivos/{archivo}/descargar', [ArchivosSemilleroController::class, 'descargar'])->name('archivos.descargar');
        Route::delete('archivos/{archivo}', [ArchivosSemilleroController::class, 'destroy'])->name('archivos.destroy');

        // Documentación interna (actas, informes)
        Route::get('doc-interna', [DocInternaController::class, 'index'])->name('doc-interna');
        Route::post('doc-interna', [DocInternaController::class, 'store'])->name('doc-interna.store');
        Route::get('doc-interna/{documento}/descargar', [DocInternaController::class, 'descargar'])->name('doc-interna.descargar');
        Route::delete('doc-interna/{documento}', [DocInternaController::class, 'destroy'])->name('doc-interna.destroy');

        // Reportes
        Route::get('reportes', [ReporteController::class, 'index'])->name('reportes.index');
        Route::post('reportes/exportar', [ReporteController::class, 'exportar'])->name('reportes.exportar');
    });
