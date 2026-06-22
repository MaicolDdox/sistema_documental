---
name: dead-code-scan
description: Detecta código muerto en GIDESTH: métodos privados sin llamar, variables no usadas, imports sin uso, clases no instanciadas, y constantes no referenciadas. NO elimina nada — produce un reporte para el agente code-cleaner.
disable-model-invocation: false
---

Detecta código muerto en GIDESTH sin modificar nada.

## Paso 1 — Escanear el objetivo
```bash
# Métodos privados que nunca se llaman:
grep -rn "private function " $RUTA

# use statements sin uso (PHP):
grep -rn "^use " $RUTA

# Variables asignadas y nunca leídas:
grep -rn "\$[a-zA-Z_]* = " $RUTA
```

## Tipos de código muerto a detectar

### 1. Métodos privados sin llamar
```php
// ❌ Nunca se llama desde la misma clase:
private function formatearFecha(string $fecha): string {
    return Carbon::parse($fecha)->format('d/m/Y');
}
```

### 2. Use statements sin usar
```php
// ❌ Importado pero no usado:
use App\Models\Project;    // si Project no aparece en el archivo
use Illuminate\Support\Str; // si Str no se usa
```

### 3. Propiedades Livewire nunca leídas ni escritas
```php
// ❌ Declarada pero no usada en template ni en métodos:
public string $variableOlvidada = '';
```

### 4. Código comentado (no docblocks)
```php
// ❌ Código comentado que ya no sirve:
// $old = OldModel::find($id);
// return $old->getData();
```

### 5. Constantes no referenciadas
```php
// ❌ Nunca se usa en el proyecto:
const LEGACY_TIMEOUT = 30;
```

## Reglas de seguridad — NO marcar como muerto
- Métodos públicos (pueden llamarse desde afuera)
- Métodos de interfaces implementadas
- Métodos `boot`, `register`, `mount`, `render`, `updated*`
- Constantes de configuración global

## Formato de reporte
```
## Código Muerto Detectado — [archivo/módulo]

### Métodos privados sin uso
- [clase::método] línea X — [acción sugerida: eliminar/revisar]

### Use statements sin uso
- [use statement] línea X — eliminar

### Propiedades sin uso
- [clase::$propiedad] línea X — [acción sugerida]

### Código comentado
- Líneas X-Y — [descripción breve]

### Total elementos: N
Para limpiar: Usa el agente code-cleaner con este reporte
```
