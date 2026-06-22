---
name: test-coverage-map
description: Mapea qué módulos y roles de GIDESTH tienen tests PHPUnit y cuáles no. Actualmente el proyecto tiene 11 tests. Genera un plan priorizado de qué cubrir primero según criticidad del módulo.
disable-model-invocation: false
---

Mapea la cobertura de tests en GIDESTH y prioriza qué crear.

## Paso 1 — Estado actual de tests
```bash
find tests/ -name "*.php" -type f
php artisan test --list-tests
```

## Paso 2 — Mapear módulos sin tests
```bash
# Módulos que DEBERÍAN tener tests:
ls app/Livewire/
ls app/Http/Controllers/
ls app/Services/
ls app/Policies/
```

## Prioridad de cobertura para GIDESTH

### CRÍTICO (sin estos, las pruebas en producción son ciegas)
1. `TrainingCenterAccess` — el núcleo del multi-tenancy
2. Autenticación y roles — Fortify + Spatie Permission
3. Módulo Admin/Users — gestión de usuarios y centros
4. Policies — autorización por recurso

### ALTO
5. Componentes Livewire con formularios de creación
6. Services con lógica de negocio compleja
7. Exportaciones PDF/Excel — fácil que fallen silenciosamente

### MEDIO
8. Componentes de listado (tabla + búsqueda)
9. Middleware custom
10. Form Requests — validaciones

### BAJO
11. Vistas Blade — verificación visual mejor que tests
12. Rutas — cubiertos indirectamente

## Formato de reporte
```
## Mapa de Cobertura de Tests — GIDESTH

### Tests existentes (11)
- [lista de tests actuales y qué cubren]

### Sin cobertura — CRÍTICO
- [módulo] → test sugerido → comando para crearlo

### Sin cobertura — ALTO
- [módulo] → test sugerido

### Plan de implementación (orden recomendado)
1. [test más crítico]
2. [segundo]
...

### Para crear el siguiente test
Usa el agente test-writer: "Genera tests para [módulo]"
```
