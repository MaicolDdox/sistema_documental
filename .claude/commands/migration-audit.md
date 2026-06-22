---
name: migration-audit
description: Audita las migraciones de GIDESTH verificando que tablas con datos de un rol tengan training_center_id, que haya índices en columnas de filtro frecuente, y que las foreign keys usen constrained(). Usar al agregar nuevas tablas o revisar el esquema.
disable-model-invocation: false
---

Audita el esquema de base de datos de GIDESTH.

## Paso 1 — Listar migraciones recientes
```bash
ls -t database/migrations/ | head -20
php artisan migrate:status
```

## Checklist por tabla

### Multi-tenancy
- [ ] ¿Tablas con datos de un rol tienen `training_center_id`?
- [ ] ¿Está definido como `foreignId('training_center_id')->constrained('training_centers')`?
- [ ] ¿Tiene índice junto a `estado`?

### Foreign keys
- [ ] ¿Todas usan `->constrained()` en lugar de `->unsigned()`?
- [ ] ¿Las cascadas son correctas? (`cascadeOnDelete` vs `nullOnDelete`)

### Índices
```bash
# Columnas que DEBEN tener índice en GIDESTH:
# - training_center_id (filtro principal)
# - estado (filtro frecuente)
# - user_id (en tablas de dominio)
# - cualquier columna en WHERE o ORDER BY frecuente
grep -rn "->index(\|->unique(" database/migrations/
```

### Tipos de columna
- [ ] ¿Dinero usa `decimal` y no `float`?
- [ ] ¿Estados usan `enum` con los valores correctos?
- [ ] ¿Textos largos usan `text` y no `string`?

## Patrones incorrectos frecuentes

### ❌ FK sin constrained
```php
$table->unsignedBigInteger('training_center_id');
$table->foreign('training_center_id')->references('id')->on('training_centers');
```

### ✅ Correcto
```php
$table->foreignId('training_center_id')->constrained('training_centers')->cascadeOnDelete();
```

### ❌ Tabla sin índice en columnas de filtro
```php
$table->string('estado');
// falta: $table->index(['training_center_id', 'estado']);
```

## Formato de reporte
```
## Auditoría de Migraciones — GIDESTH

### Tablas sin training_center_id (posible fuga)
- [tabla] → ¿necesita training_center_id?

### Foreign keys sin constrained()
- [tabla.columna] → migración sugerida

### Índices faltantes
- [tabla.columna] → impacto en queries

### Tipos incorrectos
- [tabla.columna] → tipo actual → tipo correcto
```
