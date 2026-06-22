---
name: enum-validator
description: Detecta comparaciones incorrectas con strings en lugar de Enum cases, casts faltantes en modelos, y uso inconsistente de los 8 Enums de GIDESTH. Usar cuando agregues lógica que involucre estados, tipos o géneros, o como auditoría del módulo completo.
disable-model-invocation: false
---

Audita el uso correcto de los Enums tipados de GIDESTH.

## Enums definidos en el proyecto
```
EstadoEnum          → 'activo' / 'inactivo'
TipoDocumentoEnum   → tipos de documento de identidad
GeneroEnum          → géneros
JornadaEnum         → jornadas académicas
ModalidadEnum       → modalidades
RolGrupoEnum        → roles dentro de un grupo
TipoParticipacionEnum
TipoProyectoOrigenEnum
```

## Paso 1 — Escanear el archivo o directorio
```bash
# Buscar comparaciones incorrectas con strings:
grep -rn "estado.*==.*'activo'\|estado.*==.*'inactivo'" $ARCHIVO
grep -rn "->estado.*===.*'" $ARCHIVO
grep -rn "where.*estado.*activo" $ARCHIVO

# Buscar casts faltantes en modelos:
grep -rn "protected.*casts" app/Models/
grep -rn "EstadoEnum\|TipoDocumentoEnum\|GeneroEnum" app/Models/
```

## Paso 2 — Patrones incorrectos a detectar

### ❌ Comparación con string (error silencioso)
```php
if ($modelo->estado === 'activo') { }
->where('estado', 'activo')
```

### ✅ Correcto
```php
if ($modelo->estado === EstadoEnum::Activo) { }
->where('estado', EstadoEnum::Activo)
```

### ❌ Cast faltante en modelo
```php
protected function casts(): array {
    return ['email_verified_at' => 'datetime'];
    // falta: 'estado' => EstadoEnum::class
}
```

### ✅ Correcto
```php
protected function casts(): array {
    return [
        'estado'         => EstadoEnum::class,
        'tipo_documento' => TipoDocumentoEnum::class,
        'genero'         => GeneroEnum::class,
    ];
}
```

### ❌ Acceso a value innecesario para comparar
```php
if ($modelo->estado->value === EstadoEnum::Activo->value) { }
```

### ✅ Correcto
```php
if ($modelo->estado === EstadoEnum::Activo) { }
```

## Paso 3 — Verificar en vistas Blade
```bash
grep -rn "estado.*==\|->estado" resources/views/
```
En vistas: `$item->estado->value` para mostrar, `$item->estado === EstadoEnum::Activo` para comparar.

## Formato de reporte
```
## Validación de Enums — [archivo/módulo]

### ❌ Problemas encontrados
- Línea X: [descripción] → corrección exacta

### ✅ Uso correcto detectado
- [lo que está bien]

### Modelos sin cast de Enum
- [lista de modelos que deberían tener cast]
```
