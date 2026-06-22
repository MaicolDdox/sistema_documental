---
name: bug-report
description: Intake estructurado de un bug encontrado en pruebas. Clasifica el error, identifica los archivos afectados y prepara el contexto mínimo necesario para que el agente bug-fixer lo corrija. Usar cada vez que encuentres un error durante las pruebas.
disable-model-invocation: false
---

Cuando el usuario reporte un bug, sigue este proceso exacto para consumir el mínimo de tokens.

## Tu rol
Eres el primer filtro. Tu objetivo es:
1. Entender el error con precisión
2. Leer SOLO los archivos relevantes (no el proyecto completo)
3. Clasificar el tipo de bug
4. Preparar un diagnóstico conciso para el agente bug-fixer

## Paso 1 — Leer la evidencia del usuario

El usuario puede enviarte cualquiera de estas formas de evidencia:
- **Mensaje de error** (stack trace, excepción Laravel)
- **Descripción** ("cuando hago X, pasa Y en lugar de Z")
- **Captura de pantalla** (imagen del error en el navegador)
- **Log** (fragmento de `storage/logs/laravel.log`)

## Paso 2 — Identificar archivos afectados (MÍNIMO necesario)

Según el tipo de error, leer SOLO estos archivos:

### Error 500 / Excepción PHP
```bash
# Leer el log reciente:
tail -n 50 storage/logs/laravel.log

# Leer el archivo mencionado en el stack trace
# NO leer archivos que no aparezcan en el error
```

### Error de Livewire (componente)
```bash
# Leer solo el componente afectado:
cat app/Livewire/{Modulo}/{Componente}.php
cat resources/views/livewire/{modulo}/{vista}.blade.php
```

### Error de base de datos / Query
```bash
# Leer el modelo y su migración más reciente:
cat app/Models/{Modelo}.php
ls -t database/migrations/ | head -5
```

### Error de autorización / 403
```bash
# Leer Policy y middleware relevante:
cat app/Policies/{Policy}.php
cat app/Support/TrainingCenterAccess.php
```

### Error de validación
```bash
# Leer el Form Request o los #[Validate] del componente:
cat app/Http/Requests/{Request}.php
# O el componente Livewire si usa #[Validate]
```

## Paso 3 — Clasificar el bug

Clasifica en UNA de estas categorías:

| Categoría | Ejemplos |
|-----------|---------|
| **A — Lógica** | Resultado incorrecto, cálculo mal, condición equivocada |
| **B — Autorización** | 403, acceso indebido, training_center_id incorrecto |
| **C — Livewire/UI** | Componente no actualiza, evento no dispara, propiedad no sincroniza |
| **D — Base de datos** | Query falla, N+1, migración rota, FK violation |
| **E — Validación** | Mensaje incorrecto, campo no validado, error no muestra |
| **F — Vista/Blade** | Dato no muestra, layout roto, variable undefined |

## Paso 4 — Formato de salida (CONCISO)

Genera exactamente este bloque y nada más:

```
## 🐛 Bug Report — GIDESTH
**ID:** BUG-[fecha]-[número]
**Categoría:** [A/B/C/D/E/F — nombre]
**Severidad:** [CRÍTICO | ALTO | MEDIO | BAJO]

**Descripción del error:**
[1-2 oraciones precisas de qué falla]

**Archivos afectados:**
- `ruta/al/archivo.php` — [por qué es relevante]
- `ruta/a/la/vista.blade.php` — [si aplica]

**Causa probable:**
[1 oración técnica de por qué ocurre]

**Acción siguiente:**
Enviar este reporte al agente bug-fixer con el comando:
"Usa el agente bug-fixer para corregir el BUG-[ID]"
```

## Reglas de eficiencia de tokens
- NO leer archivos que no estén relacionados directamente con el error
- NO generar la corrección en esta skill — eso es trabajo del agente bug-fixer
- NO repetir el stack trace completo — solo las líneas relevantes
- Si el error no es claro, pedir UNA sola aclaración al usuario antes de continuar
