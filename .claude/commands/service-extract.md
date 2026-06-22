---
name: service-extract
description: Detecta lógica de negocio en controladores o Livewire components y la extrae a un Service en app/Services/. Patrón central de GIDESTH que no siempre se respeta durante el desarrollo rápido.
disable-model-invocation: false
---

Detecta y extrae lógica de negocio a Services en GIDESTH.

## ¿Qué NO debe estar en controladores ni Livewire?

```php
// ❌ Lógica de negocio en Livewire:
public function guardar(): void {
    $grupo = ResearchGroup::create([...]);
    $grupo->members()->attach($this->selectedUsers);
    $grupo->researchLines()->sync($this->lines);
    Mail::to($grupo->director)->send(new GrupoCreado($grupo));
    // Esto son 4 operaciones de negocio → Service
}

// ✅ Correcto — delegar al Service:
public function guardar(): void {
    $this->validate();
    app(ResearchGroupService::class)->crear(
        $this->nombre,
        $this->selectedUsers,
        $this->lines,
        auth()->user()->training_center_id
    );
    session()->flash('success', 'Grupo creado.');
}
```

## Señales de lógica en lugar equivocado

```bash
# Buscar en controladores/Livewire:
grep -rn "->attach(\|->sync(\|->detach(" app/Http/Controllers/ app/Livewire/
grep -rn "Mail::send\|Mail::to" app/Http/Controllers/ app/Livewire/
grep -rn "::create(\|->update(\|->delete()" app/Http/Controllers/ app/Livewire/
grep -rn "DB::" app/Http/Controllers/ app/Livewire/
```

## Proceso de extracción

### 1. Identificar la lógica
Detectar métodos de más de 10 líneas con múltiples operaciones Eloquent.

### 2. Determinar el Service destino
```
app/Services/Admin/          → lógica del módulo Admin
app/Services/Director/       → ya existe
app/Services/Investigador/   → ya existe
```

### 3. Crear el Service si no existe
```bash
# No hay artisan para services — crear manualmente:
# app/Services/{Modulo}/{Nombre}Service.php
```

### 4. Mover la lógica
```php
<?php
declare(strict_types=1);
namespace App\Services\{Modulo};

class {Nombre}Service
{
    public function crear(array $datos, int $trainingCenterId): Modelo
    {
        // lógica extraída del controlador/Livewire
    }
}
```

### 5. Reemplazar en origen
```php
// Antes: 15 líneas de lógica
// Después:
app({Nombre}Service::class)->crear($datos, auth()->user()->training_center_id);
```

## Formato de reporte
```
## Extracción a Services — [archivo]

### Lógica detectada para extraer
- Método [nombre], líneas X-Y → Service sugerido: [nombre]
  Operaciones: [lista de lo que hace]

### Service a crear
- app/Services/[Modulo]/[Nombre]Service.php

### Código del Service
[código listo para copiar]

### Código reemplazado en origen
[código simplificado]
```
