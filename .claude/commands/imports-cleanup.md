---
name: imports-cleanup
description: Limpia use statements en archivos PHP de GIDESTH: detecta imports no usados, duplicados, y los reordena según PSR-12. Muy útil después de refactoring donde quedan imports huérfanos.
disable-model-invocation: false
---

Limpia los use statements de archivos PHP en GIDESTH.

## Paso 1 — Leer el archivo
```bash
cat $ARCHIVO
```

## Paso 2 — Detectar imports sin uso

Para cada `use NombreClase;` verificar:
- ¿Aparece `NombreClase` en el resto del archivo?
- ¿Se usa como type hint, en `new`, en `::`, en `instanceof`?

### Casos especiales — NO eliminar
```php
use Illuminate\Foundation\Testing\RefreshDatabase; // trait usado con `use`
use Livewire\WithPagination;                        // trait
use App\Concerns\ProfileValidationRules;            // trait
```

## Orden correcto PSR-12

```php
<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

// 1. PHP built-ins primero (raramente en Laravel)
// 2. Vendor (Illuminate, Livewire, Spatie...)
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

// 3. App clases (alphabetical)
use App\Enums\EstadoEnum;
use App\Models\ResearchGroup;
use App\Services\Admin\ResearchGroupService;
use App\Support\TrainingCenterAccess;
```

## Formato de reporte + acción inmediata

```
## Limpieza de Imports — [archivo]

### Eliminar (no usados)
- línea X: use [Clase]; → nunca aparece en el archivo

### Reordenar
Orden sugerido:
[lista ordenada de imports que quedan]

### Resultado
[número] imports eliminados, [número] reordenados
```

Después de reportar, aplicar los cambios directamente en el archivo.
