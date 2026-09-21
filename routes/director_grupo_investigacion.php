<?php

use App\Http\Controllers\DirectorGrupoInvestigacion\CoInvestigadorController;
use App\Http\Controllers\DirectorGrupoInvestigacion\DashboardController;
use App\Http\Controllers\DirectorGrupoInvestigacion\GrupoInvestigacionController;
use App\Http\Controllers\DirectorGrupoInvestigacion\MincienciasProductoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas para el rol Director de Grupo de Investigación (reforma GDI/SDI)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'ensure.active', 'training.center', 'role:director_grupo_investigacion', 'active_role:director_grupo_investigacion'])
    ->prefix('director-grupo-investigacion')
    ->name('director-grupo-investigacion.')
    ->group(function () {

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('grupo', [GrupoInvestigacionController::class, 'edit'])->name('grupo.edit');
        Route::put('grupo', [GrupoInvestigacionController::class, 'update'])->name('grupo.update');

        Route::get('co-investigadores', [CoInvestigadorController::class, 'index'])->name('co-investigadores.index');
        Route::get('co-investigadores/crear', [CoInvestigadorController::class, 'create'])->name('co-investigadores.create');
        Route::post('co-investigadores', [CoInvestigadorController::class, 'store'])->name('co-investigadores.store');

        Route::get('minciencias', [MincienciasProductoController::class, 'index'])->name('minciencias.index');
        Route::get('minciencias/{producto}', [MincienciasProductoController::class, 'show'])->name('minciencias.show');
        Route::post('minciencias/{producto}/aprobar', [MincienciasProductoController::class, 'aprobar'])->name('minciencias.aprobar');
        Route::post('minciencias/{producto}/rechazar', [MincienciasProductoController::class, 'rechazar'])->name('minciencias.rechazar');
        Route::get('minciencias-archivos/{archivo}/descargar', [MincienciasProductoController::class, 'descargarArchivo'])->name('minciencias.archivos.descargar');
    });
