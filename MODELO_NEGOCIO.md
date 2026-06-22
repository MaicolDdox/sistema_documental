# Modelo de Negocio — Sistema Documental SENA GIDESTH
## Análisis por Rol y por Módulo

---

## CONTEXTO DEL SISTEMA

El **Sistema Documental GIDESTH** es una plataforma de gestión para la actividad investigativa del SENA. Centraliza la administración de **grupos de investigación**, **semilleros de investigación**, **proyectos**, **productos académicos** y **aprendices**, organizando el acceso por rol dentro de una jerarquía institucional.

### Entidades principales del dominio
| Entidad | Descripción |
|---------|-------------|
| **Centro de Formación** | Unidad organizativa del SENA. Ej: Centro Agroempresarial (9116), Centro Industria (9527) |
| **Grupo de Investigación** | Equipo de investigadores por centro. Ej: GIDESTH Agroindustrial |
| **Semillero de Investigación** | Grupo de aprendices con un líder, bajo supervisión de asesores |
| **Proyecto** | Unidad de trabajo investigativo, vinculable a semilleros y grupos |
| **Producto** | Resultado académico (artículo, prototipo, software, etc.) con ciclo de revisión |
| **Macroproyecto** | Proyecto paraguas que agrupa proyectos relacionados |
| **Aprendiz** | Integrante de un semillero, puede ser autor de proyectos/productos |

---

## FLUJO GENERAL DEL SISTEMA

```
Super Admin
    └── crea centros de formación y asigna admins por centro

Administrador del Sistema
    └── gestiona usuarios, catálogos, grupos de investigación

Director de Investigación
    └── gestiona su grupo: vincula investigadores, aprueba productos

Director de Semilleros
    └── crea y gestiona semilleros, asigna líderes

Líder de Semillero
    └── administra su semillero: miembros, asesores, productos, archivos

Asesor de Semillero
    └── gestiona proyectos y productos del semillero, registra aprendices

Investigador Asociado
    └── crea proyectos y productos del grupo, recibe productos del semillero
```

---

## ROL 1: SUPER ADMINISTRADOR

### Descripción del rol
Máxima autoridad del sistema. Visibilidad global sobre todos los centros, grupos y usuarios. Sin restricción por centro de formación.

### Módulos accesibles

#### 1.1 Dashboard Global
- Métricas del sistema completo: total usuarios, grupos activos, semilleros, centros, proyectos
- No filtrado por centro

#### 1.2 Centros Administradores
- **Función:** Asignar un administrador del sistema (`administrador_sistema`) a cada centro de formación
- **Restricción:** Un centro solo puede tener un administrador a la vez
- **Ruta:** `POST /super-admin/link-admin-training-center`

### Estado del módulo
| Módulo | Implementado | Completo |
|--------|-------------|---------|
| Dashboard global | ✅ | ✅ |
| Asignación de admins a centros | ✅ | ✅ |
| Gestión de centros de formación | ❌ | ❌ |
| Configuración global del sistema | ❌ | ❌ |

> **Gap:** El super admin no puede crear o editar centros de formación desde su módulo. Esa funcionalidad está en el módulo del admin.

---

## ROL 2: ADMINISTRADOR DEL SISTEMA

### Descripción del rol
Gestiona un centro de formación específico. Ve y administra solo los datos de su centro (scoping por `training_center_id`).

### Módulos accesibles

#### 2.1 Dashboard del Centro
- Total de usuarios activos/inactivos
- Grupos de investigación del centro
- Semilleros del centro
- Proyectos y productos del centro

#### 2.2 Gestión de Usuarios
- **Listar** todos los usuarios del centro con rol y estado
- **Crear** usuarios: genera persona + usuario con rol asignado
- **Editar** datos personales y de usuario
- **Activar/Desactivar** usuarios (toggle estado)
- **Asignar/Revocar rol** a usuarios existentes
- **Restablecer contraseña** manualmente
- Componentes Livewire: `UserCreate`, `UserEdit`, `UserIndex`

#### 2.3 Gestión de Grupos de Investigación
- CRUD completo de grupos
- Asignación de centro de formación al grupo
- Toggle de estado (activo/inactivo)

#### 2.4 Catálogos del Sistema
Los siguientes catálogos son configurables desde el admin:

