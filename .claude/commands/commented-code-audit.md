---
name: commented-code-audit
description: Detecta bloques de código PHP comentado que ya no sirven en GIDESTH, diferenciándolos de comentarios de documentación válidos. Genera una lista para que el agente code-cleaner los elimine.
disable-model-invocation: false
---

Detecta código comentado innecesario en GIDESTH.

## Paso 1 — Buscar código comentado
```bash
grep -rn "^[[:space:]]*//" $RUTA | grep -v "TODO\|FIXME\|NOTE\|HACK\|───\|─────"
grep -rn "/\*" $RUTA | grep -v "/**\| * "
```

## Diferencia entre comentario válido y código muerto

### ✅ Comentarios válidos — NO eliminar
```php
// Roles que requieren training_center asignado
const CENTRO_BOUND_ROLE_NAMES = [...];

// ─────────────────────────────────────────────────
// Ciclo de vida
// ─────────────────────────────────────────────────

/**
 * Retorna los centros disponibles para el usuario.
 */
public function centersForSelect(?User $user): Collection

// TODO: implementar caché para esta query
// FIXME: revisar el cálculo de totales con descuento
```

### ❌ Código comentado para eliminar
```php
// $old = OldModel::find($id);
// return $old->getData();

// if ($user->hasRole('admin')) {
//     return redirect('/admin');
// }

/*
$query->where('legacy_field', true)
      ->orderBy('old_column');
*/
```

## Criterios para marcar como eliminable
1. Son líneas de PHP comentadas (no prosa)
2. El código que referencian ya no existe (`OldModel`, campos eliminados)
3. Llevan más de 1 sprint sin descomentarse
4. Están duplicando lógica que ya existe activa

## Formato de reporte
```
## Código Comentado — [archivo/módulo]

### Para eliminar
- [archivo] líneas X-Y:
  [extracto del código comentado]
  Razón: [por qué es seguro eliminar]

### Mantener (comentarios válidos)
- [archivo] línea X: [descripción]

### Total líneas de código comentado: N
Para limpiar: Usa el agente code-cleaner con este reporte
```
