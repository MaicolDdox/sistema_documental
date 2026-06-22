---
name: training-center-check
description: Audita que todas las queries de listado de datos pasen por TrainingCenterAccess. Es el check más crítico de GIDESTH — una query sin este filtro expone datos de un centro a otro. Usar en cada módulo nuevo o modificado.
disable-model-invocation: false
---

Verifica el aislamiento multi-tenant en GIDESTH.

## Paso 1 — Escanear queries de listado
```bash
# Buscar queries que NO usan TrainingCenterAccess:
grep -rn "::all()\|->get()\|->paginate(" $RUTA
grep -rn "TrainingCenterAccess" $RUTA

# Ver qué modelos tienen training_center_id:
grep -rn "training_center_id" app/Models/
```

## Roles y su acceso a datos

| Rol | ¿Ve todos los centros? | Filtro requerido |
|-----|----------------------|-----------------|
| `super_administrador` | ✅ Sí | Ninguno |
| `administrador_sistema` | ❌ Solo su centro | `training_center_id` |
| `director_semilleros` | ❌ Solo su centro | `training_center_id` |
| `lider_semillero` | ❌ Solo su centro | `training_center_id` |
| `asesor_semillero` | ❌ Solo su centro | `training_center_id` |
| `director_investigacion` | ❌ Solo su centro | `training_center_id` |
| `investigador_asociado` | ❌ Solo su centro | `training_center_id` |

## Patrones a detectar

### ❌ Fuga de datos — CRÍTICO
```php
// Sin filtro → super_admin ve todo pero admin ve datos de otros centros
$users = User::with(['person'])->paginate(15);
$grupos = ResearchGroup::where('estado', 'activo')->get();
```

### ✅ Correcto — siempre usar TrainingCenterAccess
```php
$users = TrainingCenterAccess::scopeUserQueryForList(
    User::with(['person', 'roles']),
    auth()->user()
)->paginate(15);

// O para modelos con training_center_id propio:
$grupos = ResearchGroup::query()
    ->when(
        !TrainingCenterAccess::isSuperAdmin(auth()->user()),
        fn($q) => $q->where('training_center_id', auth()->user()->training_center_id)
    )
    ->paginate(15);
```

### ❌ Creación sin training_center_id
```php
ResearchGroup::create(['nombre' => $this->nombre]);
```

### ✅ Correcto
```php
ResearchGroup::create([
    'nombre'             => $this->nombre,
    'training_center_id' => auth()->user()->training_center_id,
]);
```

## Formato de reporte
```
## Check de TrainingCenterAccess — [módulo]

### 🔴 FUGA DETECTADA (corregir inmediatamente)
- [archivo línea X]: query sin filtro de training_center → corrección

### 🟡 Creación sin training_center_id
- [archivo línea X] → corrección

### ✅ Implementado correctamente
- [lista de queries correctas]

### Riesgo
[BAJO / MEDIO / ALTO / CRÍTICO]
```
