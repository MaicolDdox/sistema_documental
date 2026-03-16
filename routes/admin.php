<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\CatalogoController;
use App\Http\Controllers\Admin\DashboardController;

Route::middleware(['auth', 'role:administrador_sistema|admin|director_investigacion'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        
        // Dashboard Admin
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Usuarios
        Route::get('usuarios/asignar-roles', [UsuarioController::class, 'asignarRoles'])->name('usuarios.asignar_roles');
        Route::get('usuarios/usuarios-con-rol', [UsuarioController::class, 'usuariosConRol'])->name('usuarios.usuarios_con_rol');
        Route::post('usuarios/asignar-rol-store', [UsuarioController::class, 'storeAsignarRol'])->name('usuarios.asignar_rol_store');
        Route::resource('usuarios', UsuarioController::class)->except(['show']);
        Route::post('usuarios/{id}/toggle-estado', [UsuarioController::class, 'toggleEstado'])->name('usuarios.toggle_estado');
        Route::post('usuarios/{id}/asignar-rol', [UsuarioController::class, 'asignarRol'])->name('usuarios.asignar_rol');
        Route::post('usuarios/{id}/revocar-rol', [UsuarioController::class, 'revocarRol'])->name('usuarios.revocar_rol');

        // Catálogos (habilitados también para Director de Investigación)
        Route::middleware('role:administrador_sistema|admin|director_investigacion')->group(function () {
            Route::get('catalogos/simples', [CatalogoController::class, 'simples'])->name('catalogos.simples');
            Route::resource('catalogos', CatalogoController::class)->except(['show']);
        });
    });
