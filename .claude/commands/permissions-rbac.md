---
name: permissions-rbac
description: Implementa control de acceso con Spatie Permission y TrainingCenterAccess en GIDESTH. Usar cuando necesites agregar autorización a un módulo, crear una Policy, asignar roles o verificar acceso por training_center.
disable-model-invocation: false
---

Implementa autorización en GIDESTH usando los patrones existentes de Spatie Permission + TrainingCenterAccess.

## Roles definidos en el sistema

```php
// Roles disponibles (NO crear nuevos sin analizar impacto):
'super_administrador'       // Ve TODOS los centros de formación
'administrador_sistema'     // Admin de un centro específico
'director_semilleros'       // Gestiona semilleros de su centro
'lider_semillero'           // Lidera un semillero
'asesor_semillero'          // Asesora semilleros
'director_investigacion'    // Gestiona investigación
'investigador_asociado'     // Participa en investigación
'admin'                     // Legacy (retrocompatibilidad)
```

## TrainingCenterAccess — el core de autorización

```php
use App\Support\TrainingCenterAccess;

// ── Verificar tipo de usuario ──
TrainingCenterAccess::isSuperAdmin($user)           // bool
TrainingCenterAccess::scopedToTrainingCenter($user) // bool

// ── Filtrar queries según centro (SIEMPRE usar en listados) ──
$query = TrainingCenterAccess::scopeUserQueryForList(
    User::query()->with(['person', 'roles']),
    auth()->user()
);

// ── Obtener centros disponibles para un select ──
$centros = TrainingCenterAccess::centersForSelect(auth()->user());

// ── Roles disponibles para formulario de usuario ──
$roles = TrainingCenterAccess::rolesForUserForm(auth()->user());

// ── Validar asignación de rol que requiere centro ──
TrainingCenterAccess::validateCentroBoundRoleAssignment($user, $rol, $trainingCenterId);
```

## Crear una Policy nueva

```bash
php artisan make:policy NombrePolicy --model=NombreModelo
```

### Patrón de Policy en GIDESTH
```php
<?php

namespace App\Policies;

use App\Models\NombreModelo;
use App\Models\User;
use App\Support\TrainingCenterAccess;

class NombrePolicy
{
    // Super admin puede todo (patrón del proyecto):
    public function before(User $user, string $ability): ?bool
    {
        if (TrainingCenterAccess::isSuperAdmin($user)) {
            return true;
        }

        return null; // Continúa evaluando los métodos
    }

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            'administrador_sistema',
            'director_semilleros',
            'director_investigacion',
        ]);
    }

    public function view(User $user, NombreModelo $modelo): bool
    {
        // Si el recurso tiene training_center_id:
        return $user->training_center_id === $modelo->training_center_id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['administrador_sistema', 'director_semilleros']);
    }

    public function update(User $user, NombreModelo $modelo): bool
    {
        return $user->id === $modelo->user_id
            || ($user->hasRole('administrador_sistema')
                && $user->training_center_id === $modelo->training_center_id);
    }

    public function delete(User $user, NombreModelo $modelo): bool
    {
        return $user->hasRole('administrador_sistema')
            && $user->training_center_id === $modelo->training_center_id;
    }
}
```

## Uso en controladores y Livewire

```php
// En controladores:
public function index(): View
{
    $this->authorize('viewAny', NombreModelo::class);
    // ...
}

public function update(Request $request, NombreModelo $modelo): RedirectResponse
{
    $this->authorize('update', $modelo);
    // ...
}

// En Livewire:
public function eliminar(int $id): void
{
    $modelo = NombreModelo::findOrFail($id);
    $this->authorize('delete', $modelo);
    $modelo->delete();
    unset($this->items);
}
```

## Uso en vistas Blade

```blade
@can('create', \App\Models\NombreModelo::class)
    <flux:button variant="primary" wire:click="crear">Nuevo</flux:button>
@endcan

@can('update', $item)
    <flux:button size="sm" wire:click="editar({{ $item->id }})">Editar</flux:button>
@endcan

@can('delete', $item)
    <flux:button size="sm" variant="danger"
        wire:click="eliminar({{ $item->id }})"
        wire:confirm="¿Estás seguro?">
        Eliminar
    </flux:button>
@endcan
```

## Middleware existente — no duplicar lógica

```php
// Ya implementados en app/Http/Middleware/:
EnsureUserIsActive          // Verifica estado=activo en User
RedirectDirectorToModule    // Redirige según rol al módulo correcto
RequireTrainingCenter       // Fuerza training_center_id en roles bound
PreventBackHistory          // Previene botón atrás del browser
```

## Registrar Policy nueva

En `app/Providers/AuthServiceProvider.php` (o equivalente en Laravel 12):
```php
protected $policies = [
    NombreModelo::class => NombrePolicy::class,
];
```

## Test de autorización (PHPUnit)

```php
public function test_director_no_puede_ver_recursos_de_otro_centro(): void
{
    $centro1 = TrainingCenter::factory()->create();
    $centro2 = TrainingCenter::factory()->create();

    $director = User::factory()->create(['training_center_id' => $centro1->id]);
    $director->assignRole('director_semilleros');

    $recursoOtroCentro = NombreModelo::factory()->create([
        'training_center_id' => $centro2->id,
    ]);

    $this->actingAs($director)
         ->get("/modulo/{$recursoOtroCentro->id}")
         ->assertForbidden();
}

public function test_super_admin_puede_ver_todos_los_centros(): void
{
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super_administrador');

    $recurso = NombreModelo::factory()->create();

    $this->actingAs($superAdmin)
         ->get("/modulo/{$recurso->id}")
         ->assertOk();
}
```
