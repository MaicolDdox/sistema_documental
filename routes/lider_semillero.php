<?php

use App\Http\Controllers\LiderSemillero\ArchivosSemilleroController;
use App\Http\Controllers\LiderSemillero\AprendicesController;
use App\Http\Controllers\LiderSemillero\DocInternaController;
use App\Http\Controllers\LiderSemillero\AsesoresController;
use App\Http\Controllers\LiderSemillero\DashboardController;
use App\Http\Controllers\LiderSemillero\InfoSemilleroController;
use App\Http\Controllers\LiderSemillero\IntegrantesController;
use App\Http\Controllers\LiderSemillero\ProductosController;
use App\Http\Controllers\LiderSemillero\ProyectosController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas para el rol Líder de Semillero
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:lider_semillero'])
    ->prefix('lider-semillero')
    ->name('lider-sem.')
    ->group(function () {

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Info del Semillero (editar nombre, logo, descripción)
        Route::get('info-semillero', [InfoSemilleroController::class, 'edit'])->name('info-semillero');
        Route::put('info-semillero', [InfoSemilleroController::class, 'update'])->name('info-semillero.update');

        // Integrantes del semillero (tarjetas con estado Con/Sin proyecto)
        Route::get('integrantes', [IntegrantesController::class, 'index'])->name('integrantes');

        // Asesores del semillero (tabla, estado activo/inactivo, activar/desactivar, crear, editar, eliminar)
        Route::get('asesores', [AsesoresController::class, 'index'])->name('asesores');
        Route::post('asesores', [AsesoresController::class, 'store'])->name('asesores.store');
        Route::put('asesores/{vinculo}', [AsesoresController::class, 'update'])->name('asesores.update');
        Route::delete('asesores/{vinculo}', [AsesoresController::class, 'destroy'])->name('asesores.destroy');
        Route::patch('asesores/{vinculo}/toggle', [AsesoresController::class, 'toggle'])->name('asesores.toggle');

        // Proyectos vinculados al semillero (solo visualización)
        Route::get('proyectos', [ProyectosController::class, 'index'])->name('proyectos');

        // Productos del semillero: listar, registrar, aprobar/rechazar
        Route::get('productos', [ProductosController::class, 'index'])->name('productos');
        Route::post('productos', [ProductosController::class, 'store'])->name('productos.store');
        Route::patch('productos/{groupProduct}/aprobar', [ProductosController::class, 'aprobar'])->name('productos.aprobar');
        Route::patch('productos/{groupProduct}/rechazar', [ProductosController::class, 'rechazar'])->name('productos.rechazar');

        // Aprendices: registro y vinculación
        Route::get('aprendices', [AprendicesController::class, 'index'])->name('aprendices');
        Route::post('aprendices/registrar', [AprendicesController::class, 'registrar'])->name('aprendices.registrar');
        Route::post('aprendices/vincular', [AprendicesController::class, 'vincular'])->name('aprendices.vincular');
        Route::patch('aprendices/desvincular/{projectAuthor}', [AprendicesController::class, 'desvincular'])->name('aprendices.desvincular');

        // Archivos del semillero (tabla: seedling_files)
        Route::get('archivos', [ArchivosSemilleroController::class, 'index'])->name('archivos');
        Route::post('archivos', [ArchivosSemilleroController::class, 'store'])->name('archivos.store');
        Route::delete('archivos/{archivo}', [ArchivosSemilleroController::class, 'destroy'])->name('archivos.destroy');

        // Documentación interna (actas, informes)
        Route::get('doc-interna', [DocInternaController::class, 'index'])->name('doc-interna');
        Route::post('doc-interna', [DocInternaController::class, 'store'])->name('doc-interna.store');
        Route::delete('doc-interna/{documento}', [DocInternaController::class, 'destroy'])->name('doc-interna.destroy');
    });
