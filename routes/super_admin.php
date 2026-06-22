<?php

use App\Http\Controllers\SuperAdmin\AdminUsuarioController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\LinkAdminTrainingCenterController;
use App\Http\Controllers\SuperAdmin\UsuarioSistemaController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'ensure.active', 'role:super_administrador'])
    ->prefix('super-admin')
    ->name('super-admin.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/centros-administradores', [LinkAdminTrainingCenterController::class, 'index'])->name('centros-administradores');
        Route::post('/centros-administradores', [LinkAdminTrainingCenterController::class, 'store'])->name('centros-administradores.store');

        // Gestión de administradores del sistema (único rol que el super admin puede crear)
        Route::get('/administradores', [AdminUsuarioController::class, 'index'])->name('administradores.index');
        Route::get('/administradores/crear', [AdminUsuarioController::class, 'create'])->name('administradores.create');
        Route::post('/administradores', [AdminUsuarioController::class, 'store'])->name('administradores.store');
        Route::post('/administradores/{id}/toggle-estado', [AdminUsuarioController::class, 'toggleEstado'])->name('administradores.toggle_estado');

        // Usuarios del sistema (todos los roles excepto administrador_sistema y super_administrador)
        Route::get('/usuarios-sistema', [UsuarioSistemaController::class, 'index'])->name('usuarios-sistema.index');
        Route::get('/usuarios-sistema/crear', [UsuarioSistemaController::class, 'create'])->name('usuarios-sistema.create');
        Route::post('/usuarios-sistema', [UsuarioSistemaController::class, 'store'])->name('usuarios-sistema.store');
        Route::post('/usuarios-sistema/{id}/toggle-estado', [UsuarioSistemaController::class, 'toggleEstado'])->name('usuarios-sistema.toggle_estado');
    });
