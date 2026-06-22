---
name: gen-especificacion
description: Genera el documento formal Especificacion_Casos_Prueba_SIGESI.docx con los 117 casos de prueba organizados por módulo. Lee el sistema real para generar casos con datos precisos. Usar antes de ejecutar las pruebas formales.
disable-model-invocation: false
---

Genera la Especificación Detallada de Casos de Prueba del sistema SIGESI.

## Proceso completo

### Paso 1 — Invocar el agente system-analyst

```
Usa el agente system-analyst para analizar el sistema SIGESI completo.
Necesito el análisis detallado de CADA módulo y funcionalidad para
generar los 117 casos de prueba con datos reales.

Presta especial atención a:
- Las rutas EXACTAS de cada módulo (URLs reales)
- Los emails reales de cada rol (de los seeders)
- Las validaciones de cada formulario
- El flujo de aprobación de productos
- Los tipos de archivo permitidos y rechazados
- Las restricciones de acceso por rol y centro
```

Esperar el análisis completo.

### Paso 2 — Preparar el entorno

```bash
mkdir -p docs/pruebas
npm install -g docx 2>/dev/null || npm install docx 2>/dev/null
```

### Paso 3 — Invocar el agente doc-generator

```
Usa el agente doc-generator para generar Especificacion_Casos_Prueba_SIGESI.docx.

Usa este análisis:
[INSERTAR ANÁLISIS DEL system-analyst AQUÍ]

Genera el documento con esta estructura:
- Portada: SIGESI, SENA CEFA, Equipo de Pruebas GIDESTH, Versión 1.0, 2026
- Introducción con objetivo y convenciones del documento
- 117 casos organizados por módulo (0 al 9)
- Cada caso en su propia tabla con estos campos:
  * ID Caso (CP-XXX-NN)
  * Módulo
  * Funcionalidad
  * Descripción
  * Precondiciones (con datos reales del sistema)
  * Datos de Entrada (emails reales, URLs reales)
  * Pasos (secuencia numerada con URLs exactas)
  * Resultado Esperado (comportamiento correcto)
  * Resultado Obtenido: [VACÍO — el tester lo llena]
  * Estado: ⬜ PENDIENTE

MÓDULOS Y CANTIDAD DE CASOS:
- Módulo 0: Autenticación — 7 casos (CP-AUTH-01 a CP-AUTH-07)
- Módulo 1: Super Administrador — 4 casos (CP-SA-01 a CP-SA-04)
- Módulo 2: Administrador Sistema — 12 casos (CP-ADM-01 a CP-ADM-12)
- Módulo 3: Director Semilleros — 11 casos (CP-DS-01 a CP-DS-11)
- Módulo 4: Líder Semillero — 17 casos (CP-LS-01 a CP-LS-17)
- Módulo 5: Asesor Semillero — 20 casos (CP-AS-01 a CP-AS-20)
- Módulo 6: Director Investigación — 18 casos (CP-DI-01 a CP-DI-18)
- Módulo 7: Investigador Asociado — 20 casos (CP-INV-01 a CP-INV-20)
- Módulo 8: Flujos E2E — 3 casos (CP-E2E-01 a CP-E2E-03)
- Módulo 9: Seguridad — 5 casos (CP-SEG-01 a CP-SEG-05)

IMPORTANTE:
- Las URLs deben ser las REALES encontradas en routes/web.php
- Los emails deben ser los REALES de los seeders
- Los datos de entrada deben ser ESPECÍFICOS y PRECISOS
- NO usar datos genéricos como "[valor]" si el sistema tiene datos reales
- La contraseña universal es Password123! (confirmada en seeders)

Guardar en: docs/pruebas/Especificacion_Casos_Prueba_SIGESI.docx
```

### Paso 4 — Verificar

```bash
ls -lh docs/pruebas/Especificacion_Casos_Prueba_SIGESI.docx
```

## Notas
- Los 117 casos deben estar completos — ninguno con datos faltantes
- Los campos Resultado Obtenido y Estado se dejan vacíos/PENDIENTE siempre
- Este documento se entrega al tester antes de iniciar las pruebas
