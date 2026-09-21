<?php

use App\Http\Controllers\CoinvestigadorSdi\DashboardController;
use App\Http\Controllers\CoinvestigadorSdi\ProyectoController;
use App\Http\Controllers\CoinvestigadorSdi\ReporteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas para el rol Co-investigador SDI (reforma GDI/SDI)
|--------------------------------------------------------------------------
| co_investigador_sdi SÍ tiene training_center_id (a diferencia del
| co_investigador original) pero sin permisos Spatie propios — el acceso se
| protege solo por rol, mismo patrón que lider_proyecto. Puede estar
| vinculado a VARIOS proyectos a la vez, por eso las rutas de proyecto
| llevan el {proyecto} explícito.
*/

Route::middleware(['auth', 'ensure.active', 'training.center', 'role:co_investigador_sdi', 'active_role:co_investigador_sdi'])
    ->prefix('co-investigador-sdi')
    ->name('co-investigador-sdi.')
    ->group(function () {

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('proyectos/{proyecto}', [ProyectoController::class, 'show'])->name('proyectos.show');
        Route::post('proyectos/{proyecto}/evidencias', [ProyectoController::class, 'storeEvidencia'])->name('proyectos.evidencias.store');
        Route::get('evidencias/{evidencia}/descargar', [ProyectoController::class, 'descargarEvidencia'])->name('evidencias.descargar');
        Route::delete('evidencias/{evidencia}', [ProyectoController::class, 'destroyEvidencia'])->name('evidencias.destroy');

        Route::get('reporte', [ReporteController::class, 'descargar'])->name('reporte.descargar');
    });
