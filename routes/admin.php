<?php

use App\Http\Controllers\Admin\CatalogoController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GrupoInvestigacionController;
use App\Http\Controllers\Admin\MincienciasProductoController;
use App\Http\Controllers\Admin\ReporteController;
use App\Http\Controllers\Admin\SemilleroController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\VinculacionGrupoInvestigacionDirectorController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:administrador_sistema', 'active_role:administrador_sistema'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Dashboard Admin
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Usuarios — listado de solo lectura (filtrable por rol) + edición/estado.
        // La creación de usuarios NO pasa por aquí: cada rol asignable tiene su
        // propio ítem dedicado (ver más abajo), rol fijo, sin selector.
        Route::resource('usuarios', UsuarioController::class)->except(['show', 'create', 'store']);
        Route::post('usuarios/{id}/toggle-estado', [UsuarioController::class, 'toggleEstado'])->name('usuarios.toggle_estado');

        Route::get('director-semilleros/crear', [UsuarioController::class, 'createDirectorSemilleros'])->name('director-semilleros.create');
        Route::post('director-semilleros', [UsuarioController::class, 'storeDirectorSemilleros'])->name('director-semilleros.store');

        Route::get('director-grupo-investigacion/crear', [UsuarioController::class, 'createDirectorGrupoInvestigacion'])->name('director-grupo-investigacion.create');
        Route::post('director-grupo-investigacion', [UsuarioController::class, 'storeDirectorGrupoInvestigacion'])->name('director-grupo-investigacion.store');

        // Semilleros — solo lectura (líder, proyectos, líder de proyecto, integrantes, co-investigadores).
        Route::get('semilleros', [SemilleroController::class, 'index'])->name('semilleros.index');
        Route::get('semilleros/{semillero}', [SemilleroController::class, 'show'])->name('semilleros.show');

        // Grupos de investigación (reforma GDI/SDI) — el admin crea con datos
        // básicos y asigna director; el director completa el resto.
        Route::resource('grupos-investigacion', GrupoInvestigacionController::class)->except(['destroy']);
        Route::get('vinculaciones-grupo-investigacion', [VinculacionGrupoInvestigacionDirectorController::class, 'index'])->name('vinculaciones-grupo-investigacion.index');
        Route::put('vinculaciones-grupo-investigacion/{grupo}', [VinculacionGrupoInvestigacionDirectorController::class, 'update'])->name('vinculaciones-grupo-investigacion.update');

        // Productos Minciencias — solo lectura (reforma GDI/SDI: aprobar/rechazar
        // pasó a director_grupo_investigacion). Ve los vinculados a su centro.
        Route::get('minciencias', [MincienciasProductoController::class, 'index'])->name('minciencias.index');
        Route::get('minciencias/{producto}', [MincienciasProductoController::class, 'show'])->name('minciencias.show');
        Route::get('minciencias-archivos/{archivo}/descargar', [MincienciasProductoController::class, 'descargarArchivo'])->name('minciencias.archivos.descargar');

        // Catálogos
        Route::get('catalogos/simples', [CatalogoController::class, 'simples'])->name('catalogos.simples');
        Route::resource('catalogos', CatalogoController::class)->except(['show']);

        // Reportes
        Route::get('reportes', [ReporteController::class, 'index'])->name('reportes.index');
        Route::post('reportes/exportar', [ReporteController::class, 'exportar'])->name('reportes.exportar');
    });
