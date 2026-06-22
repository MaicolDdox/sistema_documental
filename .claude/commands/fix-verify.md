---
name: fix-verify
description: Verifica que la corrección aplicada resolvió el bug y no introdujo regresiones. Genera el test PHPUnit mínimo para el comportamiento corregido. Usar después de aplicar una corrección del agente bug-fixer.
disable-model-invocation: false
---

Verifica que el bug fue corregido correctamente en GIDESTH.

## Uso
`/fix-verify BUG-[ID]`

## Proceso

### Paso 1 — Confirmar el cambio aplicado
```bash
# Ver qué cambió en git:
git diff HEAD
# O si ya hizo commit:
git show --stat HEAD
```

### Paso 2 — Verificación rápida según categoría del bug

**Categoría A (Lógica):**
```bash
# Correr el test relacionado si existe:
php artisan test --filter=[ClaseTest]
# Si no existe, Claude generará uno mínimo (ver Paso 4)
```

**Categoría B (Autorización):**
```bash
# Verificar que TrainingCenterAccess sigue funcionando:
php artisan test --filter=TrainingCenter
php artisan test tests/Feature/Auth/
```

**Categoría C (Livewire):**
```bash
# Limpiar caché de vistas:
php artisan view:clear
php artisan livewire:publish --assets
# Correr tests Livewire del módulo afectado:
php artisan test tests/Feature/Livewire/
```

**Categoría D (Base de datos):**
```bash
# Verificar migraciones:
php artisan migrate:status
# Si se tocó una migración nueva:
php artisan migrate:fresh --seed --env=testing
php artisan test
```

**Categoría E (Validación):**
```bash
php artisan test --filter=[FormRequestTest]
```

**Categoría F (Vista/Blade):**
```bash
php artisan view:clear
# Verificar visualmente en el navegador
```

### Paso 3 — Correr suite completa (rápida)
```bash
# Solo los tests del módulo afectado (más rápido que todos):
php artisan test tests/Feature/[Modulo]/

# Suite completa si el cambio fue en archivos compartidos
# (TrainingCenterAccess, Middleware, Models/User):
composer test
```

### Paso 4 — Generar test mínimo para el comportamiento corregido

Si no existía test para el comportamiento que falló, generar UNO específico:

```php
<?php
// tests/Feature/Regression/BUG[ID]Test.php

namespace Tests\Feature\Regression;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresión: BUG-[ID]
 * Descripción: [qué fallaba]
 * Corregido: [fecha]
 */
class BUG[ID]Test extends TestCase
{
    use RefreshDatabase;

    /**
     * Verifica que [descripción del comportamiento correcto].
     * Regresión para BUG-[ID].
     */
    public function test_[descripcion_del_comportamiento_correcto](): void
    {
        // Arrange — setup mínimo para reproducir el escenario
        // [código de setup]

        // Act — la acción que antes fallaba
        // [código de la acción]

        // Assert — el comportamiento correcto esperado
        // [assertions]
    }
}
```

**Ejecutar el nuevo test:**
```bash
php artisan test --filter=BUG[ID]Test
```

### Paso 5 — Reporte de cierre del bug

```
## ✅ Bug Cerrado — BUG-[ID]

**Estado:** RESUELTO
**Tests pasando:** [número] / [total]
**Nuevo test de regresión:** tests/Feature/Regression/BUG[ID]Test.php

**Verificación manual requerida:**
[ ] Probar el flujo en el navegador: [pasos]
[ ] Verificar que el rol [X] no puede [Y]  (si era bug de autorización)
[ ] Confirmar en BD que los datos son correctos (si era bug de datos)

**Archivos modificados en este fix:**
- [lista de archivos]

---
➡️ Listo para el siguiente bug. Usa /bug-report con la próxima evidencia.
```

## Señales de que el fix NO funcionó
- Los tests siguen fallando → volver al agente bug-fixer con más contexto
- Apareció un error diferente → nuevo `/bug-report` para el error nuevo
- El test pasa pero el comportamiento en el navegador sigue mal → problema de caché o de Livewire
