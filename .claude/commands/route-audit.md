---
name: route-audit
description: Audita routes/web.php verificando que cada grupo de rutas tenga los middleware correctos por rol, nombres consistentes, y que no existan rutas huérfanas o sin protección. Usar antes de agregar un módulo nuevo o al terminar un sprint.
disable-model-invocation: false
---

Audita las rutas de GIDESTH.

## Paso 1 — Leer el archivo de rutas
```bash
cat routes/web.php
```

## Middleware requerido por tipo de ruta

### Rutas autenticadas (todas)
```php
->middleware(['auth', 'verified'])
```

### Rutas de usuarios activos (todas excepto login/registro)
```php
->middleware(['auth', 'verified', 'ensureUserIsActive'])
```

### Rutas por rol — patrón del proyecto
```php
Route::middleware(['auth', 'verified', 'ensureUserIsActive', 'role:director_semilleros'])
    ->prefix('director-semilleros')
    ->name('director_semilleros.')
    ->group(function () { ... });
```

## Checklist de auditoría

### Middleware
- [ ] ¿Todas las rutas tienen `auth`?
- [ ] ¿Las rutas post-login tienen `ensureUserIsActive`?
- [ ] ¿Las rutas por módulo tienen el middleware de rol correcto?
- [ ] ¿El middleware `requireTrainingCenter` está en rutas de roles CENTRO_BOUND?

### Nombres
- [ ] ¿Los nombres siguen el patrón `{rol}.{recurso}.{accion}`?
- [ ] ¿Los prefijos usan kebab-case?
- [ ] ¿Los nombres de ruta usan snake_case?

### Cobertura
- [ ] ¿Cada componente Livewire tiene su ruta definida?
- [ ] ¿Existen rutas que apuntan a controladores/componentes inexistentes?

```bash
# Verificar que los componentes referenciados existen:
php artisan route:list --columns=uri,name,action | head -50
```

## Formato de reporte
```
## Auditoría de Rutas — GIDESTH

### Rutas sin middleware correcto
- [ruta] → middleware faltante

### Nombres inconsistentes
- [ruta] → nombre actual → nombre sugerido

### Rutas huérfanas (acción inexistente)
- [ruta] → [acción que no existe]

### Resumen
Total rutas: N | Con problemas: N | OK: N
```