| Catálogo | Tabla | CRUD |
|----------|-------|------|
| Departamentos | departments | ✅ |
| Ciudades | cities | ✅ |
| Centros de formación | training_centers | ✅ |
| Cargos / posiciones | entity_positions | ✅ |
| Tipos de vinculación | linkage_types | ✅ |
| Registros de formación | training_records | ✅ |
| Tipos de programas | training_program_types | ✅ |
| Programas de formación | training_programs | ✅ |
| Líneas de investigación | research_lines | ✅ |
| Líneas tecnológicas | technological_lines | ✅ |
| Áreas temáticas | thematic_areas | ✅ |
| Modalidades de proyecto | project_modalities | ✅ |
| Tipos de investigación | investigation_types | ✅ |
| Tipologías Minciencias | minciencias_typologies | ✅ |
| Subcategorías Minciencias | minciencias_subcategories | ✅ |
| Grandes áreas de conocimiento | knowledge_grand_areas | ✅ |
| Áreas de conocimiento | knowledge_areas | ✅ |

#### 2.5 Asesores Externos
- Registro de asesores externos (sin cuenta del sistema)
- Visualización y gestión básica

### Estado del módulo
| Módulo | Implementado | Completo |
|--------|-------------|---------|
| Dashboard | ✅ | ✅ |
| Usuarios | ✅ | ✅ |
| Grupos de investigación | ✅ | ✅ |
| Catálogos | ✅ | ✅ |
| Asesores externos | ✅ | Parcial |
| Reportes del centro | ❌ | ❌ |

---

## ROL 3: DIRECTOR DE INVESTIGACIÓN

### Descripción del rol
Dirige un grupo de investigación. Gestiona su equipo, revisa y aprueba productos académicos, genera reportes del grupo.

### Módulos accesibles

#### 3.1 Dashboard del Grupo
- Total investigadores del grupo
- Productos pendientes de revisión
- Proyectos activos
- Actividad reciente

#### 3.2 Gestión de Investigadores
- **Crear** investigadores: crea usuario + persona + vincula al grupo con rol
- **Cambiar rol** interno en el grupo (Director / Investigador Líder / Investigador Asociado / Integrante)
- **Desvincular** investigador del grupo
- **Toggle estado** (activar/desactivar)
- **Restablecer contraseña**

**Roles internos del grupo (RolGrupoEnum):**
- `Director` — el propio director
- `InvestigadorLider` — investigador con rol de liderazgo interno
- `InvestigadorAsociado` — investigador estándar
- `Integrante` — participante sin rol de investigación

#### 3.3 Revisión de Productos
- **Listar** productos del grupo con estado de revisión
- **Ver detalle** de cada producto con sus evidencias
- **Aprobar** producto (requiere evidencia adjunta)
- **Rechazar** con observaciones obligatorias
- **Cambiar a "En Revisión"** para indicar proceso activo

**Flujo de revisión:**
```
Pendiente → En Revisión → Aprobado
                        → Rechazado
```

#### 3.4 Documentos del Grupo
- Subir documentos institucionales del grupo (actas, informes, etc.)
- Listar y eliminar documentos propios

> **Nota técnica:** Actualmente usa la tabla `seedling_internal_documents` como workaround. Hay un TODO para crear tabla `group_documents` dedicada.

#### 3.5 Macroproyectos
- CRUD completo de macroproyectos del grupo
- Activar/desactivar macroproyectos
- Los proyectos pueden vincularse a un macroproyecto

#### 3.6 Reportes del Grupo
- **Actividad general:** resumen de proyectos y productos del grupo
- **Aprobados vs Rechazados:** comparativa por período
- **Productos por investigador:** distribución de producción
- **Productos por año:** tendencia temporal
- **Exportar CSV:** datos tabulares para análisis externo
- **Exportar PDF:** informe formal del grupo

### Estado del módulo
| Módulo | Implementado | Completo |
|--------|-------------|---------|
| Dashboard | ✅ | ✅ |
| Gestión de investigadores | ✅ | ✅ |
| Revisión de productos | ✅ | ✅ |
| Documentos del grupo | ✅ | Parcial (tabla compartida) |
| Macroproyectos | ✅ | ✅ |
| Reportes | ✅ | ✅ |

---

## ROL 4: DIRECTOR DE SEMILLEROS

### Descripción del rol
Coordina todos los semilleros de su centro de formación. Crea semilleros, asigna líderes, gestiona documentación institucional y genera reportes.

### Módulos accesibles

