---
name: regression-check
description: Al terminar una sesión de corrección de bugs, verifica que los fixes acumulados no rompieron funcionalidades críticas de GIDESTH. Revisa TrainingCenterAccess, autenticación, roles y los módulos modificados. Produce un reporte de estado del sistema. Usar al final del día o al terminar un bloque de correcciones.
disable-model-invocation: false
---

Ejecuta un chequeo de regresiones completo en GIDESTH al terminar una sesión de corrección de bugs.

## Propósito
Reportar el estado real del sistema después de los fixes del día.
La decisión de cuándo el sistema está listo es **tuya**, no de esta skill.

## Cuándo usar
- Al terminar una sesión de corrección de múltiples bugs
- Cuando se modificaron archivos compartidos (TrainingCenterAccess, User, Middleware)
- Antes de continuar con el siguiente bloque de pruebas

## Proceso

### Paso 1 — Resumen de cambios en la sesión
```bash
git diff main --name-only
git log main..HEAD --oneline
git diff main --stat
```

### Paso 2 — Clasificar archivos modificados por riesgo

**RIESGO ALTO** (requieren suite completa):
- `app/Support/TrainingCenterAccess.php`
- `app/Models/User.php`
- `app/Http/Middleware/`
- `database/migrations/`
- `config/`

**RIESGO MEDIO** (correr tests del módulo):
- `app/Livewire/`
- `app/Http/Controllers/`
- `app/Services/`
- `app/Policies/`

**RIESGO BAJO** (verificación visual):
- `resources/views/`
- `resources/css/`

### Paso 3 — Ejecutar tests según riesgo

```bash
# Siempre: tests de autenticación
php artisan test tests/Feature/Auth/

# Si se tocó TrainingCenterAccess o User:
composer test

# Si solo se tocaron módulos específicos:
php artisan test tests/Feature/[Modulo1]/
php artisan test tests/Feature/[Modulo2]/

# Tests de regresión acumulados:
php artisan test tests/Feature/Regression/
```

### Paso 4 — Checklist de funcionalidades críticas de GIDESTH

**Autenticación y sesión:**
- [ ] Login con usuario activo → redirige al módulo correcto según rol
- [ ] Login con usuario inactivo → bloqueado por `EnsureUserIsActive`
- [ ] Logout limpia la sesión correctamente

**Multi-tenancy:**
- [ ] Admin del Centro A no ve usuarios del Centro B
- [ ] Super Admin ve todos los centros
- [ ] Roles `CENTRO_BOUND` requieren `training_center_id`

**Módulos por rol (verificar el modificado):**
- [ ] Admin → `/admin/users` lista correctamente
- [ ] Director Semilleros → accede solo a su módulo
- [ ] Investigador → no puede acceder a rutas de director

**Reportes (si se modificó algo relacionado):**
- [ ] PDF se genera sin error 500
- [ ] Excel descarga correctamente

### Paso 5 — Reporte de estado

```
## 📋 Estado del sistema — GIDESTH
**Fecha:** [fecha]
**Rama:** feature/aldana_refactoring
**Bugs corregidos en esta sesión:** [lista de BUG-IDs]

### Tests automatizados
- Tests ejecutados: [N]
- Tests pasando:   [N]
- Tests fallando:  [N]

### Archivos de alto riesgo modificados
[lista o "ninguno"]

### Checklist manual
- [x] Autenticación ✅
- [x] Multi-tenancy ✅
- [ ] [item pendiente] ⚠️

### Estado general
[✅ SISTEMA ESTABLE | ⚠️ PENDIENTE: descripción | ❌ HAY ERRORES: detalle]

### Bugs pendientes por corregir
[lista si los hay, o "ninguno detectado en esta sesión"]
```

## Regla de oro
Si `composer test` falla → hay errores pendientes que reportar.
Si hay un test de regresión (BUG-*) fallando → ese bug no está completamente corregido.

El estado del reporte es información para ti. Cuando consideres que el sistema
está al 100%, tú decides qué hacer con ese código.
