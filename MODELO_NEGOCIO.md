# Modelo de Negocio — Sistema Documental SENA GIDESTH
## Análisis por Rol y por Módulo

**Última actualización:** 2026-09-02 — reescrito completo tras el rediseño de roles de agosto de 2026 (ver `docs/SESION_2026-08-13_REDISENO_ROLES.md`, `docs/CHANGELOG-sesion-2026-08.md` y `docs/FEAT-20260830-001-multirol.md`). La versión anterior de este documento describía un sistema de 7 roles (`AsesorSemillero`, `DirectorInvestigacion`, `InvestigadorAsociado`, entidad `ResearchGroup`) que ya no existe en el código.

---

## CONTEXTO DEL SISTEMA

El **Sistema Documental GIDESTH** es una plataforma de gestión para la actividad investigativa del SENA. Centraliza la administración de **semilleros de investigación**, **proyectos**, **evidencias/productos** y **productos Minciencias personales**, organizando el acceso por rol dentro de una jerarquía institucional por centro de formación.

El sistema tiene **6 roles activos**: `super_administrador`, `administrador_sistema`, `director_semilleros`, `lider_semillero`, `lider_proyecto`, `co_investigador`. Un usuario puede tener un **rol principal** más **roles adicionales**, y cambiar de "rol activo" en cualquier momento — el acceso a cada módulo queda aislado estrictamente al rol activo (ver [Sistema de rol activo](#sistema-de-rol-activo-multi-rol)).

### Entidades principales del dominio

| Entidad | Descripción |
|---------|-------------|
| **Centro de Formación** (`TrainingCenter`) | Unidad organizativa del SENA. Todo usuario con rol "ligado a centro" pertenece a exactamente uno |
| **Semillero de Investigación** (`Seedling`) | Grupo de investigación de base, con un líder de semillero, creado por un Director de Semilleros de su centro |
| **Proyecto** (`Project`) | Unidad de trabajo dentro de un semillero, con un único Líder de Proyecto (`lider_proyecto_user_id`, restricción de unicidad a nivel de BD) |
| **Aprendiz** (`ProjectLearner`) | Dato libre asociado a un proyecto (nombre, documento, teléfono, email, programa de formación) — **no** es una cuenta de usuario del sistema |
| **Co-investigador vinculado** (`ProjectAuthor`) | Vínculo entre un `User` con rol `co_investigador` (rol global) y un proyecto en el que colabora |
| **Evidencia de proyecto** (`ProjectEvidence`) | Archivo subido por el Líder de Proyecto, de uno de 4 tipos, cada uno con su propio flujo de aprobación (ver [Flujo de avance del proyecto](#flujo-de-avance-del-proyecto)) |
| **Producto Minciencias** (`MincienciasProduct`) | Producto académico **personal** de un co-investigador, independiente de cualquier semillero o proyecto, sometido a revisión del administrador de un centro de formación elegido por el propio co-investigador |
| **Macroproyecto** (`MacroProject`) | Proyecto paraguas que agrupa proyectos relacionados |

> Las entidades `ResearchGroup` (grupo de investigación), `Product`/`GroupProduct` (producto de grupo con flujo Director de Investigación) y `KnowledgeArea`/`KnowledgeGrandArea` (áreas de conocimiento) del diseño anterior **fueron eliminadas por completo** — modelos, tablas, controladores, rutas y vistas.

---

## FLUJO GENERAL DEL SISTEMA

```
Super Administrador
    └── crea centros de formación, asigna un administrador_sistema a cada uno,
        y puede crear/editar directamente cualquier otro usuario del sistema
        (excepto otro super_administrador)

Administrador del Sistema (uno por centro, normalmente)
    └── gestiona los usuarios de su centro (solo lectura + edición/estado),
        crea Directores de Semilleros y Co-investigadores,
        administra catálogos, aprueba/rechaza productos Minciencias del centro

Director de Semilleros (por centro)
    └── crea y gestiona semilleros, asigna/reasigna Líderes de Semillero,
        aprueba la 2ª etapa de "producto final"

Líder de Semillero
    └── crea Líderes de Proyecto, revisa evidencias de sus proyectos
        (formulación, ejecución, 1ª etapa de producto final)

Líder de Proyecto
    └── registra aprendices (datos libres), vincula Co-investigadores
        existentes a su proyecto, sube evidencias

Co-investigador (rol global, sin centro de formación fijo)
    └── colabora en los proyectos donde está vinculado (solo evidencia de
        tipo "desarrollo"), y gestiona sus propios productos Minciencias
        personales, eligiendo el centro que los revisa
```

---

## SISTEMA DE ROL ACTIVO (MULTI-ROL)

Desde `FEAT-20260830-001` (agosto de 2026), un usuario puede tener un **rol principal** (comportamiento normal de login) más **roles adicionales** asignados por `super_administrador` o `administrador_sistema` (este último no puede asignarse roles adicionales a sí mismo). El menú **"Mis roles"** permite cambiar el **rol activo**: dashboard, sidebar y acciones pasan a ser los del rol elegido — nunca una mezcla de ambos.

- Cada grupo de rutas por rol valida `active_role:<rol>` además del `role:<rol>` de Spatie: acceder por URL directa a un módulo de un rol asignado pero **no activo** devuelve 403.
- `training_center_id` sigue siendo una sola columna por usuario, válida para todos sus roles "ligados a centro" simultáneamente — no existe una tabla de centro por rol.
- El grupo de catálogos compartidos (`admin.` en `routes/web.php`: departamentos, ciudades, centros, cargos, tipos de vinculación, programas de formación, líneas de investigación/tecnológicas, áreas temáticas, tipologías Minciencias, asesores externos) **no** tiene aislamiento por rol activo — es un hueco de autorización documentado y pendiente (`BUG-20260813-057`).

---

## ROL 1: SUPER ADMINISTRADOR

### Descripción del rol
Máxima autoridad del sistema. Visibilidad global sobre todos los centros y usuarios, sin restricción por `training_center_id`.

### Módulos accesibles

#### 1.1 Dashboard Global
Métricas del sistema completo, no filtrado por centro.

#### 1.2 Vinculación Admin ↔ Centro
Asigna un `administrador_sistema` a cada centro de formación (`LinkAdminTrainingCenterController`).

#### 1.3 Administradores del Sistema
CRUD completo de cuentas `administrador_sistema` (`AdminUsuarioController`): crear, listar, editar, activar/desactivar, eliminar. Puede asignar roles adicionales a estas cuentas.

#### 1.4 Usuarios del Sistema (cualquier otro rol)
CRUD de cualquier usuario que **no** sea `super_administrador` ni `administrador_sistema` (`UsuarioSistemaController`): puede crear directamente Directores de Semilleros, Líderes de Semillero, Líderes de Proyecto o Co-investigadores, saltándose la jerarquía normal de creación. Incluye asignación de roles adicionales.

### Estado del módulo
| Módulo | Implementado | Completo |
|--------|-------------|---------|
| Dashboard global | ✅ | ✅ |
| Vinculación admin-centro | ✅ | ✅ |
| CRUD Administradores del Sistema | ✅ | ✅ (creación + edición) |
| CRUD Usuarios del Sistema (otros roles) | ✅ | ✅ (creación + edición) |
| Gestión de centros de formación | ⚠️ | Vía catálogo compartido `admin.training-centers`, no exclusivo del módulo Super Admin |

---

## ROL 2: ADMINISTRADOR DEL SISTEMA

### Descripción del rol
Gestiona un centro de formación específico (`administrador_sistema` está en `CENTRO_BOUND_ROLE_NAMES`, requiere `training_center_id` obligatorio). Ve y administra solo los datos de su centro.

### Módulos accesibles

#### 2.1 Dashboard del Centro
Usuarios, semilleros y métricas del propio centro.

#### 2.2 Gestión de Usuarios
- **Listado** de solo lectura, filtrable por rol, con edición/activación/desactivación/eliminación (`UsuarioController@index`).
- **Creación exclusiva por rol fijo** — no existe un formulario genérico "crear usuario con cualquier rol":
  - `admin/director-semilleros/crear` → crea un `director_semilleros`.
  - `admin/co-investigadores/crear` → crea un `co_investigador` (rol global, sin centro obligatorio salvo que reciba un rol adicional que sí lo exija).
- Checkbox **"¿Este usuario tiene más roles?"** en ambos formularios, para asignar roles adicionales en la misma transacción.
- Los `co_investigador` de otros centros con un rol global adicional (p. ej. otro `co_investigador`) también son visibles cuando corresponde — `TrainingCenterAccess::scopeUserQueryForList()` siempre incluye los roles globales.

#### 2.3 Semilleros (solo lectura)
Lista los semilleros del centro; el detalle muestra líder de semillero, cada proyecto con su líder de proyecto, aprendices y co-investigadores vinculados. Sin edición ni eliminación.

#### 2.4 Productos Minciencias
Aprueba/rechaza los productos personales de los `co_investigador` que eligieron este centro para su revisión (`MincienciasProductoController`), con visibilidad estrictamente acotada al propio centro.

#### 2.5 Catálogos del Sistema
| Catálogo | Gestión |
|----------|---------|
| Cargo / Posición (`entity_positions`) | "Catálogos simples" |
| Tipo de Vinculación (`linkage_types`) — Planta / Contratista / Otros | "Catálogos simples" |
| Modalidad de proyecto (`project_modalities`) | "Catálogos simples" |
| Tipo de investigación (`investigation_types`) | "Catálogos simples" |
| Línea tecnológica (`technological_lines`) | "Catálogos simples" |
| Área temática (`thematic_areas`) | "Catálogos simples" |
| Departamentos, ciudades, centros de formación, programas de formación, líneas de investigación, tipologías/subcategorías Minciencias | Recursos CRUD independientes, compartidos en `routes/web.php` (no exclusivos de este rol, ver nota de autorización abierta) |
| Asesores externos | Registro sin cuenta del sistema |

> El modelo genérico `Catalogo` (tabla `catalogos`, tipos de ejemplo "Área de Conocimiento", "Tipo de Proyecto", etc.) sigue existiendo en el código y tiene CRUD propio (`admin.catalogos.*`), pero no está referenciado por ninguna otra tabla del sistema — parece ser un remanente sin uso funcional real, pendiente de confirmar si debe eliminarse.

#### 2.6 Reportes del Centro
Exportación de datos del centro (`ReporteController`).

### Estado del módulo
| Módulo | Implementado | Completo |
|--------|-------------|---------|
| Dashboard | ✅ | ✅ |
| Usuarios (listar/crear por rol fijo/editar) | ✅ | ✅ |
| Semilleros (solo lectura) | ✅ | ✅ |
| Productos Minciencias (aprobación) | ✅ | ✅ |
| Catálogos | ✅ | ✅ (con el remanente sin uso anotado arriba) |
| Asesores externos | ✅ | Parcial |
| Reportes | ✅ | ✅ |

---

## ROL 3: DIRECTOR DE SEMILLEROS

### Descripción del rol
Coordina todos los semilleros de su centro de formación. Crea semilleros, asigna líderes de semillero, gestiona documentación institucional, aprueba la etapa final de producto y genera reportes.

### Módulos accesibles

#### 3.1 Dashboard de Semilleros
Total semilleros activos/inactivos, líderes, aprendices y proyectos del centro.

#### 3.2 Gestión de Semilleros
- **Crear**: nombre, código (alfanumérico, hasta 50 caracteres — antes era solo entero), descripción.
- **Editar**, **toggle estado** (activar/desactivar — ya no hay borrado real desde el menú de acciones, corregido en `BUG-20260813-013`).
- **Ver detalle**: página única con secciones apiladas (proyectos con buscador, líder, integrantes, co-investigadores, evidencias, estado del producto final).

#### 3.3 Gestión de Líderes de Semillero
Crear, editar, activar/desactivar, enviar credenciales por email (`LiderCredentialsMail`).

#### 3.4 Vinculación Semillero ↔ Líder
Reasignar qué líder está a cargo de cada semillero (`VinculacionSemilleroLiderController`).

#### 3.5 Documentos Institucionales
Subir/listar/eliminar documentos del semillero, con verificación de que el semillero pertenece al centro del director (`DocumentoSemilleroController::ensureDelCentro()`, `BUG-20260813-032`).

#### 3.6 Revisión de Productos — 2ª etapa
Aprueba/rechaza la **etapa final** de evidencias tipo "producto final", después de que el líder de semillero ya aprobó la 1ª etapa (`RevisionProductoController`). No interviene en Formulación ni Ejecución.

#### 3.7 Reportes de Semilleros
Resumen, aprendices por semillero, proyectos por estado, exportación PDF/Excel.

### Estado del módulo
| Módulo | Implementado | Completo |
|--------|-------------|---------|
| Dashboard | ✅ | ✅ |
| Gestión de semilleros | ✅ | ✅ |
| Gestión de líderes | ✅ | ✅ |
| Vinculaciones | ✅ | ✅ |
| Documentos | ✅ | ✅ |
| Revisión de producto (2ª etapa) | ✅ | ✅ |
| Reportes | ✅ | ✅ |

---

## ROL 4: LÍDER DE SEMILLERO

### Descripción del rol
Coordina un semillero específico. Crea los líderes de proyecto de su semillero, revisa el avance de los proyectos y aprueba/rechaza evidencias en 3 de los 4 tipos posibles.

### Módulos accesibles

#### 4.1 Dashboard del Semillero
Resumen propio, con **notificación tipo "punto rojo"** cuando hay novedades en productos que ya había aprobado y el director acaba de mover.

#### 4.2 Información del Semillero
Vista de solo lectura — no puede editar datos maestros (eso lo hace el director).

#### 4.3 Gestión de Líderes de Proyecto
Crear, listar, activar/desactivar cuentas `lider_proyecto` para los proyectos de su semillero.

#### 4.4 Proyectos (consulta)
Listado con buscador; cada proyecto abre una página de detalle de solo lectura (descripción, integrantes, avance).

#### 4.5 Productos — agrupados por proyecto
Página en formato **acordeón** (solo los proyectos con al menos una evidencia aparecen, expandidos por defecto si tienen pendientes). Por cada evidencia:
- **Formulación** (30% del avance) — aprobación de **1 sola etapa**.
- **Ejecución** (50% del avance) — aprobación de **1 sola etapa**.
- **Producto Final** (20% del avance) — **1ª etapa** de un flujo de 2 (la 2ª la hace el Director de Semilleros).
- **Evidencia de desarrollo** — informativa, sin aprobación, no se muestra en esta página.

#### 4.6 Archivos del Semillero
Solo **descarga** (la opción "Ver en el navegador" fue eliminada en todo el sistema, `BUG-20260813-030`).

#### 4.7 Documentación Interna
Subir/listar/eliminar documentos internos del semillero.

#### 4.8 Reportes
Exportación PDF/Excel del semillero.

### Estado del módulo
| Módulo | Implementado | Completo |
|--------|-------------|---------|
| Dashboard | ✅ | ✅ |
| Info semillero | ✅ | ✅ |
| Líderes de proyecto | ✅ | ✅ |
| Proyectos (consulta) | ✅ | ✅ |
| Productos (avance real por fases) | ✅ | ✅ |
| Archivos | ✅ | ✅ |
| Documentación interna | ✅ | ✅ |

---

## ROL 5: LÍDER DE PROYECTO

### Descripción del rol
Ejecuta un proyecto específico dentro de un semillero. Un usuario solo puede liderar **un** proyecto a la vez (índice único en `projects.lider_proyecto_user_id`).

### Módulos accesibles

#### 5.1 Dashboard
Resumen del proyecto + notificación "punto rojo" cuando el líder de semillero o el director actuaron sobre una evidencia.

#### 5.2 Aprendices
Registro de **datos libres** (no crea cuentas de usuario): nombre completo, número de documento, teléfono, email y **Programa de Formación** (catálogo real `training_programs`, reemplazó al campo de texto libre "Nombre de tecnólogo").

#### 5.3 Co-investigadores
Vincula/desvincula usuarios existentes con rol `co_investigador` (rol global — pueden ser de cualquier centro) al proyecto. Muestra el listado completo de disponibles por defecto.

#### 5.4 Evidencias
Sube evidencia eligiendo uno de 4 tipos, en este orden en el formulario:
1. Evidencia de investigación y/o desarrollo (sin aprobación).
2. Formulación (30%).
3. Ejecución (50%).
4. Producto final (20% — flujo de 2 etapas).

Ve el estado combinado de revisión del producto final en un solo texto (pendiente / aprobado-falta-director / rechazado-por-líder / rechazado-por-director / aprobado-definitivo).

#### 5.5 Reportes
Exportación del proyecto propio.

### Estado del módulo
| Módulo | Implementado | Completo |
|--------|-------------|---------|
| Dashboard | ✅ | ✅ |
| Aprendices | ✅ | ✅ |
| Co-investigadores | ✅ | ✅ |
| Evidencias (4 tipos) | ✅ | ✅ |
| Reportes | ✅ | ✅ |

---

## ROL 6: CO-INVESTIGADOR

### Descripción del rol
**Único rol global del sistema** — no requiere `training_center_id`. Puede estar vinculado como co-investigador en proyectos de varios semilleros/centros a la vez, y gestiona sus propios productos Minciencias de forma completamente independiente de cualquier semillero.

### Módulos accesibles

#### 6.1 Dashboard
Resumen de proyectos donde está vinculado + acceso a descarga de reporte.

#### 6.2 Proyectos Vinculados
Desplegable con cada proyecto individual. En el detalle de cada uno:
- Sube evidencia **solo de tipo "desarrollo"** (nunca formulación, ejecución ni producto final).
- Ve el equipo del proyecto (líder de proyecto + aprendices) de solo lectura.
- Ve/comparte evidencias con el líder de proyecto — cada quien elimina solo lo que subió.
- Ve el **producto final** que suba el líder de proyecto y su estado en las 2 etapas de revisión, de solo lectura.
- Cada acción valida la vinculación activa contra `project_authors` (no asume "mi proyecto" único, porque puede tener varios).

#### 6.3 Productos Minciencias (personales)
CRUD de sus propios productos académicos, **sin vincularlos a ningún semillero ni proyecto**: elige el centro de formación que los revisará, adjunta archivos, y ve el estado de revisión (pendiente / en revisión / aprobado / rechazado) con la observación del administrador.

#### 6.4 Reportes
Exportación de su actividad.

### Estado del módulo
| Módulo | Implementado | Completo |
|--------|-------------|---------|
| Dashboard | ✅ | ✅ |
| Proyectos vinculados (detalle) | ✅ | ✅ |
| Productos Minciencias personales | ✅ | ✅ |
| Reportes | ✅ | ✅ |

---

## MÓDULOS TRANSVERSALES

### Autenticación y Sesión
- Login con Livewire.
- Autenticación de dos factores (2FA) vía Fortify, con QR.
- Envío de correo transaccional (credenciales, códigos) vía **Resend** (`resend/resend-laravel`).
- Prevención de back-button con cabeceras no-cache (`PreventBackHistory`).
- Al iniciar sesión se inicializa el **rol activo** en sesión con el rol principal del usuario.

### Configuración de Perfil (todos los roles)
- Editar perfil (nombre, email), cambiar contraseña, configurar 2FA.
- Campo **CVLAC** (link) expuesto en el formulario de perfil (`BUG-20260813-043`).
- Exclusivos para `co_investigador`: **Nivel de Formación** (Técnico/Tecnólogo/Pregrado/Posgrado) y **Fecha de Vinculación** (`BUG-20260813-044`).
- Eliminar cuenta.
- Apariencia (tema claro/oscuro) — sigue sin implementación real.

### Sistema de rol activo (multi-rol)
Ver sección [Sistema de rol activo](#sistema-de-rol-activo-multi-rol) más arriba.

### Scoping por Centro (automático)
Todo dato se filtra automáticamente por `training_center_id` del usuario autenticado, centralizado en `app/Support/TrainingCenterAccess.php`. El **super administrador** y el **co_investigador** (rol global) son las únicas excepciones — el segundo simplemente nunca requiere centro.

---

## FLUJO DE AVANCE DEL PROYECTO

El avance de un proyecto (que ve el Líder de Semillero) se calcula por **3 fases con peso fijo**, no por fechas:

```
Formulación   — 30% — aprobación de 1 sola etapa (Líder de Semillero)
Ejecución     — 50% — aprobación de 1 sola etapa (Líder de Semillero)
Producto Final — 20% — aprobación de 2 etapas (Líder de Semillero → Director de Semilleros)
```

Combinaciones posibles de avance: 0%, 30%, 80%, 100%.

```
1. Líder de Proyecto sube evidencia (Formulación / Ejecución / Producto Final)
   ↓
2. Líder de Semillero aprueba o rechaza
   ↓ (si es Formulación o Ejecución, termina aquí)
3. Si es Producto Final y el Líder de Semillero aprobó:
   → Director de Semilleros aprueba o rechaza (etapa final)
```

La **evidencia de desarrollo** (1 de los 4 tipos) no participa de este flujo: es informativa y nunca requiere aprobación.

---

## FLUJO DE PRODUCTOS MINCIENCIAS (independiente)

```
1. Co-investigador registra su producto personal y elige un centro de formación
   ↓
2. Administrador del Sistema de ese centro aprueba o rechaza, con observación
```

Este flujo **no** pasa por ningún semillero, proyecto, líder de semillero ni director de semilleros — es una relación directa co-investigador ↔ administrador del centro elegido.

---

## MODELO DE DATOS SIMPLIFICADO

```
TrainingCenter
    ├── User (administrador_sistema, director_semilleros, lider_semillero, lider_proyecto)
    ├── Seedling                                   [creado por director_semilleros]
    │   └── Project                                [líder de proyecto único]
    │       ├── ProjectLearner                     (aprendices — datos libres)
    │       ├── ProjectAuthor                      (vincula Users co_investigador)
    │       ├── ProjectEvidence                    (desarrollo | formulación | ejecución | producto_final)
    │       └── MacroProject (opcional)
    └── MincienciasProduct                         [creado y revisado dentro de este centro]

User (co_investigador)                             — rol global, sin training_center_id
    ├── vinculado a Project de cualquier centro (vía ProjectAuthor)
    └── dueño de sus propios MincienciasProduct
```

---

## TABLA RESUMEN DE MÓDULOS POR ROL

| Módulo | Super Admin | Admin | Dir. Semilleros | Líder Semillero | Líder Proyecto | Co-investigador |
|--------|:-----------:|:-----:|:----------------:|:----------------:|:----------------:|:----------------:|
| Dashboard propio | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Centros de formación | ✅ | Catálogo compartido | — | — | — | — |
| Gestión de usuarios | ✅ (todos) | ✅ (crea Dir. Sem. / Co-inv.) | — | — | — | — |
| Roles adicionales (multi-rol) | ✅ | ✅ | — | — | — | — |
| Catálogos | — | ✅ | — | — | — | — |
| Semilleros | — | Solo lectura | ✅ | Solo lectura | — | — |
| Líderes de semillero | — | — | ✅ | — | — | — |
| Líderes de proyecto | — | — | — | ✅ | — | — |
| Aprendices | — | — | — | — | ✅ | — |
| Co-investigadores (vincular) | — | — | — | — | ✅ | — |
| Proyectos (consulta) | — | Solo lectura | — | Solo lectura | ✅ (el propio) | ✅ (vinculados) |
| Evidencias — Formulación/Ejecución/Prod. Final (revisar) | — | — | ✅ (2ª etapa) | ✅ (1ª etapa) | — | — |
| Evidencias — subir | — | — | — | — | ✅ (4 tipos) | ✅ (solo desarrollo) |
| Productos Minciencias (personal, crear) | — | — | — | — | — | ✅ |
| Productos Minciencias (aprobar) | — | ✅ | — | — | — | — |
| Documentos del semillero | — | — | ✅ | ✅ (interna) | — | — |
| Archivos del semillero | — | — | — | ✅ (solo descarga) | — | — |
| Reportes | — | ✅ | ✅ | ✅ | ✅ | ✅ |
| Configurar 2FA | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
