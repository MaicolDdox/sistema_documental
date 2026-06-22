---
name: livewire-flux
description: Crea componentes Livewire 4.0 con Flux UI para GIDESTH. Genera la clase PHP, vista Blade con Flux y el test PHPUnit. Usar cuando necesites crear tablas con búsqueda, formularios, modales o cualquier componente interactivo del sistema.
disable-model-invocation: false
---

Crea componentes Livewire 4.0 siguiendo los patrones de GIDESTH.

## Argumentos
`/livewire-flux Modulo/NombreComponente [tipo: tabla|formulario|modal]`

Ejemplo: `/livewire-flux Admin/Semilleros/SemilleroIndex tabla`

## Paso 1: Crear con artisan
```bash
php artisan make:livewire $ARGUMENTS
```

## Paso 2: Clase PHP

### Patrón tabla con búsqueda y paginación (más común en GIDESTH)
```php
<?php

namespace App\Livewire\{Modulo};

use App\Support\TrainingCenterAccess;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class {Nombre}Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    public string $estado = '';

    // ─────────────────────────────────────────────────
    // Ciclo de vida
    // ─────────────────────────────────────────────────

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingEstado(): void
    {
        $this->resetPage();
    }

    // ─────────────────────────────────────────────────
    // Datos computados
    // ─────────────────────────────────────────────────

    #[Computed]
    public function items(): LengthAwarePaginator
    {
        return TrainingCenterAccess::scopeUserQueryForList(
            Modelo::query()->with(['relacion']),
            auth()->user()
        )
        ->when($this->search, fn($q) =>
            $q->where('nombre', 'like', "%{$this->search}%")
        )
        ->when($this->estado, fn($q) =>
            $q->where('estado', $this->estado)
        )
        ->latest()
        ->paginate(15);
    }

    // ─────────────────────────────────────────────────
    // Acciones
    // ─────────────────────────────────────────────────

    public function toggleEstado(int $id): void
    {
        $this->authorize('update', Modelo::findOrFail($id));

        $item = Modelo::findOrFail($id);
        $item->update([
            'estado' => $item->estado === EstadoEnum::Activo
                ? EstadoEnum::Inactivo
                : EstadoEnum::Activo,
        ]);

        unset($this->items);  // Limpiar computed cache en Livewire 4
    }

    public function render(): View
    {
        return view('livewire.{modulo}.{nombre}-index');
    }
}
```

### Patrón formulario modal (crear/editar)
```php
<?php

namespace App\Livewire\{Modulo};

use App\Enums\EstadoEnum;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;

class {Nombre}Form extends Component
{
    public bool $mostrarModal = false;
    public ?int $editandoId = null;

    // ─── Propiedades del formulario ───
    #[Validate('required|string|max:100')]
    public string $nombre = '';

    #[Validate('required|email|max:150')]
    public string $email = '';

    // ─────────────────────────────────────────────────
    // Apertura y cierre del modal
    // ─────────────────────────────────────────────────

    public function crear(): void
    {
        $this->resetForm();
        $this->mostrarModal = true;
    }

    public function editar(int $id): void
    {
        $item = Modelo::findOrFail($id);
        $this->editandoId = $id;
        $this->nombre = $item->nombre;
        $this->email  = $item->email;
        $this->mostrarModal = true;
    }

    public function cerrar(): void
    {
        $this->mostrarModal = false;
        $this->resetForm();
    }

    // ─────────────────────────────────────────────────
    // Persistencia
    // ─────────────────────────────────────────────────

    public function guardar(): void
    {
        $this->validate();

        $datos = ['nombre' => $this->nombre, 'email' => $this->email];

        if ($this->editandoId) {
            Modelo::findOrFail($this->editandoId)->update($datos);
        } else {
            Modelo::create($datos);
        }

        $this->cerrar();
        $this->dispatch('elemento-guardado');
        session()->flash('success', 'Guardado correctamente.');
    }

    // ─────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────

    private function resetForm(): void
    {
        $this->reset(['editandoId', 'nombre', 'email']);
        $this->resetValidation();
    }

    public function render(): View
    {
        return view('livewire.{modulo}.{nombre}-form');
    }
}
```

