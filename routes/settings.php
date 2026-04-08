<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'ensure.active'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', fn () => view('settings.profile-page'))->name('profile.edit');
    Route::get('settings/password', fn() => view('settings.password-page'))->name('settings.password');
    Route::get('settings/two-factor', fn() => view('settings.two-factor-page'))->name('settings.two-factor');
    Route::get('settings/delete-user', fn() => view('settings.delete-user-page'))->name('settings.delete-user');
});
