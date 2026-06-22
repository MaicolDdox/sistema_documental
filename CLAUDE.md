# GIDESTH — Sistema Documental de Semilleros de Investigación

## Stack tecnológico
- **Laravel:** 12.51.0 | **PHP:** 8.2.28
- **Frontend reactivo:** Livewire 4.0 + Flux 2.9.0 (UI components)
- **CSS:** Tailwind CSS 4.0 (utility-first)
- **Auth:** Laravel Fortify + Spatie Permission 6.24
- **Base de datos:** MySQL — `sistema_documental` (127.0.0.1:3306)
- **Testing:** PHPUnit 11.5.3 con SQLite en memoria
- **Reportes:** DomPDF 3.1 (PDF) + PHPSpreadsheet 5.5 (Excel)
- **Bundler:** Vite 7 con laravel-vite-plugin

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

### Tipado
```php
// Siempre tipos de retorno explícitos:
public function index(Request $request): View {}
public function create(int $userId, ?User $user = null): Collection {}

// Constantes en SNAKE_CASE:
const CENTRO_BOUND_ROLE_NAMES = [...];
```

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

## Livewire 4.0 — convenciones específicas

```php
<?php

namespace App\Livewire\Admin\Users;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Livewire\Attributes\Computed;
use Illuminate\Contracts\View\View;

class UserIndex extends Component
{
    use WithPagination;

    #[Url]                    // Sincroniza con URL
    public string $search = '';

    public string $estado = '';

    // IMPORTANTE en Livewire 4: resetear paginación al buscar
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function users(): \Illuminate\Pagination\LengthAwarePaginator
    {
        return TrainingCenterAccess::scopeUserQueryForList(
            User::with(['person', 'roles']),
            auth()->user()
        )
        ->when($this->search, fn($q) => $q->whereHas('person', fn($p) =>
            $p->where('primer_nombre', 'like', "%{$this->search}%")
        ))
        ->paginate(15);
    }

    public function render(): View
    {
        return view('livewire.admin.users.user-index');
    }
}
```

### Flux UI — componentes disponibles
```blade
{{-- Botones --}}
<flux:button variant="primary">Guardar</flux:button>
<flux:button variant="danger" wire:click="eliminar">Eliminar</flux:button>

{{-- Inputs --}}
<flux:input wire:model.blur="nombre" label="Nombre" placeholder="..." />
<flux:select wire:model="estado" label="Estado">
    <flux:select.option value="">Todos</flux:select.option>
    @foreach(EstadoEnum::cases() as $estado)
        <flux:select.option value="{{ $estado->value }}">{{ $estado->label() }}</flux:select.option>
    @endforeach
</flux:select>

{{-- Tablas --}}
<flux:table>
    <flux:table.head>
        <flux:table.row>
            <flux:table.heading>Nombre</flux:table.heading>
        </flux:table.row>
    </flux:table.head>
    <flux:table.body>
        @foreach($this->users as $user)
        <flux:table.row>
            <flux:table.cell>{{ $user->getNameAttribute() }}</flux:table.cell>
        </flux:table.row>
        @endforeach
    </flux:table.body>
</flux:table>
```

## Roles y permisos — Spatie Permission

```php
// Verificar rol:
$user->hasRole('director_semilleros')
$user->hasAnyRole(['admin', 'super_administrador'])

// En Policies — patrón del proyecto:
public function update(User $user, ResearchGroup $group): bool
{
    return $user->id === $group->director_id
        || $user->hasRole('super_administrador');
}

// Roles que requieren training_center_id (de TrainingCenterAccess):
// director_semilleros, lider_semillero, asesor_semillero,
// director_investigacion, investigador_asociado
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

## Generación de reportes

```php
// PDF con DomPDF:
use Barryvdh\DomPDF\Facade\Pdf;
$pdf = Pdf::loadView('pdf.reporte', compact('datos'));
return $pdf->download('reporte.pdf');

// Excel con PhpSpreadsheet:
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue('A1', 'Encabezado');
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