#### 4.1 Dashboard de Semilleros
- Total semilleros activos/inactivos del centro
- Total líderes registrados
- Total aprendices en semilleros
- Total asesores

#### 4.2 Gestión de Semilleros
- **Crear** semillero: nombre, código, logo, descripción, grupo de investigación
- **Editar** datos del semillero
- **Toggle estado** (activar/desactivar)
- **Reasignar líder** a un semillero
- **Ver detalle** completo del semillero

#### 4.3 Gestión de Líderes
- **Crear** líderes de semillero: genera usuario con rol `lider_semillero`
- **Editar** datos del líder
- **Toggle estado** (activar/desactivar)
- **Enviar credenciales** por email (envío de contraseña al líder)
- Eliminar líder (si no tiene semilleros activos asignados)

#### 4.4 Vinculaciones Semillero-Líder
- Ver tabla de vinculaciones existentes
- Actualizar qué líder está asignado a qué semillero
- Un semillero puede tener múltiples líderes históricamente

#### 4.5 Documentos Institucionales
- Subir documentos del centro/semillero (reglamentos, actas, etc.)
- Listar documentos
- Eliminar documentos propios

#### 4.6 Reportes de Semilleros
- Resumen de semilleros con métricas
- Aprendices por semillero
- Proyectos por estado
- Exportar a PDF/Excel

### Estado del módulo
| Módulo | Implementado | Completo |
|--------|-------------|---------|
| Dashboard | ✅ | ✅ |
| Gestión de semilleros | ✅ | ✅ |
| Gestión de líderes | ✅ | Parcial (email no confirmado) |
| Vinculaciones | ✅ | ✅ |
| Documentos | ✅ | ✅ |
| Reportes | ✅ | ✅ |

---

## ROL 5: LÍDER DE SEMILLERO

### Descripción del rol
Coordina un semillero específico. Gestiona sus asesores, supervisa proyectos, aprueba productos del semillero y administra archivos y documentación interna.

### Módulos accesibles

#### 5.1 Dashboard del Semillero
- Resumen del semillero propio
- Integrantes activos y sus estados de proyecto
- Productos pendientes de aprobación
- Proyectos en curso

#### 5.2 Información del Semillero
- Vista de solo lectura con datos del semillero
- No puede editar datos maestros del semillero (eso lo hace el director)

#### 5.3 Integrantes
- Listado de miembros con indicadores de estado de sus proyectos
- Vista tipo tarjeta por integrante
- No puede agregar/eliminar integrantes (lo hace el asesor)

#### 5.4 Gestión de Asesores
- **Registrar** asesor: puede crear cuenta del sistema (rol `asesor_semillero`) o solo registrarlo como externo
- **Editar** datos del asesor
- **Toggle estado** (activar/desactivar asesor en el semillero)
- **Eliminar** asesor

#### 5.5 Proyectos (vista de consulta)
- Listado de proyectos vinculados al semillero
- Solo lectura — no puede crear ni editar proyectos (eso lo hace el asesor)

#### 5.6 Gestión de Productos
- **Registrar** producto: vinculado a un proyecto del semillero, con archivo o URL de repositorio
- **Ver detalle** del producto con estado de revisión
- **Aprobar** producto del semillero
- **Rechazar** producto del semillero con observación
- **Asignar a investigador asociado** del grupo vinculado (formalización hacia el grupo)

**Flujo de producto en semillero:**
```
Asesor registra producto
    → Líder revisa
    → Líder aprueba/rechaza
    → Si aprueba: asigna a investigador del grupo
    → Investigador lo formaliza como GroupProduct
```

#### 5.7 Archivos del Semillero
- Subir archivos (ponencias, informes, actas, logos, etc.)
- Ver/descargar archivos
- Eliminar archivos propios

#### 5.8 Documentación Interna
- Subir documentos internos (actas de reunión, informes de actividad)
- Ver/descargar documentos
- Eliminar documentos propios

### Estado del módulo
| Módulo | Implementado | Completo |
|--------|-------------|---------|
| Dashboard | ✅ | ✅ |
| Info semillero | ✅ | ✅ |
| Integrantes | ✅ | ✅ |
| Asesores | ✅ | ✅ |
| Proyectos (consulta) | ✅ | ✅ |
| Productos | ✅ | ✅ |
| Archivos | ✅ | ✅ |
| Documentación interna | ✅ | ✅ |

---

## ROL 6: ASESOR DE SEMILLERO

