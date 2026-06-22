---
name: gen-lista-chequeo
description: Genera la Lista_Chequeo_Pruebas_SIGESI.xlsx con contenido real. Lee el historial de git, cruza BUG-IDs con casos de prueba, y llena automáticamente las 5 hojas del Excel incluyendo el Registro de Bugs. Usar después de ejecutar las pruebas y corregir los bugs.
disable-model-invocation: false
---

Genera la Lista de Chequeo de Pruebas del sistema SIGESI con resultados reales.

## Proceso completo

### Paso 1 — Invocar el agente system-analyst

```
Usa el agente system-analyst para analizar el sistema SIGESI completo
y generar el mapa de resultados de pruebas.

Necesito específicamente:
1. El análisis completo del sistema (módulos, rutas, roles)
2. El historial de git con TODOS los BUG-IDs y commits de fix
3. El mapa BUG-ID → Caso de prueba (CP-XXX)
4. El estado inferido de cada uno de los 117 casos:
   - ✅ PASA: bug corregido y confirmado en ese módulo/funcionalidad
   - ❌ FALLA: bug conocido sin corregir
   - ⬜ PENDIENTE: sin información de ejecución
   - 🔄 EN PROGRESO: fix en proceso
5. Los datos reales de credenciales (de seeders)
6. La fecha de cada corrección (fecha del commit de fix)

Ejecuta TODAS las fases incluyendo la Fase 5 de historial de git.
```

### Paso 2 — Preparar el entorno

```bash
mkdir -p docs/pruebas
pip install openpyxl --break-system-packages 2>/dev/null || pip install openpyxl
```

### Paso 3 — Invocar el agente doc-generator

```
Usa el agente doc-generator para generar Lista_Chequeo_Pruebas_SIGESI.xlsx.

Usa este análisis:
[INSERTAR ANÁLISIS COMPLETO DEL system-analyst AQUÍ]

Genera el Excel con estas 5 hojas:

═══ HOJA 1: PORTADA ═══
- Título: LISTA DE CHEQUEO DE PRUEBAS
- Sistema: SIGESI — Gestión Documental SENA
- Institución: SENA CEFA
- Framework: Laravel / PHP 8.2
- Entorno: http://localhost
- Total de casos: 117
- Versión: 1.0 | Fecha: 2026-04-08
- Campos vacíos para llenar: "Ejecutado por:", "Fecha ejecución:", "Versión probada:"
- Convenciones: ✅ PASA, ❌ FALLA, ⬜ PENDIENTE, 🔄 EN PROGRESO, ⚠️ BLOQUEADO

═══ HOJA 2: ✅ CHECKLIST DE PRUEBAS ═══
Columnas (12):
  A: ID CASO
  B: MÓDULO
  C: FUNCIONALIDAD
  D: DESCRIPCIÓN
  E: PRECONDICIONES
  F: DATOS DE ENTRADA
  G: PASOS DE EJECUCIÓN
  H: RESULTADO ESPERADO
  I: RESULTADO OBTENIDO  ← llenar con resultado real del análisis git
  J: ESTADO              ← ✅/❌/⬜ según mapa BUG-ID
  K: FECHA SOLUCIÓN BUG  ← fecha del commit de fix si aplica
  L: NOTAS / OBSERVACIONES ← BUG-ID vinculado si aplica

Llenar las 117 filas con:
- Columnas A-H: datos completos del sistema real
- Columna I: descripción del resultado basada en el análisis
- Columna J: estado real (✅/❌/⬜) basado en git history
- Columna K: fecha del commit de fix si hubo corrección
- Columna L: referencia al BUG-ID si aplica (ej: "Corregido BUG-2026-05-26-1")

Formato de la hoja:
- Fila 1-4: encabezado institucional
- Fila 5: headers de columna (azul oscuro, texto blanco, negrita)
- Filas 6+: datos alternando blanco/azul claro
- Columnas anchas: G (pasos) y H (resultado esperado) = 300px mínimo

═══ HOJA 3: 📊 DASHBOARD ═══
Con fórmulas COUNTIF que calculan automáticamente:
- Total casos: 117
- ✅ Pasaron: =COUNTIF('✅ CHECKLIST DE PRUEBAS'!J:J,"✅ PASA")
- ❌ Fallaron: =COUNTIF('✅ CHECKLIST DE PRUEBAS'!J:J,"❌ FALLA")
- ⬜ Pendientes: =COUNTIF('✅ CHECKLIST DE PRUEBAS'!J:J,"⬜ PENDIENTE")
- % Aprobación: =pasaron/117*100

Tabla de resumen por módulo con COUNTIFS por módulo y estado.

═══ HOJA 4: 🔑 CREDENCIALES ═══
Columnas: ROL | EMAIL | URL TRAS LOGIN
Llenar con los datos REALES encontrados en seeders:
- Super Administrador → superadmin@sena.edu.co → /super-admin/dashboard
- [resto de roles con emails reales]
- Contraseña universal: Password123! (en encabezado)

═══ HOJA 5: 🐛 REGISTRO DE BUGS ═══
Columnas: BUG ID | ID CASO | MÓDULO | DESCRIPCIÓN DEL BUG | PASOS PARA REPRODUCIR | SEVERIDAD | ESTADO BUG | FECHA SOLUCIÓN

Llenar con TODOS los BUG-IDs encontrados en el historial de git:
- BUG-ID real del sistema (del git log)
- CP-XXX vinculado (del mapa del system-analyst)
- Módulo afectado
- Descripción del bug (del commit message)
- Severidad inferida: CRÍTICA/ALTA/MEDIA/BAJA según el módulo
- Estado: RESUELTO (si tiene commit de fix) o ABIERTO
- Fecha de la corrección (del commit)

Si no hay BUG-IDs en git, dejar las filas vacías con los headers.

Guardar en: docs/pruebas/Lista_Chequeo_Pruebas_SIGESI.xlsx

IMPORTANTE: El Excel debe abrir correctamente en Microsoft Excel y LibreOffice.
Las fórmulas del Dashboard deben funcionar sin errores.
```

### Paso 4 — Verificar y recalcular fórmulas

```bash
# Verificar que el archivo existe
ls -lh docs/pruebas/Lista_Chequeo_Pruebas_SIGESI.xlsx

# Recalcular fórmulas con LibreOffice
python scripts/recalc.py docs/pruebas/Lista_Chequeo_Pruebas_SIGESI.xlsx 2>/dev/null || echo "Sin recalculación - fórmulas activas al abrir"
```

### Paso 5 — Presentar al usuario

```
El Excel está listo en docs/pruebas/Lista_Chequeo_Pruebas_SIGESI.xlsx
```

## Notas importantes
- Este es el único documento con contenido real — los otros dos son plantillas
- Si no hay BUG-IDs en git, la columna J queda toda en ⬜ PENDIENTE
- El Dashboard calcula automáticamente al abrir el Excel
- Los vínculos BUG-ID → CP-XXX se basan en el análisis del system-analyst
- Cuanto más completo esté el historial de git, más preciso será el resultado
