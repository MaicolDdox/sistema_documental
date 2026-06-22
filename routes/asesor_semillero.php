<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AsesorSemillero\AprendizController;
use App\Http\Controllers\AsesorSemillero\ProyectoController;
use App\Http\Controllers\AsesorSemillero\ProductoController;
use App\Http\Controllers\AsesorSemillero\EvidenciaController;
use App\Http\Controllers\AsesorSemillero\MisSemillerosController;
use App\Http\Controllers\AsesorSemillero\ExportarReporteController;
use App\Http\Controllers\AsesorSemillero\SemilleroActivoController;

/*
|--------------------------------------------------------------------------
| Rutas del Módulo: Asesor de Semillero
|--------------------------------------------------------------------------
| Aplicadas a los roles: asesor_semillero, lider_semillero, director_semilleros
| (el líder y director llevan el rol asesor por Spatie, sin cambio de cuenta)
*/

Route::middleware(['auth', 'ensure.active', 'training.center', 'role:asesor_semillero|lider_semillero|director_semilleros'])
    ->prefix('asesor-semillero')
    ->name('asesor.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', function () {
            return view('asesor_semillero.dashboard');
        })->name('dashboard');

        // Mis Semilleros y Proyectos
        Route::get('/mis-semilleros', [MisSemillerosController::class, 'index'])->name('mis_semilleros.index');

        Route::post('/semillero-activo', [SemilleroActivoController::class, 'store'])->name('semillero-activo.store');

        // ─── APRENDICES ────────────────────────────────────────────────────────
        Route::middleware('can:aprendices.listar')->group(function () {
            Route::get('/aprendices', [AprendizController::class, 'index'])->name('aprendices.index');
        });

        Route::middleware('can:aprendices.registrar')->group(function () {
            Route::get('/aprendices/create', [AprendizController::class, 'create'])->name('aprendices.create');
            Route::post('/aprendices', [AprendizController::class, 'store'])->name('aprendices.store');
        });

        Route::middleware('can:aprendices.ver_detalle')->group(function () {
            Route::get('/aprendices/{id}', [AprendizController::class, 'show'])->name('aprendices.show');
        });

        Route::middleware('can:aprendices.editar')->group(function () {
            Route::get('/aprendices/{id}/edit', [AprendizController::class, 'edit'])->name('aprendices.edit');
            Route::put('/aprendices/{id}', [AprendizController::class, 'update'])->name('aprendices.update');
            Route::patch('/aprendices/{id}/desactivar', [AprendizController::class, 'deactivate'])->name('aprendices.deactivate');
            Route::delete('/aprendices/{id}', [AprendizController::class, 'destroy'])->name('aprendices.destroy');
        });

        // ─── PROYECTOS ─────────────────────────────────────────────────────────
        Route::middleware('can:proyectos.listar_semillero')->group(function () {
            Route::get('/proyectos', [ProyectoController::class, 'index'])->name('proyectos.index');
        });

        Route::middleware('can:proyectos.crear_semillero')->group(function () {
            Route::get('/proyectos/create', [ProyectoController::class, 'create'])->name('proyectos.create');
            Route::post('/proyectos', [ProyectoController::class, 'store'])->name('proyectos.store');
        });

        Route::middleware('can:proyectos.ver_detalle')->group(function () {
            Route::get('/proyectos/{id}', [ProyectoController::class, 'show'])->name('proyectos.show');
        });

        Route::middleware('can:proyectos.editar')->group(function () {
            Route::get('/proyectos/{id}/edit', [ProyectoController::class, 'edit'])->name('proyectos.edit');
            Route::put('/proyectos/{id}', [ProyectoController::class, 'update'])->name('proyectos.update');
            Route::patch('/proyectos/{id}/desactivar', [ProyectoController::class, 'deactivate'])->name('proyectos.deactivate');
        });

        Route::middleware('can:proyectos.vincular_integrantes')->group(function () {
            Route::get('/proyectos/{id}/integrantes', [ProyectoController::class, 'integrantes'])->name('proyectos.integrantes');
            Route::post('/proyectos/{id}/integrantes', [ProyectoController::class, 'vincularIntegrante'])->name('proyectos.vincular');
            Route::delete('/proyectos/{id}/integrantes/{user_id}', [ProyectoController::class, 'desvincularIntegrante'])->name('proyectos.desvincular');
        });

        // ─── PRODUCTOS ─────────────────────────────────────────────────────────
        Route::middleware('can:productos.listar')->group(function () {
            Route::get('/productos', [ProductoController::class, 'index'])->name('productos.index');
        });

        Route::middleware('can:productos.registrar')->group(function () {
            Route::get('/productos/create/{proyecto_id?}', [ProductoController::class, 'create'])->name('productos.create');
            Route::post('/productos', [ProductoController::class, 'store'])->name('productos.store');
        });

        Route::middleware('can:productos.ver_detalle')->group(function () {
            Route::get('/productos/{id}', [ProductoController::class, 'show'])->name('productos.show');
        });

        Route::middleware('can:productos.editar')->group(function () {
            Route::get('/productos/{id}/edit', [ProductoController::class, 'edit'])->name('productos.edit');
            Route::put('/productos/{id}', [ProductoController::class, 'update'])->name('productos.update');
            Route::patch('/productos/{id}/desactivar', [ProductoController::class, 'deactivate'])->name('productos.deactivate');
            Route::delete('/productos/{id}', [ProductoController::class, 'destroy'])->name('productos.destroy');
        });

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

        // ─── DESCARGAS DE EVIDENCIAS ─────────────────────────────────────────────────
        Route::middleware('can:evidencias.listar')->group(function () {
            Route::get('/proyectos/{proyecto_id}/evidencias/{evidencia_id}/descargar', [EvidenciaController::class, 'descargarEvidenciaProyecto'])->name('evidencias.proyecto.download');
            Route::get('/productos/{producto_id}/evidencias/{evidencia_id}/descargar', [EvidenciaController::class, 'descargarEvidenciaProducto'])->name('evidencias.producto.download');
        });

        // ─── REPORTES (EXPORTACIÓN) ──────────────────────────────────────────────
        // Pendiente agregar middleware específico si se requiere en el futuro.
        // Por ahora lo dejaremos accesible a todos los asesores (quienes ya tienen acceso a listar estos módulos)
        Route::name('exportar.')->prefix('exportar')->group(function () {
            Route::get('/dashboard',  [ExportarReporteController::class, 'dashboard'])->name('dashboard');
            Route::get('/semilleros', [ExportarReporteController::class, 'semilleros'])->name('semilleros');
            Route::get('/proyectos',  [ExportarReporteController::class, 'proyectos'])->name('proyectos');
            Route::get('/productos',  [ExportarReporteController::class, 'productos'])->name('productos');
            Route::get('/aprendices', [ExportarReporteController::class, 'aprendices'])->name('aprendices');
        });
    });