## Paso 3: Vista Blade con Flux UI

### Vista tabla (index)
```blade
<div>
    {{-- Flash --}}
    @if (session()->has('success'))
        <flux:callout variant="success" class="mb-4">{{ session('success') }}</flux:callout>
    @endif

    {{-- Toolbar --}}
    <div class="flex items-center justify-between mb-4 gap-3">
        <flux:input
            wire:model.live.debounce.300ms="search"
            placeholder="Buscar..."
            icon="magnifying-glass"
            class="max-w-xs"
        />

        <flux:select wire:model.live="estado" class="max-w-xs">
            <flux:select.option value="">Todos los estados</flux:select.option>
            @foreach(\App\Enums\EstadoEnum::cases() as $e)
                <flux:select.option value="{{ $e->value }}">{{ $e->label() }}</flux:select.option>
            @endforeach
        </flux:select>

        @can('create', \App\Models\Modelo::class)
            <flux:button variant="primary" wire:click="$dispatch('abrir-modal')">
                Nuevo
            </flux:button>
        @endcan
    </div>

    {{-- Tabla --}}
    <flux:table>
        <flux:table.head>
            <flux:table.row>
                <flux:table.heading>Nombre</flux:table.heading>
                <flux:table.heading>Estado</flux:table.heading>
                <flux:table.heading class="text-right">Acciones</flux:table.heading>
            </flux:table.row>
        </flux:table.head>
        <flux:table.body>
            @forelse ($this->items as $item)
                <flux:table.row wire:key="{{ $item->id }}">
                    <flux:table.cell>{{ $item->nombre }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge variant="{{ $item->estado === \App\Enums\EstadoEnum::Activo ? 'success' : 'danger' }}">
                            {{ $item->estado->value }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell class="text-right">
                        <flux:button size="sm" wire:click="toggleEstado({{ $item->id }})">
                            {{ $item->estado === \App\Enums\EstadoEnum::Activo ? 'Desactivar' : 'Activar' }}
                        </flux:button>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="3" class="text-center text-slate-400">
                        No se encontraron registros.
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.body>
    </flux:table>

    {{-- Paginación --}}
    <div class="mt-4">
        {{ $this->items->links() }}
    </div>
</div>
```

## Paso 4: Test PHPUnit

```php
<?php

namespace Tests\Feature\Livewire\{Modulo};

use App\Livewire\{Modulo}\{Nombre}Index;
use App\Models\Modelo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class {Nombre}IndexTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
        $this->admin->assignRole('administrador_sistema');
    }

    public function test_renderiza_correctamente(): void
    {
        Livewire::actingAs($this->admin)
            ->test({Nombre}Index::class)
            ->assertStatus(200);
    }

    public function test_filtra_por_busqueda(): void
    {
        Modelo::factory()->create(['nombre' => 'Semillero Alpha']);
        Modelo::factory()->create(['nombre' => 'Semillero Beta']);

        Livewire::actingAs($this->admin)
            ->test({Nombre}Index::class)
            ->set('search', 'Alpha')
            ->assertSee('Semillero Alpha')
            ->assertDontSee('Semillero Beta');
    }

    public function test_resetea_paginacion_al_buscar(): void
    {
        Livewire::actingAs($this->admin)
            ->test({Nombre}Index::class)
            ->set('page', 2)
            ->set('search', 'algo')
            ->assertSet('page', 1);
    }
}
```

## Notas GIDESTH
- Siempre usar `TrainingCenterAccess::scopeUserQueryForList()` en listados de usuarios
- `unset($this->items)` para invalidar `#[Computed]` después de mutaciones en Livewire 4
- Los componentes van en `App\Livewire\{Rol}\` según el módulo
- Usar `EstadoEnum` para estados, nunca strings directos
- `flux:badge variant="success|danger"` para indicadores de estado
