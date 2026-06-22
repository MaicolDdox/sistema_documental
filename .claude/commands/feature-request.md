---
name: feature-request
description: Estructura e ingresa una nueva funcionalidad para GIDESTH antes de enviarla al agente feature-builder. Analiza el impacto, lista todos los archivos a crear o modificar, y produce un plan de implementación. Usar antes de pedirle al agente que implemente algo nuevo.
disable-model-invocation: false
---

Cuando el usuario pida implementar una funcionalidad nueva en GIDESTH, sigue este proceso.

## Tu rol
Eres el arquitecto previo. NO implementas — planificas. Tu objetivo es:
1. Entender exactamente qué se necesita construir
2. Identificar TODOS los archivos afectados
3. Detectar dependencias con el sistema existente
4. Producir un plan claro para el agente feature-builder

---

## Paso 1 — Clasificar el tipo de feature

### Tipo A — Módulo completo nuevo
Nueva sección del sistema con CRUD completo.
Archivos involucrados típicos: migration, model, factory, service, controller, livewire component, vista blade, rutas, policy, tests.

### Tipo B — Campo/columna en módulo existente
Agregar datos a una entidad ya existente.
Archivos involucrados típicos: migration nueva, model ($fillable, casts), form request (reglas), livewire component (propiedad + vista), tests.

### Tipo C — Exportación PDF o Excel nueva
Nuevo reporte descargable.
Archivos involucrados típicos: controller de reporte, vista blade para PDF o lógica PhpSpreadsheet, ruta, botón en vista existente, test.

### Tipo D — Notificación o email automático
Email que se dispara ante un evento.
Archivos involucrados típicos: clase Mail, vista blade del email, trigger en service o controller existente, configuración de cola si aplica, test.

---

## Paso 2 — Leer el contexto relevante (SOLO lo necesario)

```bash
# Para Tipo A — ver si ya existe un módulo similar como base:
ls app/Livewire/Admin/
ls app/Http/Controllers/Admin/

# Para Tipo B — leer el modelo y migración más reciente:
cat app/Models/[Modelo].php
ls -t database/migrations/ | head -5

# Para Tipo C — ver reportes existentes como referencia:
ls app/Http/Controllers/ | grep -i report
ls resources/views/pdf/

# Para Tipo D — ver si ya hay clases Mail existentes:
ls app/Mail/
```

---

## Paso 3 — Verificar impacto en TrainingCenterAccess

Para cualquier tipo de feature, responder:
- ¿La nueva funcionalidad maneja datos que deben estar aislados por `training_center_id`?
- ¿Qué roles tendrán acceso?
- ¿Se necesita una Policy nueva o se extiende una existente?

---

## Paso 4 — Producir el plan de implementación

Genera exactamente este formato:

```
## PLAN DE FEATURE — GIDESTH
**ID:** FEAT-[fecha]-[número]
**Tipo:** [A / B / C / D]
**Nombre:** [descripción corta]

### Descripción
[2-3 oraciones de qué hace esta funcionalidad]

### Roles con acceso
[lista de roles que pueden usar esta feature]

### ¿Requiere training_center_id?
[SÍ — descripción / NO — razón]

### Archivos a CREAR (nuevos)
1. `ruta/archivo.php` — [qué hace]
2. `ruta/archivo.blade.php` — [qué hace]

### Archivos a MODIFICAR (existentes)
1. `ruta/archivo.php` — [qué se agrega/cambia]
2. `routes/web.php` — [qué ruta se agrega]

### Orden de implementación recomendado
1. Migration (si aplica)
2. Model
3. Service
4. Controller / Livewire
5. Vista Blade / Flux
6. Rutas
7. Policy (si aplica)
8. Tests

### Dependencias con código existente
[qué archivos actuales se tocan y por qué]

### Riesgos identificados
[posibles problemas o conflictos]

---
Acción siguiente:
"Usa el agente feature-builder para implementar el FEAT-[ID]"
```

---

## Reglas de eficiencia
- NO empezar a escribir código — solo el plan
- Si la feature es ambigua, hacer máximo UNA pregunta de aclaración
- Identificar si alguno de los tipos de feature se puede reutilizar de código existente
- Si el alcance es muy grande (más de 15 archivos), sugerir dividirla en sub-features
