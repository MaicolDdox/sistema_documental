# Sesión de trabajo — Depuración y refinamiento por rol (rediseño de roles)
## Sistema Documental SENA GIDESTH

**Rama:** `feature/aldana_refactoring`
**Rango de IDs de bug:** BUG-20260813-001 a BUG-20260813-027
**Estado final de la suite:** 165/165 tests pasando (PHPUnit + SQLite en memoria)
**Convención seguida:** cada cambio de comportamiento quedó tipificado con un ID de bug secuencial y su propio test de regresión en `tests/Feature/Regression/`.

---

## ÍNDICE

1. [Resumen ejecutivo](#resumen-ejecutivo)
2. [Preparación de datos de prueba](#preparación-de-datos-de-prueba)
3. [Administrador del Sistema](#administrador-del-sistema)
4. [Super Administrador](#super-administrador)
5. [Director de Semilleros](#director-de-semilleros)
6. [Líder de Semillero](#líder-de-semillero)
7. [Líder de Proyecto](#líder-de-proyecto)
8. [Co-investigador](#co-investigador)
9. [Cambios transversales](#cambios-transversales)
10. [Tabla completa de bugs (BUG-001 a BUG-027)](#tabla-completa-de-bugs)
11. [Archivos nuevos creados en la sesión](#archivos-nuevos-creados-en-la-sesión)
12. [Pendientes / hallazgos sin resolver](#pendientes--hallazgos-sin-resolver)

---

## RESUMEN EJECUTIVO

Esta sesión auditó y corrigió, **rol por rol**, el sistema completo tras el rediseño de roles (6 roles activos: `super_administrador`, `administrador_sistema`, `director_semilleros`, `lider_semillero`, `lider_proyecto`, `co_investigador`). El trabajo cubrió:

- **Bugs de código heredado** de la limpieza de fases anteriores (referencias a modelos/tablas eliminados: `Product`, `ResearchGroup`, `project_seedlings`).
- **Bugs de UX/flujo** reportados directamente por el usuario probando cada rol manualmente (formularios que fallan en silencio, botones mal etiquetados, iframes que no cierran, filtros que no traen datos).
- **Fusiones y reorganizaciones de sidebar** para eliminar ítems duplicados o redundantes en cada rol.
- **Construcción de funcionalidad nueva**: rol Co-investigador completo (dashboard, detalle de proyecto, subida de evidencias), ítem "Semilleros" de solo lectura para Administrador del Sistema, y un sistema de notificación tipo "punto rojo" (estilo WhatsApp) entre Líder de Proyecto ↔ Líder de Semillero ↔ Director de Semilleros sobre el estado de revisión del producto final.
- **Depuración de datos**: se vació la base de datos operativa (usuarios, semilleros, proyectos) manteniendo los catálogos, para permitir probar el flujo completo desde cero; luego se sembraron catálogos vacíos que bloqueaban la creación de proyectos.

Cada corrección quedó respaldada por un test PHPUnit en `tests/Feature/Regression/BUG20260813{ID}Test.php`, con docblock explicando causa raíz y solución.

---

## PREPARACIÓN DE DATOS DE PRUEBA

Al inicio de la sesión se limpió la base de datos para poder probar el flujo completo desde cero:

- Se eliminaron todos los usuarios excepto el súper administrador, y todos los registros operativos (semilleros, proyectos, evidencias, etc.).
- Se **preservaron los catálogos** (`research_lines`, `technological_lines`, `thematic_areas`, `project_modalities`, `investigation_types`) por instrucción explícita del usuario — pero estaban completamente vacíos, lo cual bloqueaba silenciosamente la creación de cualquier proyecto (`research_line_id` es obligatorio). Se sembraron manualmente vía `tinker` con datos de prueba (5 líneas de investigación, 5 líneas tecnológicas, 5 áreas temáticas, 3 modalidades, 4 tipos de investigación).
- Se crearon usuarios de prueba para cada uno de los 6 roles y, en dos ocasiones posteriores, se reseteó la contraseña de **todos** los usuarios del sistema a un único valor común para facilitar las pruebas manuales por rol. La contraseña usada se comunicó directamente en el chat (no se registra en este documento por buena práctica de seguridad, dado que este archivo puede terminar versionado en git).
- **Bug propio detectado durante ese proceso:** un primer intento de reseteo masivo usó `User::query()->update(['password' => $pw])`, que **no pasa por el cast `hashed`** del modelo (los `Builder::update()` masivos no instancian el modelo ni aplican mutadores/casts) — la contraseña quedó guardada en **texto plano** en la base de datos por un momento. Se detectó inmediatamente (`getRawOriginal('password')`) y se corrigió re-hasheando con `Hash::make()` antes de que se usara en ningún flujo real. Ver [Pendientes](#pendientes--hallazgos-sin-resolver) para la lección aprendida.

---

## ADMINISTRADOR DEL SISTEMA

| Bug | Resumen |
|---|---|
| BUG-003 | Se corrigieron 5 problemas: usuario viéndose a sí mismo en sus propios listados; formulario de creación ofreciendo cualquier rol (incluido `super_administrador`); páginas redundantes "Asignar Roles"/"Usuarios con rol" eliminadas; sidebar "Asesores Externos" → "Co-investigadores"; widget "Gestionar roles" por fila eliminado. |
| BUG-024 | **Reestructuración de "Gestión de Usuarios"**: "Usuarios" pasa a ser de solo lectura y filtrable por rol (ya no crea usuarios). Se crearon dos ítems de sidebar nuevos y exclusivos: **"Director de Semilleros"** y **"Co-investigadores"**, cada uno con su propio formulario de creación con el rol **fijo en el controlador** (nunca leído del `request`, para que no se puedan mezclar). Rutas `admin.usuarios.create/store` eliminadas por completo. |
| BUG-024 (bug de fondo) | `TrainingCenterAccess::scopeUserQueryForList()` filtraba **todos** los usuarios por `training_center_id`, incluido `co_investigador` — pero ese rol es global y nunca tiene centro asignado, así que un administrador **nunca veía ningún co-investigador** en su listado. Corregido: los `co_investigador` se listan siempre, además de los usuarios del centro propio del actor. |
| BUG-025 | **Nuevo ítem "Semilleros"** (solo lectura): lista los semilleros del centro del administrador y, en el detalle, muestra líder de semillero, cada proyecto con su líder de proyecto, aprendices e co-investigadores vinculados. Sin edición ni eliminación — se respeta la regla de que solo quien crea un registro puede gestionarlo. Los permisos `semilleros.listar` / `semilleros.ver_detalle` ya estaban seedeados para este rol en `RolesAndPermissionsSeeder` pero nunca se habían implementado controlador/vista/ruta. |

**Archivos clave:** `app/Http/Controllers/Admin/UsuarioController.php`, `app/Http/Controllers/Admin/SemilleroController.php` (nuevo), `app/Support/TrainingCenterAccess.php`, `routes/admin.php`, `resources/views/admin/director_semilleros/create.blade.php` (nuevo), `resources/views/admin/coinvestigadores/create.blade.php` (nuevo), `resources/views/admin/semilleros/{index,show}.blade.php` (nuevos).

---

## SUPER ADMINISTRADOR

| Bug | Resumen |
|---|---|
| BUG-022 | Se quitó la card "Accesos rápidos" del dashboard (vincular centro, panel admin, gestión de usuarios, centros, datos paramétricos) — el dashboard queda únicamente como resumen. Se aplanó el grid de 3 columnas a una sola (era la única card de la columna derecha). |

**Archivo:** `resources/views/super-admin/dashboard.blade.php`.

---

## DIRECTOR DE SEMILLEROS

| Bug | Resumen |
|---|---|
| BUG-008 | Dashboard sin "Acciones Rápidas"; `SemilleroController::edit()` llamaba a `Builder::orWhereKey()` (método inexistente) → `BadMethodCallException` al abrir "Editar Semillero"; modal "Nuevo Líder" sin el campo obligatorio `tipo_documento`, la validación fallaba en silencio. |
| BUG-009 | El modal "Editar semillero" (iframe `?embedded=1`) redirigía siempre al listado completo tras guardar, rompiendo el iframe (renderizaba la página completa con sidebar dentro del modal). Se agregó una vista mínima de "ruptura" (`window.top.location`) cuando la petición viene marcada como embebida. También se quitó la opción de subir logo del formulario de editar. |
| BUG-010 | Fusión de los ítems de sidebar "Semilleros" / "Documentos" / "Reportes" en una sola estructura con un desplegable "Semilleros" que lleva al detalle con tabs adicionales. La card del dashboard "Asesores vinculados" pasó a "Co-investigadores asociados" con datos reales. |
| BUG-011 | Refinamiento de BUG-010: el detalle del semillero pasó de tabs a una sola página con scroll y secciones apiladas. La sección "Proyectos" ganó buscador por nombre y al seleccionar uno muestra líder de proyecto, integrantes, co-investigadores, evidencias y estado del producto final. Se eliminaron secciones redundantes o con código muerto ("Co-investigadores" a nivel de semillero, "Productos" hardcodeado a `[]`, "Evidencias" duplicada). |
| BUG-012 | `codigo` de semillero (columna `INT` de MySQL, tope 2147483647) no tenía validación de rango superior — un valor fuera de rango producía un `QueryException` 500 sin control. Se agregó `max:2147483647`. |
| BUG-013 | El botón "Eliminar" del menú de tres puntos apuntaba realmente a `destroy()` (borrado real, sin las validaciones de negocio de `toggleEstado()`) en vez de desactivar. Se corrigió para usar `toggle-estado` con texto/modal dinámico ("Desactivar"/"Activar" según estado). **Se advirtió al usuario que revisara su listado de semilleros por si algún borrado real ya había ocurrido antes del fix.** |
| BUG-023 | Se quitaron 2 cards del dashboard sin ninguna función real ("Resumen gráfico del módulo" y "Estado de semilleros", ambas con gráficos Chart.js), junto con el `<script>` asociado y las variables de datos de gráfico ya muertas en el controlador. |

**Archivos clave:** `app/Http/Controllers/DirectorSemilleros/{DashboardController,SemilleroController,RevisionProductoController}.php`, vistas en `resources/views/director_semilleros/`.

---

## LÍDER DE SEMILLERO

| Bug | Resumen |
|---|---|
| BUG-001 | La limpieza de la fase anterior (eliminación de `ResearchGroup`/`Product`/`project_seedlings`) dejó el sidebar y varios controladores con referencias colgantes a modelos/tablas eliminados — error fatal en cualquier página de este rol. |
| BUG-002 | `ReporteSemilleroController::buildDatosReporte()` roto por las mismas referencias eliminadas (`$s->researchGroup`, relación `seedlings` inexistente, `Seedling::members()` para "aprendices" cuando ahora son datos libres vía `ProjectLearner`). |
| BUG-014 | Dashboard sin "Acciones Rápidas"; eliminada la visualización del logo del semillero en **todas** las vistas del sistema (ya no se puede subir desde ningún rol); eliminado por completo el ítem "Integrantes" del sidebar (sin funcionalidad real, operaba sobre un concepto obsoleto). |
| BUG-015 | El ítem "Proyectos" del sidebar pasó de link directo a desplegable que lista los proyectos del semillero; cada uno lleva a una página de detalle con 3 cards (descripción, integrantes, avances). |
| BUG-016 | **Bug de datos:** `ProyectoLiderService::crearProyecto()` vinculaba automáticamente al Líder de Proyecto como su propio co-investigador (código heredado de antes del rediseño) — aparecía duplicado en todas las vistas de "co-investigadores vinculados" construidas en la sesión. Se quitó el `ProjectAuthor::create()` automático y se limpió la fila afectada existente en base de datos. |

**Archivos clave:** `app/Http/Controllers/LiderSemillero/{DashboardController,ProyectosController,ProductosController}.php`, `app/Services/LiderSemillero/{ProyectoLiderService,RevisionEvidenciaService}.php`.

---

## LÍDER DE PROYECTO

| Bug | Resumen |
|---|---|
| BUG-017 | Auditoría completa del rol. Hallazgo: `projects.lider_proyecto_user_id` no tenía restricción de unicidad — un mismo líder de proyecto podía quedar asignado a 2 proyectos, pero `LiderProyectoContext::miProyecto()` asume exactamente uno (`firstOrFail()`), dejándolo "atrapado" viendo solo uno. Se agregó validación de unicidad al crear/editar proyecto. También se corrigió HTML inválido (un `<form>` dentro de una fila de tabla) usando el atributo `form=""`. |
| BUG-018 | Refinamiento en 4 partes: (1) Dashboard y "Resumen" (páginas duplicadas) se fusionaron — el dashboard ahora tiene resumen del proyecto + cards de solo lectura de aprendices y co-investigadores; (2) Evidencias sin cambios; (3) tabla de aprendices con botones-ícono (lápiz/basura) en vez de texto "Guardar"/"Eliminar"; (4) el listado de co-investigadores "disponibles" para vincular ahora se muestra completo por defecto (antes exigía buscar). |
| BUG-019 | El sidebar tenía dos enlaces a la misma página ("Dashboard" genérico + "Resumen" específico). Se quitó el duplicado. |
| BUG-026 | El estado de revisión del producto final se mostraba como 2 badges separados, obligando a interpretarlos en conjunto. Se agregó un estado combinado explícito en un solo texto (aprobado-falta-director / rechazado-por-líder / rechazado-por-director / aprobado-definitivo / pendiente). |
| BUG-027 | **Sistema de notificación tipo "punto rojo"** (ver sección [Cambios transversales](#cambios-transversales)). |

**Archivos clave:** `app/Http/Controllers/LiderProyecto/{DashboardController,EvidenciaController,CoinvestigadorController,AprendizController}.php`.

---

## CO-INVESTIGADOR

Rol construido casi por completo en esta sesión (partía de solo dashboard + reporte PDF):

| Bug | Resumen |
|---|---|
| BUG-020 | El sidebar tenía un ítem duplicado ("Proyectos Vinculados" apuntaba a la misma página que "Dashboard"). Se conservaron ambos pero con función distinta: "Dashboard" pasó a 2 cards (resumen de proyectos + descarga de reporte) y "Proyectos Vinculados" se convirtió en un desplegable con cada proyecto individual. Se construyó la **página de detalle por proyecto** (`Coinvestigador\ProyectoController`, nuevo): el co-investigador puede subir evidencias tipo **"desarrollo" únicamente** (nunca producto final), ver los actores del proyecto (líder + aprendices) de solo lectura, y ve/comparte evidencias con el líder de proyecto (cada quien solo elimina lo que subió). Como un co-investigador puede estar en varios proyectos a la vez, cada acción valida la vinculación activa contra `project_authors` en vez de asumir "mi proyecto" único. |
| BUG-021 | La página de detalle de proyecto solo mostraba evidencias de desarrollo. Se agregó una card de solo lectura con el **producto final** que suba el líder de proyecto y su estado de revisión en las 2 etapas (líder de semillero / director de semilleros). |

**Archivos clave (nuevos):** `app/Http/Controllers/Coinvestigador/ProyectoController.php`, `resources/views/co_investigador/proyectos/show.blade.php`, `resources/views/co_investigador/dashboard.blade.php` (rediseñada).

---

## CAMBIOS TRANSVERSALES

### Sistema de notificación tipo "punto rojo" (BUG-027)

Pedido explícito del usuario: "algo como las notificaciones de los chats de WhatsApp, un punto rojo en la esquina". Sin campanita, sin historial, sin tabla de notificaciones — aparece mientras hay una novedad sin ver y desaparece apenas se visita la página que refleja el estado actual. Diseñado **sin Events/Listeners/Jobs/Observers** (regla explícita del proyecto en `CLAUDE.md`):

- **Migración** `2026_08_14_004946_add_notificacion_visto_to_project_evidences_table.php`: agrega `visto_por_lider_proyecto_at` y `visto_por_lider_semillero_at` (timestamps nullable) a `project_evidences`.
- **`RevisionEvidenciaService`**: cada aprobación/rechazo resetea a `null` la columna del rol que **no** actuó (así se marca "hay algo sin ver" para esa persona) y marca `now()` en la columna de quien sí actuó.
- **Al subir evidencia** (`LiderProyecto\EvidenciaController::store()`): se marca como vista por el propio líder de proyecto de inmediato (no hay novedad que notificarse a sí mismo).
- **Al visitar la página relevante** (`LiderProyecto\DashboardController::index()`, `LiderSemillero\ProductosController::index()`): se marca `now()` en la columna correspondiente — el punto rojo desaparece en esa misma carga.
- **Sidebar** (`resources/views/components/app-layout.blade.php`): pinta un punto rojo absoluto sobre el ícono de "Dashboard" (líder de proyecto) y de "Productos" (líder de semillero, solo cuando el director ya actuó sobre algo que ese líder había aprobado — cuidadosamente separado del badge numérico existente de "pendientes por revisar por primera vez", para no confundir ambos conceptos).

### Bug adicional descubierto durante las pruebas: `FIELD()` no portable

Al escribir el test de BUG-027 se descubrió que `LiderSemillero\ProductosController` y `DirectorSemilleros\RevisionProductoController` ordenaban resultados con `orderByRaw("FIELD(...) DESC")`, función **exclusiva de MySQL**. Bajo SQLite (motor de tests, ver `phpunit.xml`) esto rompía con `SQLSTATE[HY000]: no such function: FIELD`. Nunca se había cubierto con un test antes, por lo que pasó inadvertido. Corregido con un `CASE WHEN` portable entre ambos motores.

---

## TABLA COMPLETA DE BUGS

| ID | Rol / Área | Categoría | Test de regresión |
|---|---|---|---|
| BUG-20260813-001 | Líder de Semillero | D — Base de datos (referencias colgantes) | `BUG20260813001Test.php` |
| BUG-20260813-002 | Líder de Semillero | A — Lógica (reportes) | `BUG20260813002Test.php` |
| BUG-20260813-003 | Administrador del Sistema | B/F — Autorización y UI | `BUG20260813003Test.php` |
| BUG-20260813-008 | Director de Semilleros | A/D/F | `BUG20260813008Test.php` |
| BUG-20260813-009 | Director de Semilleros | C — UI (iframe) | `BUG20260813009Test.php` |
| BUG-20260813-010 | Director de Semilleros | F — Vista/Blade (fusión sidebar) | `BUG20260813010Test.php` |
| BUG-20260813-011 | Director de Semilleros | F — Vista/Blade (refinamiento) | `BUG20260813011Test.php` |
| BUG-20260813-012 | Director de Semilleros | E/D — Validación | `BUG20260813012Test.php` |
| BUG-20260813-013 | Director de Semilleros | A — Lógica (botón mal wireado) | `BUG20260813013Test.php` |
| BUG-20260813-014 | Líder de Semillero | F — Vista/Blade | `BUG20260813014Test.php` |
| BUG-20260813-015 | Líder de Semillero | F — Vista/Blade | `BUG20260813015Test.php` |
| BUG-20260813-016 | Líder de Semillero | D — Base de datos (dato corrupto) | `BUG20260813016Test.php` |
| BUG-20260813-017 | Líder de Proyecto | D/E — Unicidad | `BUG20260813017Test.php` |
| BUG-20260813-018 | Líder de Proyecto | F — Vista/Blade (4 partes) | `BUG20260813018Test.php` |
| BUG-20260813-019 | Líder de Proyecto | F — Vista/Blade (sidebar duplicado) | `BUG20260813019Test.php` |
| BUG-20260813-020 | Co-investigador | A — Feature nueva | `BUG20260813020Test.php` |
| BUG-20260813-021 | Co-investigador | F — Vista/Blade | `BUG20260813021Test.php` |
| BUG-20260813-022 | Super Administrador | F — Vista/Blade | `BUG20260813022Test.php` |
| BUG-20260813-023 | Director de Semilleros | F — Vista/Blade | `BUG20260813023Test.php` |
| BUG-20260813-024 | Administrador del Sistema | B/F — Autorización y UI | `BUG20260813024Test.php` |
| BUG-20260813-025 | Administrador del Sistema | A — Feature nueva | `BUG20260813025Test.php` |
| BUG-20260813-026 | Líder de Proyecto | F — Vista/Blade | `BUG20260813026Test.php` |
| BUG-20260813-027 | Líder de Proyecto / Líder de Semillero | A — Feature nueva | `BUG20260813027Test.php` |

> Los IDs 004–007 no tienen test de regresión dedicado (correspondían a pasos de depuración de datos, no a cambios de código).

---

## ARCHIVOS NUEVOS CREADOS EN LA SESIÓN

**Controladores:**
- `app/Http/Controllers/Admin/SemilleroController.php`
- `app/Http/Controllers/Coinvestigador/ProyectoController.php`

**Vistas:**
- `resources/views/admin/director_semilleros/create.blade.php`
- `resources/views/admin/coinvestigadores/create.blade.php`
- `resources/views/admin/semilleros/index.blade.php`
- `resources/views/admin/semilleros/show.blade.php`
- `resources/views/co_investigador/proyectos/show.blade.php`
- `resources/views/lider_proyecto/proyectos/show.blade.php`
- `resources/views/lider_semillero/proyectos/show.blade.php`
- `resources/views/director_semilleros/semilleros/embedded-redirect.blade.php`

**Migraciones:**
- `database/migrations/2026_08_14_004946_add_notificacion_visto_to_project_evidences_table.php`

**Tests de regresión:** los 23 archivos listados en la tabla anterior, todos en `tests/Feature/Regression/`.

---

## PENDIENTES / HALLAZGOS SIN RESOLVER

- **Cuentas de prueba con datos basura:** en la última revisión de usuarios aparecían varias cuentas con emails inválidos (`@jerneor`, `@gamilc.om`, etc.) creadas manualmente al probar formularios — no se limpiaron, quedan para una futura sesión de depuración de datos si se desea.
- **Usuario sin perfil `Person`:** el usuario `hills.mathias@example.com` (id 21, rol `administrador_sistema`) no tiene registro `person` asociado — puede fallar en vistas que muestren el nombre completo.
- **Lección de seguridad:** un reseteo masivo de contraseñas con `Model::query()->update([...])` **no aplica el cast `hashed`** (los `Builder::update()` no pasan por mutadores de instancia). Cualquier reseteo masivo de contraseñas debe usar `Hash::make()` explícitamente antes del `update()`, o iterar instancias con `->save()`.
- **`FIELD()` no portable** ya corregido en los dos controladores de revisión de productos (ver arriba) — vale la pena revisar si queda algún otro `orderByRaw` con sintaxis específica de MySQL en el resto del código, dado que el hallazgo fue accidental (solo se detectó porque un test nuevo finalmente ejercitó esa ruta).
