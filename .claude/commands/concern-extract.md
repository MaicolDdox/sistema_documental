---
name: concern-extract
description: Detecta lógica de validación o comportamiento duplicado entre Form Requests o componentes Livewire de GIDESTH y propone extraerla a un Concern/Trait en app/Concerns/. El proyecto ya usa ProfileValidationRules y PasswordValidationRules como referencia.
disable-model-invocation: false
---

Detecta y extrae lógica repetida a Concerns/Traits en GIDESTH.

## Concerns existentes en el proyecto
```
app/Concerns/ProfileValidationRules  → reglas de validación de perfil
app/Concerns/PasswordValidationRules → reglas de contraseña
```

## Paso 1 — Buscar duplicación
```bash
# Buscar reglas de validación repetidas en Form Requests:
grep -rn "rules()\|#\[Validate" app/Http/Requests/ app/Livewire/

# Buscar métodos idénticos en múltiples clases:
grep -rn "public function " app/Http/Requests/ | awk -F: '{print $NF}' | sort | uniq -d
```

## ¿Cuándo extraer a un Concern?

Extraer cuando la misma lógica aparece en 2 o más de estos:
- Múltiples Form Requests del mismo dominio
- Múltiples componentes Livewire del mismo rol
- Controladores del mismo módulo

### Ejemplos de Concerns útiles para GIDESTH

```php
// app/Concerns/HasTrainingCenterScope.php
// Para componentes Livewire que necesitan filtrar por centro:
trait HasTrainingCenterScope
{
    public function scopedQuery(Builder $query): Builder
    {
        return TrainingCenterAccess::scopeUserQueryForList(
            $query, auth()->user()
        );
    }
}

// app/Concerns/SemilleroValidationRules.php
// Si múltiples Form Requests de semilleros comparten reglas:
trait SemilleroValidationRules
{
    public function semilleroRules(): array
    {
        return [
            'nombre'      => 'required|string|max:150',
            'descripcion' => 'nullable|string|max:1000',
            'estado'      => ['required', Rule::enum(EstadoEnum::class)],
        ];
    }
}
```

## Estructura de un Concern
```php
<?php
declare(strict_types=1);
namespace App\Concerns;

trait NombreValidationRules
{
    // ─────────────────────────────────────────────────
    // Reglas de validación
    // ─────────────────────────────────────────────────

    public function nombreRules(): array
    {
        return [
            'campo' => 'required|string|max:255',
        ];
    }

    public function nombreMessages(): array
    {
        return [
            'campo.required' => 'El campo es obligatorio.',
        ];
    }
}
```

## Formato de reporte
```
## Extracción de Concerns — GIDESTH

### Duplicación detectada
- [lógica] aparece en: [archivo1, archivo2, archivo3]

### Concern sugerido
- Nombre: app/Concerns/[Nombre].php
- Clases que lo usarían: [lista]

### Código del Concern
[código listo para crear]

### Cambio en las clases origen
[cómo queda cada clase después de usar el trait]
```
