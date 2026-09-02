<?php

use App\Http\Controllers\Coinvestigador\DashboardController;
use App\Http\Controllers\Coinvestigador\MincienciasProductoController;
use App\Http\Controllers\Coinvestigador\ProyectoController;
use App\Http\Controllers\Coinvestigador\ReporteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas para el rol Co-investigador (rediseño de roles)
|--------------------------------------------------------------------------
| co_investigador no tiene training_center_id (ver
| TrainingCenterAccess::CENTRO_BOUND_ROLE_NAMES) ni permisos Spatie propios
| — el acceso se protege solo por rol, mismo patrón que lider_proyecto.
| A diferencia de lider_proyecto, un co_investigador puede estar vinculado a
| VARIOS proyectos a la vez, por eso las rutas de proyecto llevan el
| {proyecto} explícito en vez de resolver "mi proyecto" único.
*/

Route::middleware(['auth', 'ensure.active', 'role:co_investigador', 'active_role:co_investigador'])
    ->prefix('co-investigador')
    ->name('co-investigador.')
    ->group(function () {

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('proyectos/{proyecto}', [ProyectoController::class, 'show'])->name('proyectos.show');
        Route::post('proyectos/{proyecto}/evidencias', [ProyectoController::class, 'storeEvidencia'])->name('proyectos.evidencias.store');
        Route::get('evidencias/{evidencia}/descargar', [ProyectoController::class, 'descargarEvidencia'])->name('evidencias.descargar');
        Route::delete('evidencias/{evidencia}', [ProyectoController::class, 'destroyEvidencia'])->name('evidencias.destroy');

        Route::get('reporte', [ReporteController::class, 'descargar'])->name('reporte.descargar');

        // Producto Minciencias — 100% personal del co-investigador, sin
        // vínculo a semillero, proyecto ni líder de proyecto.
        Route::get('productos', [MincienciasProductoController::class, 'index'])->name('productos.index');
        Route::get('productos/crear', [MincienciasProductoController::class, 'create'])->name('productos.create');
        Route::post('productos', [MincienciasProductoController::class, 'store'])->name('productos.store');
        Route::get('productos/{producto}', [MincienciasProductoController::class, 'show'])->name('productos.show');
        Route::get('productos/{producto}/editar', [MincienciasProductoController::class, 'edit'])->name('productos.edit');
        Route::put('productos/{producto}', [MincienciasProductoController::class, 'update'])->name('productos.update');
        Route::delete('productos/{producto}', [MincienciasProductoController::class, 'destroy'])->name('productos.destroy');
        Route::post('productos/{producto}/archivos', [MincienciasProductoController::class, 'storeArchivo'])->name('productos.archivos.store');
        Route::get('archivos/{archivo}/descargar', [MincienciasProductoController::class, 'descargarArchivo'])->name('archivos.descargar');
        Route::delete('archivos/{archivo}', [MincienciasProductoController::class, 'destroyArchivo'])->name('archivos.destroy');
    });
