<?php

use App\Actions\Fortify\SendPasswordResetLink;
use App\Http\Controllers\Web\CityController;
// ─── Imports ─────────────────────────────────────────────────────────────────
use App\Http\Controllers\Web\DepartmentController;
use App\Http\Controllers\Web\EntityPositionController;
use App\Http\Controllers\Web\ExternalAdvisorController;
use App\Http\Controllers\Web\LinkageTypeController;
use App\Http\Controllers\Web\PersonController;
use App\Http\Controllers\Web\ResearchLineController;
use App\Http\Controllers\Web\TechnologicalLineController;
use App\Http\Controllers\Web\ThematicAreaController;
use App\Http\Controllers\Web\TrainingCenterController;
use App\Http\Controllers\Web\TrainingProgramController;
use App\Http\Controllers\Web\UserController;
use Illuminate\Support\Facades\Route;

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
// BUG-20260813-050: el orden de estos if/elseif antes era fijo y no
// coincidía con la prioridad real de rol principal usada en todo el resto
// del sistema (App\Support\RoleModuleLinks::LOGIN_ROLE_PRIORITY, la misma
// que usa el login para decidir a dónde redirigir). Un usuario con más de
// un rol asignado (p. ej. super_administrador + lider_semillero) caía
// siempre en la primera rama que matcheara, sin importar cuál fuera
// realmente su rol principal.
// BUG-20260813-056: y una vez agregado el rol ACTIVO (multi-rol), decidir
// aquí con el rol PRINCIPAL seguía siendo un problema — cualquier enlace a
// esta ruta genérica (incluidos ~16 breadcrumbs "Administración/Catálogos"
// en vistas de admin) mandaba al usuario al módulo de su rol principal sin
// pasar por roles.switch, y el aislamiento de la Fase 2 lo bloqueaba con
// 403 si su rol activo era otro. Ahora decide con el rol ACTIVO
// (ActiveRoleContext::current(), que ya cae al principal si no hay uno
// válido en sesión), así esta ruta nunca puede sacarte de tu propio
// contexto activo.
Route::get('/dashboard', function (\Illuminate\Http\Request $request) {
    $user = auth()->user();

    // Evitar caché desactualizada de roles (Spatie)
    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    $user->load('roles');

    $primary = \App\Support\ActiveRoleContext::current();

    return match ($primary) {
        'super_administrador' => redirect()->to('/super-admin/dashboard', 302),
        'administrador_sistema' => app(\App\Http\Controllers\Admin\DashboardController::class)->index($request),
        'director_semilleros' => redirect()->to('/director-semilleros', 302),
        'lider_semillero' => redirect()->to('/lider-semillero', 302),
        'lider_proyecto' => redirect()->to('/lider-proyecto', 302),
        'co_investigador' => redirect()->to('/co-investigador', 302),
        'director_investigacion' => redirect()->to('/director', 302),
        'investigador_asociado' => redirect()->to('/investigador', 302),
        'asesor_semillero' => redirect()->to('/asesor-semillero/dashboard', 302),
        default => view('dashboard.home'),
    };
})->middleware(['auth', 'ensure.active', 'redirect.director'])->name('dashboard');

// ═══════════════════════════════════════════════════════════════════════════════
// RUTAS PROTEGIDAS — requieren auth + estado activo
// ═══════════════════════════════════════════════════════════════════════════════
Route::middleware(['auth', 'ensure.active'])->group(function () {

    // ─── Rol activo (FEAT-20260830-001, multi-rol) ─────────────────────────
    Route::post('/mis-roles/cambiar', [\App\Http\Controllers\RoleSwitchController::class, 'switch'])
        ->name('roles.switch');

    // ─── Módulo: Admin ───────────────────────────────────────────────────────
    // Acceso para cualquier usuario autenticado y activo.
    // Los permisos finos se controlan dentro de las vistas / componentes.
    Route::prefix('admin')->name('admin.')->group(function () {

        // Dashboard del admin (vista pendiente de creación)
        // Route::view('dashboard', 'admin.dashboard')->name('dashboard');

        // Configuración general
        Route::resource('departments', DepartmentController::class)->names('departments');
        Route::resource('cities', CityController::class)->names('cities');
        Route::middleware('role:super_administrador')->group(function () {
            Route::resource('training-centers', TrainingCenterController::class)->names('training-centers');
            Route::patch('training-centers/{training_center}/toggle', [TrainingCenterController::class, 'toggle'])->name('training-centers.toggle');
        });
        Route::resource('entity-positions', EntityPositionController::class)->names('entity-positions');
        Route::resource('linkage-types', LinkageTypeController::class)->names('linkage-types');
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

        // Gestión de usuarios (controllers clásicos + Livewire CRUD)
        Route::resource('users', UserController::class)->names('users');
        Route::resource('people', PersonController::class)->names('people');
        Route::resource('external-advisors', ExternalAdvisorController::class)->names('external-advisors');

        // Livewire admin user management
        Route::livewire('users-manage', \App\Livewire\Admin\Users\UserIndex::class)->name('users.manage');
        Route::livewire('users-manage/create', \App\Livewire\Admin\Users\UserCreate::class)->name('users.manage.create');
        Route::livewire('users-manage/{user}/edit', \App\Livewire\Admin\Users\UserEdit::class)->name('users.manage.edit');
    });

    // Nota: los módulos "Investigación" (/research), "Semilleros" (/seedlings)
    // y "Productos" (/products) que existían aquí eran controladores stub de
    // Blueprint sin implementar (cuerpos vacíos), nunca funcionales. Se
    // eliminaron en el rediseño de roles junto con ResearchGroup/Product/
    // GroupProduct — la funcionalidad real vive en los módulos por rol
    // (LiderSemillero, LiderProyecto, DirectorSemilleros).
});

require __DIR__.'/settings.php';
