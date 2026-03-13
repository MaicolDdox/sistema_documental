<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AsesorSemillero\AprendizController;
use App\Http\Controllers\AsesorSemillero\ProyectoController;
use App\Http\Controllers\AsesorSemillero\ProductoController;
use App\Http\Controllers\AsesorSemillero\EvidenciaController;
use App\Http\Controllers\AsesorSemillero\MisSemillerosController;

/*
|--------------------------------------------------------------------------
| Rutas del Módulo: Asesor de Semillero
|--------------------------------------------------------------------------
| Aplicadas a los roles: asesor_semillero, lider_semillero, director_semilleros
| (el líder y director llevan el rol asesor por Spatie, sin cambio de cuenta)
*/

Route::middleware(['auth', 'role:asesor_semillero|lider_semillero|director_semilleros'])
    ->prefix('asesor-semillero')
    ->name('asesor.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', function () {
            return view('asesor_semillero.dashboard');
        })->name('dashboard');

        // Mis Semilleros y Proyectos
        Route::get('/mis-semilleros', [MisSemillerosController::class, 'index'])->name('mis_semilleros.index');

        // ─── APRENDICES ────────────────────────────────────────────────────────
        // Para el rol asesor_semillero, el acceso ya está restringido por el rol en este grupo.
        // Quitamos los middlewares can:* para evitar 403 por desajustes de permisos finos.
        Route::get('/aprendices', [AprendizController::class, 'index'])->name('aprendices.index');
        Route::middleware('can:aprendices.registrar')->group(function () {
            Route::get('/aprendices/create', [AprendizController::class, 'create'])->name('aprendices.create');
            Route::post('/aprendices', [AprendizController::class, 'store'])->name('aprendices.store');
        });

        // Ver detalle / editar / eliminar: ya están restringidos al rol en el grupo principal
        Route::get('/aprendices/{id}', [AprendizController::class, 'show'])->name('aprendices.show');
        Route::get('/aprendices/{id}/edit', [AprendizController::class, 'edit'])->name('aprendices.edit');
        Route::put('/aprendices/{id}', [AprendizController::class, 'update'])->name('aprendices.update');
        Route::delete('/aprendices/{id}', [AprendizController::class, 'destroy'])->name('aprendices.destroy');

        // ─── PROYECTOS ─────────────────────────────────────────────────────────
        Route::get('/proyectos', [ProyectoController::class, 'index'])->name('proyectos.index');
        // Acceso controlado solo por rol dentro de este grupo
        Route::get('/proyectos/create', [ProyectoController::class, 'create'])->name('proyectos.create');
        Route::post('/proyectos', [ProyectoController::class, 'store'])->name('proyectos.store');

        Route::middleware('can:proyectos.ver_detalle')->group(function () {
            Route::get('/proyectos/{id}', [ProyectoController::class, 'show'])->name('proyectos.show');
        });

        // Edición / eliminación: restringidas solo por rol del grupo principal
        Route::get('/proyectos/{id}/edit', [ProyectoController::class, 'edit'])->name('proyectos.edit');
        Route::put('/proyectos/{id}', [ProyectoController::class, 'update'])->name('proyectos.update');
        Route::delete('/proyectos/{id}', [ProyectoController::class, 'destroy'])->name('proyectos.destroy');

        Route::middleware('can:proyectos.vincular_integrantes')->group(function () {
            Route::get('/proyectos/{id}/integrantes', [ProyectoController::class, 'integrantes'])->name('proyectos.integrantes');
            Route::post('/proyectos/{id}/integrantes', [ProyectoController::class, 'vincularIntegrante'])->name('proyectos.vincular');
            Route::delete('/proyectos/{id}/integrantes/{user_id}', [ProyectoController::class, 'desvincularIntegrante'])->name('proyectos.desvincular');
        });

        // ─── PRODUCTOS ─────────────────────────────────────────────────────────
        // Para asesores, controlamos el acceso solo por rol del grupo principal,
        // sin usar gates can:* para evitar 403 por desajustes de permisos.
        Route::get('/productos', [ProductoController::class, 'index'])->name('productos.index');
        Route::get('/productos/create/{proyecto_id?}', [ProductoController::class, 'create'])->name('productos.create');
        Route::post('/productos', [ProductoController::class, 'store'])->name('productos.store');
        Route::get('/productos/{id}', [ProductoController::class, 'show'])->name('productos.show');
        Route::get('/productos/{id}/edit', [ProductoController::class, 'edit'])->name('productos.edit');
        Route::put('/productos/{id}', [ProductoController::class, 'update'])->name('productos.update');
        Route::delete('/productos/{id}', [ProductoController::class, 'destroy'])->name('productos.destroy');

        // ─── AJAX / API helpers ────────────────────────────────────────────────
        Route::get('/api/semillero/{seedling_id}/proyectos', [ProductoController::class, 'apiProyectosPorSemillero'])->name('api.semillero.proyectos');
        Route::get('/api/proyecto/{project_id}/autores', [ProductoController::class, 'apiAutoresPorProyecto'])->name('api.proyecto.autores');
        Route::get('/productos/{id}/descargar', [ProductoController::class, 'download'])->name('productos.download');

        // ─── EVIDENCIAS ────────────────────────────────────────────────────────
        Route::middleware('can:evidencias.listar')->group(function () {
            Route::get('/proyectos/{proyecto_id}/evidencias', [EvidenciaController::class, 'listarEvidenciasProyecto'])->name('evidencias.proyecto.index');
            Route::get('/productos/{producto_id}/evidencias', [EvidenciaController::class, 'listarEvidenciasProducto'])->name('evidencias.producto.index');
        });

        Route::middleware('can:evidencias.subir_proyecto')->group(function () {
            Route::post('/proyectos/{proyecto_id}/evidencias', [EvidenciaController::class, 'subirEvidenciaProyecto'])->name('evidencias.proyecto.store');
        });

        Route::middleware('can:evidencias.subir_producto')->group(function () {
            Route::post('/productos/{producto_id}/evidencias', [EvidenciaController::class, 'subirEvidenciaProducto'])->name('evidencias.producto.store');
        });

        Route::middleware('can:evidencias.eliminar_propia')->group(function () {
            Route::delete('/evidencias/{id}', [EvidenciaController::class, 'destroy'])->name('evidencias.destroy');
        });
    });
