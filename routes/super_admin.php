<?php

use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\LinkAdminTrainingCenterController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'ensure.active', 'role:super_administrador'])
    ->prefix('super-admin')
    ->name('super-admin.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/centros-administradores', [LinkAdminTrainingCenterController::class, 'index'])->name('centros-administradores');
        Route::post('/centros-administradores', [LinkAdminTrainingCenterController::class, 'store'])->name('centros-administradores.store');
    });
