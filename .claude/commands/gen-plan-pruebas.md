---
name: gen-plan-pruebas
description: Genera el documento formal Plan_Pruebas_SIGESI.docx. Estudia profundamente el sistema, entiende la arquitectura y produce el plan completo listo para entrega. Usar UNA SOLA VEZ antes de iniciar las pruebas formales.
disable-model-invocation: false
---

Genera el Plan de Pruebas formal del sistema SIGESI.

## Proceso completo

### Paso 1 — Invocar el agente system-analyst

```
Usa el agente system-analyst para analizar el sistema SIGESI completo.
Necesito el análisis para generar el Plan de Pruebas.
Ejecuta todas las fases del análisis y produce el reporte estructurado completo.
```

Esperar el reporte completo del system-analyst antes de continuar.

### Paso 2 — Preparar el entorno

```bash
mkdir -p docs/pruebas
npm install -g docx 2>/dev/null || npm install docx 2>/dev/null
```

### Paso 3 — Invocar el agente doc-generator

```
Usa el agente doc-generator para generar el Plan_Pruebas_SIGESI.docx.

Usa este análisis del sistema:
[INSERTAR ANÁLISIS COMPLETO DEL system-analyst AQUÍ]

Genera el documento con:
- Portada: SIGESI, SENA CEFA, Juan Esteban Aldana Cortes, 2026
- Tabla de versiones: versión 1.0, fecha 2026-04-08
- Sección 1: Introducción con propósito y alcance reales del sistema analizado
- Sección 2: Elementos para probar — los 10 módulos con descripción real
- Sección 3: Características para probar — FT-01 al FT-10, NFT-01 al NFT-03
- Sección 4: Estrategia de pruebas basada en la arquitectura real
- Sección 5: Criterios de aprobación
- Sección 6: Resumen de ejecución — tabla vacía (117 casos en 10 módulos)
- Sección 7: Aprobaciones con firma de Juan Esteban Aldana Cortes

Guardar en: docs/pruebas/Plan_Pruebas_SIGESI.docx
```

### Paso 4 — Verificar

```bash
ls -lh docs/pruebas/Plan_Pruebas_SIGESI.docx
```

### Paso 5 — Presentar al usuario

```
El documento está en docs/pruebas/Plan_Pruebas_SIGESI.docx
```

## Notas
- Este documento se genera UNA SOLA VEZ — es un documento de entrega formal
- La tabla de resumen de ejecución (sección 6) se deja vacía — se llena durante las pruebas
- El plan debe reflejar el sistema REAL analizado, no un plan genérico
