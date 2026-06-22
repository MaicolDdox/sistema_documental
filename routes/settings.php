<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\DeleteUserController;

Route::middleware(['auth', 'ensure.active'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('settings/password', fn () => view('settings.password-page'))->name('settings.password');
    Route::patch('settings/password', [PasswordController::class, 'update'])->name('settings.password.update');

    Route::get('settings/two-factor', fn () => view('settings.two-factor-page'))
        ->middleware('password.confirm')
        ->name('settings.two-factor');

    Route::get('settings/delete-user', fn () => view('settings.delete-user-page'))->name('settings.delete-user');
    Route::delete('settings/delete-user', [DeleteUserController::class, 'destroy'])->name('settings.delete-user.destroy');
});
