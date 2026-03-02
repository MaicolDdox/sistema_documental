<?php

use Illuminate\Support\Facades\Route;

// ─── Imports ─────────────────────────────────────────────────────────────────
use App\Http\Controllers\Web\DepartmentController;
use App\Http\Controllers\Web\CityController;
use App\Http\Controllers\Web\TrainingCenterController;
use App\Http\Controllers\Web\EntityPositionController;
use App\Http\Controllers\Web\LinkageTypeController;
use App\Http\Controllers\Web\TrainingProgramController;
use App\Http\Controllers\Web\ResearchLineController;
use App\Http\Controllers\Web\TechnologicalLineController;
use App\Http\Controllers\Web\ThematicAreaController;
use App\Http\Controllers\Web\ResearchGroupController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\PersonController;
use App\Http\Controllers\Web\ExternalAdvisorController;
use App\Http\Controllers\Web\SeedlingController;
use App\Http\Controllers\Web\ProjectController;
use App\Http\Controllers\Web\ProductController;
use App\Http\Controllers\Web\GroupProductController;

// ─── Ruta pública ────────────────────────────────────────────────────────────
Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// ─── Módulo: Configuración General (Admin) ───────────────────────────────────
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {

    Route::resource('departments', DepartmentController::class)->names('departments');
    Route::resource('cities', CityController::class)->names('cities');
    Route::resource('training-centers', TrainingCenterController::class)->names('training-centers');
    Route::resource('entity-positions', EntityPositionController::class)->names('entity-positions');
    Route::resource('linkage-types', LinkageTypeController::class)->names('linkage-types');
    Route::resource('training-programs', TrainingProgramController::class)->names('training-programs');
    Route::resource('research-lines', ResearchLineController::class)->names('research-lines');
    Route::resource('technological-lines', TechnologicalLineController::class)->names('technological-lines');
    Route::resource('thematic-areas', ThematicAreaController::class)->names('thematic-areas');

});

// ─── Módulo: Gestión de Usuarios ─────────────────────────────────────────────
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {

    Route::resource('users', UserController::class)->names('users');
    Route::resource('people', PersonController::class)->names('people');
    Route::resource('external-advisors', ExternalAdvisorController::class)->names('external-advisors');

});

// ─── Módulo: Investigación ───────────────────────────────────────────────────
Route::middleware(['auth', 'verified'])->prefix('research')->name('research.')->group(function () {

    Route::resource('groups', ResearchGroupController::class)->names('groups');
    Route::resource('projects', ProjectController::class)->names('projects');

});

// ─── Módulo: Semilleros ──────────────────────────────────────────────────────
Route::middleware(['auth', 'verified'])->prefix('seedlings')->name('seedlings.')->group(function () {

    Route::resource('/', SeedlingController::class)->names('index')->parameters(['' => 'seedling']);

});

// ─── Módulo: Productos ───────────────────────────────────────────────────────
Route::middleware(['auth', 'verified'])->prefix('products')->name('products.')->group(function () {

    Route::resource('/', ProductController::class)->names('index')->parameters(['' => 'product']);
    Route::resource('group-products', GroupProductController::class)->names('group-products');

});

require __DIR__.'/settings.php';
