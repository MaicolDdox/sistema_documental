<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\CatalogoController;

Route::middleware(['auth', 'role:administrador_sistema'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        
        // Dashboard Admin
        Route::get('/dashboard', function () {
            // Contadores para el dashboard
            $totalUsuarios = App\Models\User::where('training_center_id', auth()->user()->training_center_id)->count();
            $totalRoles = Spatie\Permission\Models\Role::count();
            $totalCatalogos = App\Models\Catalogo::count();
            
            return view('admin.dashboard', compact('totalUsuarios', 'totalRoles', 'totalCatalogos'));
        })->name('dashboard');

        // Usuarios
        Route::resource('usuarios', UsuarioController::class)->except(['show']);
        Route::post('usuarios/{id}/toggle-estado', [UsuarioController::class, 'toggleEstado'])->name('usuarios.toggle_estado');
        Route::post('usuarios/{id}/asignar-rol', [UsuarioController::class, 'asignarRol'])->name('usuarios.asignar_rol');
        Route::post('usuarios/{id}/revocar-rol', [UsuarioController::class, 'revocarRol'])->name('usuarios.revocar_rol');

        // Catálogos
        Route::resource('catalogos', CatalogoController::class)->except(['show']);
    });
