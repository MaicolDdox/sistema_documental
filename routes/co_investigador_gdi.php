<?php

use App\Http\Controllers\CoinvestigadorGdi\DashboardController;
use App\Http\Controllers\CoinvestigadorGdi\MincienciasProductoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas para el rol Co-investigador GDI (reforma GDI/SDI)
|--------------------------------------------------------------------------
| co_investigador_gdi SÍ tiene training_center_id (a diferencia del
| co_investigador original) pero sin permisos Spatie propios — el acceso se
| protege solo por rol, mismo patrón que lider_proyecto.
*/

Route::middleware(['auth', 'ensure.active', 'training.center', 'role:co_investigador_gdi', 'active_role:co_investigador_gdi'])
    ->prefix('co-investigador-gdi')
    ->name('co-investigador-gdi.')
    ->group(function () {

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

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
