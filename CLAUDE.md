# GIDESTH — Sistema Documental de Semilleros de Investigación

## Arquitectura del proyecto

### Organización por rol (CRÍTICO)
Todo el proyecto está organizado por rol de usuario. Cada módulo replica esta estructura:
```
app/Http/Controllers/{Rol}/
app/Livewire/{Rol}/
resources/views/{rol}/
```
Roles existentes: `Admin`, `SuperAdmin`, `AsesorSemillero`, `DirectorInvestigacion`,
`DirectorSemilleros`, `InvestigadorAsociado`, `LiderSemillero`

### Multi-tenancy por Training Center (CENTRAL)
**`app/Support/TrainingCenterAccess.php`** es el archivo más importante del proyecto.
Controla qué datos ve cada usuario según su `training_center_id`.

```php
// SIEMPRE usar esto para listar usuarios en controladores/Livewire:
$users = TrainingCenterAccess::scopeUserQueryForList(
    User::with(['person', 'roles']),
    auth()->user()
)->paginate(15);

// Verificar si es super admin:
TrainingCenterAccess::isSuperAdmin(auth()->user())

// Roles que REQUIEREN training_center_id asignado:
// director_semilleros, lider_semillero, asesor_semillero,
// director_investigacion, investigador_asociado
```

### Patrones en uso (y los que NO se usan)
✅ Services (`app/Services/Director/`, `app/Services/Investigador/`)
✅ Actions Fortify (`app/Actions/Fortify/`)
✅ Concerns/Traits (`app/Concerns/` — validaciones reutilizables)
✅ Enums tipados (`app/Enums/`)
✅ Policies (`app/Policies/`)
✅ Form Requests (`app/Http/Requests/`)
✅ Middleware custom (`app/Http/Middleware/`)
✅ Support classes (`app/Support/`)

❌ NO Repositories — Eloquent directo
❌ NO Events/Listeners — no hay eventos de dominio
❌ NO Jobs — queue configurada pero sin jobs
❌ NO Observers
❌ NO DTOs
❌ NO API Resources — app Livewire/Blade pura

## Convenciones de código

### Enums — uso obligatorio para estados y tipos
```php
// En modelos, siempre castear enums:
protected function casts(): array {
    return [
        'estado' => EstadoEnum::class,
        'tipo_documento' => TipoDocumentoEnum::class,
        'genero' => GeneroEnum::class,
    ];
}

// Enums disponibles: EstadoEnum, TipoDocumentoEnum, GeneroEnum,
// JornadaEnum, ModalidadEnum, RolGrupoEnum,
// TipoParticipacionEnum, TipoProyectoOrigenEnum
```

### Separadores de sección en código
```php
// ─────────────────────────────────────────────────
// Nombre de la sección
// ─────────────────────────────────────────────────
```

### Queries con `when()` y scopes
```php
// Patrón estándar del proyecto:
$results = Modelo::query()
    ->with(['relacion1', 'relacion2'])
    ->when($this->search, fn($q) => $q->where('nombre', 'like', "%{$this->search}%"))
    ->when($this->estado, fn($q) => $q->where('estado', $this->estado))
    ->paginate(15);
```

## Testing — PHPUnit (NO Pest)

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NombreTest extends TestCase
{
    use RefreshDatabase;  // SQLite en memoria (ver phpunit.xml)

    public function test_usuario_puede_ver_lista(): void
    {
        $user = User::factory()->create();
        $user->assignRole('administrador_sistema');

        $response = $this->actingAs($user)->get('/admin/users');

        $response->assertStatus(200);
    }
}
```

## Reglas críticas — NO hacer sin avisar

- ❌ No modificar migraciones ya ejecutadas — siempre crear nuevas
- ❌ No agregar lógica de acceso a datos sin pasar por `TrainingCenterAccess`
- ❌ No asignar roles de `CENTRO_BOUND_ROLE_NAMES` sin `training_center_id`
- ❌ No crear controladores sin verificar si el módulo ya existe para ese rol
- ❌ No usar `DB::` directo si hay modelo Eloquent disponible
- ❌ No crear componentes Livewire sin seguir la estructura `{Rol}/{Nombre}.php`
- ❌ No ejecutar `php artisan migrate` sin revisar integridad referencial antes
- ❌ No cambiar la lógica de `TrainingCenterAccess` sin analizar impacto en todos los roles

## Comandos del proyecto

```bash
composer dev             # Servidor + queue + Vite (todo junto)
composer test            # PHPUnit + Pint linting
composer lint            # Solo Laravel Pint
php artisan migrate      # Migraciones
php artisan make:livewire Modulo/NombreComponente
php artisan make:model NombreModelo -mfc
npm run dev              # Solo Vite HMR
npm run build            # Build producción
```