### Descripción del rol
Operador directo del semillero. Registra aprendices, crea proyectos, registra productos, sube evidencias y genera reportes. Puede estar asignado a múltiples semilleros.

### Módulos accesibles

#### 6.1 Dashboard
- Resumen de todos sus semilleros
- Proyectos y productos agregados
- Aprendices totales

#### 6.2 Mis Semilleros
- Lista de semilleros donde es asesor
- Selector de "semillero activo" para enfocar el trabajo
- El semillero activo se guarda en sesión y filtra los demás módulos

#### 6.3 Gestión de Aprendices
- **Registrar** aprendiz: datos personales, programa de formación, tipo de vinculación
- **Ver detalle** del aprendiz
- **Editar** datos
- **Desactivar** aprendiz
- Buscar aprendiz por documento de identidad

#### 6.4 Gestión de Proyectos
- **Crear** proyecto vinculado al semillero activo
- **Editar** proyecto (nombre, descripción, fechas, líneas, modalidad, tipo)
- **Desactivar** proyecto
- **Gestionar integrantes**: vincular/desvincular aprendices como autores del proyecto

#### 6.5 Gestión de Productos
- **Registrar** producto vinculado a un proyecto
- **Ver detalle** con estado de revisión
- **Editar** producto
- **Desactivar** producto
- **Eliminar** producto (si no tiene restricciones)
- **Descargar** archivo del producto
- API: proyectos por semillero, autores por proyecto (para formularios dinámicos)

#### 6.6 Gestión de Evidencias
- Subir evidencias de proyecto (archivos probatorios)
- Subir evidencias de producto
- Listar evidencias
- Eliminar evidencias propias

#### 6.7 Exportar Reportes
- Dashboard resumen exportable
- Semilleros asignados
- Proyectos del semillero
- Productos registrados
- Aprendices por semillero

### Estado del módulo
| Módulo | Implementado | Completo |
|--------|-------------|---------|
| Dashboard | ✅ | ✅ |
| Mis semilleros | ✅ | ✅ |
| Aprendices | ✅ | ✅ |
| Proyectos | ✅ | ✅ |
| Productos | ✅ | ✅ |
| Evidencias | ✅ | ✅ |
| Exportar reportes | ✅ | ✅ |

---

## ROL 7: INVESTIGADOR ASOCIADO

### Descripción del rol
Miembro de un grupo de investigación. Crea proyectos del grupo, registra productos académicos, recibe productos del semillero para formalización y genera sus propios reportes.

### Módulos accesibles

#### 7.1 Dashboard del Investigador
- Mis proyectos activos
- Mis productos y su estado de revisión
- Productos pendientes del semillero (bandeja)
- Actividad reciente del grupo

#### 7.2 Gestión de Proyectos
- **Crear** proyecto del grupo (no del semillero)
- **Ver detalle** con autores y evidencias
- **Editar** proyecto
- **Gestionar autores** del proyecto (otros investigadores)
- **Finalizar** proyecto
- Subir/descargar evidencias del proyecto

#### 7.3 Gestión de Productos (GroupProduct)
- **Registrar** producto del grupo con metadatos completos:
  - Tipología Minciencias + subcategoría
  - Área de conocimiento
  - Tiene repositorio: sí/no + URL
  - Evidencia adjunta
  - Autorización de datos
- **Ver detalle** con historial de revisión
- **Editar** producto
- **Eliminar** producto

#### 7.4 Bandeja de Semilleros
- Ver productos enviados desde semilleros para su formalización
- Recibe productos que el líder aprobó y asignó a este investigador
- **Formalizar** un producto del semillero: lo convierte en `GroupProduct` con metadatos completos
  - Agregar tipología Minciencias
  - Clasificar por área de conocimiento
  - Completar información académica

**Este es el puente entre el módulo semillero y el módulo de investigación.**

#### 7.5 Estados de Productos
- Vista de todos sus productos con su estado de revisión actual
- Historial de revisiones: quién revisó, qué dijo, cuándo

#### 7.6 Reportes
- Mis productos por estado
- Exportar en CSV
- Exportar en PDF

### Estado del módulo
| Módulo | Implementado | Completo |
|--------|-------------|---------|
| Dashboard | ✅ | ✅ |
| Proyectos | ✅ | ✅ |
| Productos | ✅ | ✅ |
| Bandeja de semilleros | ✅ | Parcial (formalización incompleta) |
| Estados | ✅ | ✅ |
| Reportes | ✅ | ✅ |

