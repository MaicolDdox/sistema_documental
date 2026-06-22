---
name: naming-check
description: Verifica que los archivos y clases de GIDESTH sigan las convenciones de nombrado del proyecto. Detecta modelos en plural, controladores en singular, componentes Livewire con nombres poco descriptivos, y vistas con nomenclatura inconsistente.
disable-model-invocation: false
---

Verifica convenciones de nombrado en GIDESTH.

## Convenciones del proyecto

| Elemento | Convención | Ejemplo correcto | Ejemplo incorrecto |
|---------|-----------|-----------------|-------------------|
| Modelo | PascalCase singular | `ResearchGroup` | `ResearchGroups`, `research_group` |
| Controlador | PascalCase plural + Controller | `ResearchGroupsController` | `ResearchGroupController` |
| Componente Livewire | PascalCase descriptivo | `UserIndex`, `ProductoForm` | `Users`, `Form` |
| Vista Livewire | kebab-case del componente | `user-index.blade.php` | `userIndex.blade.php` |
| Vista Blade | kebab-case | `crear-semillero.blade.php` | `crearSemillero.blade.php` |
| Ruta (prefix) | kebab-case | `/director-semilleros` | `/directorSemilleros` |
| Nombre de ruta | snake_case con punto | `admin.users.index` | `adminUsersIndex` |
| Service | PascalCase + Service | `ResearchGroupService` | `GroupService`, `ResearchGroups` |
| Enum | PascalCase + Enum | `EstadoEnum` | `Estados`, `EstadoType` |
| Constante | SNAKE_CASE | `CENTRO_BOUND_ROLE_NAMES` | `centroBoundRoles` |
| Método | camelCase | `toggleEstado()` | `toggle_estado()` |
| Propiedad | camelCase | `$trainingCenterId` | `$training_center_id` |
| Migración | snake_case descriptivo | `add_estado_to_seedlings_table` | `addEstado` |

## Paso 1 — Escanear nombres
```bash
ls app/Models/
ls app/Http/Controllers/
ls app/Livewire/
ls app/Services/
ls resources/views/livewire/
```

## Formato de reporte
```
## Check de Nomenclatura — [módulo]

### Nombres incorrectos
- [archivo actual] → [nombre correcto sugerido]
  Razón: [regla violada]

### Rutas con nombres inconsistentes
- [ruta] → nombre actual → nombre sugerido

### ✅ Correctos
[lista de lo que está bien]
```
