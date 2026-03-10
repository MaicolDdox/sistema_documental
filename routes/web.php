<?php

use Illuminate\Support\Facades\Route;
use App\Actions\Fortify\SendPasswordResetLink;

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

// ─── Rutas de Autenticación Custom ──────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', \App\Livewire\Auth\Login::class)->name('login');
});

// ─── Forgot Password custom (búsqueda dual email) ───────────────────────────
// Debe definirse ANTES de las rutas de Fortify para tomar precedencia
Route::post('/forgot-password', SendPasswordResetLink::class)
    ->middleware(['guest', 'throttle:6,1'])
    ->name('password.email');

// ─── Dashboard: por rol se muestra panel correspondiente o se redirige ─────
// Líder y director de semilleros siempre a su módulo (layout y dashboard propios)
Route::get('/dashboard', function (\Illuminate\Http\Request $request) {
    $user = auth()->user();

    // Evitar caché desactualizada de roles (Spatie)
    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

    if ($user->hasRole('lider_semillero')) {
        return redirect()->to('/lider-semillero', 302);
    }
    if ($user->hasRole('director_semilleros')) {
        return redirect()->to('/director-semilleros', 302);
    }
    if ($user->hasRole('administrador_sistema') || $user->hasRole('admin')) {
        return app(\App\Http\Controllers\Admin\DashboardController::class)->index($request);
    }
    if ($user->hasRole('director_investigacion') || $user->hasRole('investigador_asociado')) {
        return redirect()->to('/research/dashboard', 302);
    }
    if ($user->hasRole('asesor_semillero')) {
        return redirect()->to('/asesor-semillero/dashboard', 302);
    }
    if ($user->hasRole('asesor')) {
        return redirect()->to('/seedlings', 302);
    }
    return view('dashboard.home');
})->middleware(['auth', 'ensure.active', 'redirect.director'])->name('dashboard');

// ═══════════════════════════════════════════════════════════════════════════════
// RUTAS PROTEGIDAS — requieren auth + estado activo
// ═══════════════════════════════════════════════════════════════════════════════
Route::middleware(['auth', 'ensure.active'])->group(function () {

    // ─── Módulo: Admin ───────────────────────────────────────────────────────
    Route::middleware(['role:administrador_sistema|admin'])->prefix('admin')->name('admin.')->group(function () {

        // Dashboard del admin (vista pendiente de creación)
        // Route::view('dashboard', 'admin.dashboard')->name('dashboard');

        // Configuración general
        Route::resource('departments', DepartmentController::class)->names('departments');
        Route::resource('cities', CityController::class)->names('cities');
        Route::resource('training-centers', TrainingCenterController::class)->names('training-centers');
        Route::patch('training-centers/{training_center}/toggle', [TrainingCenterController::class, 'toggle'])->name('training-centers.toggle');
        Route::resource('entity-positions', EntityPositionController::class)->names('entity-positions');
        Route::resource('linkage-types', LinkageTypeController::class)->names('linkage-types');
        Route::resource('training-records', \App\Http\Controllers\Web\TrainingRecordController::class)->names('training-records');
        Route::resource('training-program-types', \App\Http\Controllers\Web\TrainingProgramTypeController::class)->names('training-program-types');
        Route::resource('training-programs', TrainingProgramController::class)->names('training-programs');
        Route::patch('training-programs/{training_program}/toggle', [TrainingProgramController::class, 'toggle'])->name('training-programs.toggle');
        Route::resource('research-lines', ResearchLineController::class)->names('research-lines');
        Route::resource('technological-lines', TechnologicalLineController::class)->names('technological-lines');
        Route::resource('thematic-areas', ThematicAreaController::class)->names('thematic-areas');
        Route::resource('project-modalities', \App\Http\Controllers\Web\ProjectModalityController::class)->names('project-modalities');
        Route::resource('investigation-types', \App\Http\Controllers\Web\InvestigationTypeController::class)->names('investigation-types');
        Route::resource('minciencias-typologies', \App\Http\Controllers\Web\MincienciasTypologyController::class)->names('minciencias-typologies');
        Route::resource('minciencias-subcategories', \App\Http\Controllers\Web\MincienciasSubcategoryController::class)->names('minciencias-subcategories');
        Route::resource('knowledge-grand-areas', \App\Http\Controllers\Web\KnowledgeGrandAreaController::class)->names('knowledge-grand-areas');
        Route::resource('knowledge-areas', \App\Http\Controllers\Web\KnowledgeAreaController::class)->names('knowledge-areas');

        // Gestión de usuarios (controllers clásicos + Livewire CRUD)
        Route::resource('users', UserController::class)->names('users');
        Route::resource('people', PersonController::class)->names('people');
        Route::resource('external-advisors', ExternalAdvisorController::class)->names('external-advisors');

        // Livewire admin user management
        Route::livewire('users-manage', \App\Livewire\Admin\Users\UserIndex::class)->name('users.manage');
        Route::livewire('users-manage/create', \App\Livewire\Admin\Users\UserCreate::class)->name('users.manage.create');
        Route::livewire('users-manage/{user}/edit', \App\Livewire\Admin\Users\UserEdit::class)->name('users.manage.edit');
    });

    // ─── Módulo: Investigación ───────────────────────────────────────────────
    Route::middleware(['role:director_investigacion|investigador_asociado|admin'])
        ->prefix('research')->name('research.')->group(function () {

        // Route::view('dashboard', 'research.dashboard')->name('dashboard');
        Route::resource('groups', ResearchGroupController::class)->names('groups');
        Route::resource('projects', ProjectController::class)->names('projects');
    });

    // ─── Módulo: Semilleros ──────────────────────────────────────────────────
    Route::middleware(['role:director_semilleros|lider_semillero|asesor|admin'])
        ->prefix('seedlings')->name('seedlings.')->group(function () {

        // Route::view('dashboard', 'seedlings.dashboard')->name('dashboard');
        Route::resource('/', SeedlingController::class)->names('index')->parameters(['' => 'seedling']);
    });

    // ─── Módulo: Productos ───────────────────────────────────────────────────
    Route::middleware(['role:admin|director_investigacion|investigador_asociado'])
        ->prefix('products')->name('products.')->group(function () {

        Route::resource('/', ProductController::class)->names('index')->parameters(['' => 'product']);
        Route::resource('group-products', GroupProductController::class)->names('group-products');
    });

});

require __DIR__.'/settings.php';