---

## MÓDULOS TRANSVERSALES

### Autenticación y Sesión
- Login con Livewire
- Autenticación de dos factores (2FA) via Fortify
- Logout seguro
- Prevención de back-button con cabeceras no-cache

### Configuración de Perfil (todos los roles)
- Editar perfil (nombre, email)
- Cambiar contraseña
- Configurar 2FA con QR code
- Eliminar cuenta
- Apariencia (tema claro/oscuro — stub sin implementar)

### Scoping por Centro (automático)
- Todo dato se filtra automáticamente por `training_center_id` del usuario autenticado
- Super admin es la única excepción (ve todo)
- Implementado via traits: `TrainingCenterAccess`, `AsesorSemilleroContext`, `DirectorContext`, `InvestigadorContext`

---

## FLUJO DE VIDA DE UN PRODUCTO (completo)

```
1. Asesor registra producto vinculado a proyecto del semillero
   ↓
2. Líder del semillero revisa el producto
   ↓
3a. Líder RECHAZA → Asesor recibe observación → puede corregir y reenviar
3b. Líder APRUEBA → Asigna producto a un Investigador Asociado del grupo
   ↓
4. Investigador recibe producto en su "Bandeja de Semilleros"
   ↓
5. Investigador FORMALIZA → Crea GroupProduct con metadatos Minciencias
   ↓
6. Director de Investigación revisa el GroupProduct
   ↓
7a. Director RECHAZA con observaciones → Investigador puede corregir
7b. Director marca "En Revisión" → Proceso activo
7c. Director APRUEBA → Producto finalizado en el sistema
```

---

## MODELO DE DATOS SIMPLIFICADO

```
TrainingCenter
    ├── ResearchGroup
    │   ├── ResearchGroupUser (rol: Director | InvestigadorLider | InvestigadorAsociado | Integrante)
    │   ├── MacroProject
    │   │   └── Project
    │   └── GroupProduct (producto del grupo con metadatos Minciencias)
    │       ├── GroupProductReview (historial de revisiones)
    │       └── ProductEvidence
    └── Seedling
        ├── SeedlingMember (aprendices)
        ├── SeedlingAdvisor (asesores externos)
        ├── SeedlingFile (archivos)
        ├── SeedlingInternalDocument (documentación interna)
        └── Project (proyectos del semillero)
            ├── ProjectAuthor (autores / aprendices)
            ├── ProjectEvidence (evidencias)
            └── Product (producto del semillero)
                └── → GroupProduct (cuando es formalizado por investigador)
```

---

## TABLA RESUMEN DE MÓDULOS POR ROL

| Módulo | Super Admin | Admin | Dir. Investigación | Dir. Semilleros | Líder Semillero | Asesor | Investigador |
|--------|:-----------:|:-----:|:------------------:|:---------------:|:---------------:|:------:|:------------:|
| Dashboard propio | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Gestión usuarios | — | ✅ | Parcial | Parcial | — | — | — |
| Catálogos | — | ✅ | — | — | — | — | — |
| Grupos investigación | — | ✅ | ✅ | — | — | — | — |
| Investigadores del grupo | — | — | ✅ | — | — | — | — |
| Macroproyectos | — | — | ✅ | — | — | — | — |
| Semilleros | — | ✅ | — | ✅ | Solo lectura | — | — |
| Líderes | — | — | — | ✅ | — | — | — |
| Asesores | — | ✅ | — | — | ✅ | — | — |
| Aprendices/Integrantes | — | — | — | — | Solo lectura | ✅ | — |
| Proyectos (semillero) | — | — | — | — | Solo lectura | ✅ | — |
| Proyectos (grupo) | — | — | ✅ | — | — | — | ✅ |
| Productos (semillero) | — | — | — | — | ✅ | ✅ | — |
| Productos (grupo/GroupProduct) | — | — | ✅ revisión | — | — | — | ✅ |
| Evidencias | — | — | ✅ | — | — | ✅ | ✅ |
| Documentos | — | — | ✅ | ✅ | ✅ | — | — |
| Archivos semillero | — | — | — | — | ✅ | — | — |
| Bandeja semilleros | — | — | — | — | — | — | ✅ |
| Reportes | — | ✅ | ✅ | ✅ | — | ✅ | ✅ |
| Exportar PDF/CSV | — | — | ✅ | ✅ | — | ✅ | ✅ |
| Configurar 2FA | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
