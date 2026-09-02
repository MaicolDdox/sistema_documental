<?php

use App\Http\Controllers\LiderProyecto\AprendizController;
use App\Http\Controllers\LiderProyecto\CoinvestigadorController;
use App\Http\Controllers\LiderProyecto\DashboardController;
use App\Http\Controllers\LiderProyecto\EvidenciaController;
use App\Http\Controllers\LiderProyecto\ReporteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas para el rol Líder de Proyecto (rediseño de roles)
|--------------------------------------------------------------------------
| lider_proyecto no tiene permisos Spatie propios (rol nuevo, sin
| funcionalidad asignada todavía en la matriz de permisos) — el acceso se
| protege solo por rol, y cada controlador resuelve "mi proyecto" via
| LiderProyectoContext, igual que el patrón usado en el módulo SuperAdmin.
*/

Route::middleware(['auth', 'ensure.active', 'training.center', 'role:lider_proyecto', 'active_role:lider_proyecto'])
    ->prefix('lider-proyecto')
    ->name('lider-proyecto.')
    ->group(function () {

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('evidencias', [EvidenciaController::class, 'index'])->name('evidencias.index');
        Route::post('evidencias', [EvidenciaController::class, 'store'])->name('evidencias.store');
        Route::get('evidencias/{evidencia}/descargar', [EvidenciaController::class, 'descargar'])->name('evidencias.descargar');
        Route::delete('evidencias/{evidencia}', [EvidenciaController::class, 'destroy'])->name('evidencias.destroy');

        Route::get('aprendices', [AprendizController::class, 'index'])->name('aprendices.index');
        Route::post('aprendices', [AprendizController::class, 'store'])->name('aprendices.store');
        Route::put('aprendices/{aprendiz}', [AprendizController::class, 'update'])->name('aprendices.update');
        Route::delete('aprendices/{aprendiz}', [AprendizController::class, 'destroy'])->name('aprendices.destroy');

        Route::get('co-investigadores', [CoinvestigadorController::class, 'index'])->name('coinvestigadores.index');
        Route::post('co-investigadores', [CoinvestigadorController::class, 'store'])->name('coinvestigadores.store');
        Route::delete('co-investigadores/{coinvestigador}', [CoinvestigadorController::class, 'destroy'])->name('coinvestigadores.destroy');

        Route::get('reporte', [ReporteController::class, 'descargar'])->name('reporte.descargar');
    });
