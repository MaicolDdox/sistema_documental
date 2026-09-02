# Changelog — Sesión de reformación GIDESTH (agosto 2026)

Rama: `fix/superadmin-eliminar-usuarios-y-roles-muertos` (sin commitear al cierre de esta sesión).

Todos los cambios de esta sesión se tratan como **bugs/reformas con ID secuencial** (`BUG-20260813-NNN`), cada uno con diagnóstico, implementación y un test de regresión propio en `tests/Feature/Regression/BUG20260813{ID}Test.php`. Al cierre de la sesión: **253/253 tests pasando**.

---

## BUG-028 — Reemplazo de listas de catálogo

Reemplazo completo de los valores de: líneas de investigación, líneas tecnológicas, áreas temáticas, modalidad de proyecto. En "tipo de investigación" solo se agregó el ítem **Innovación** (sin tocar los existentes).

**Archivos:** seeders de `ResearchLine`, `TechnologicalLine`, `ThematicArea`, `ProjectModality`, `InvestigationType`.

---

## BUG-029 — Flujo de aprobación de productos Minciencias

Nuevo flujo: el co-investigador selecciona un `training_center_id` al crear un producto Minciencias; el `administrador_sistema` de ese centro aprueba/rechaza, con visibilidad estrictamente acotada al centro.

**Archivos:**
- Migración `create_minciencias_products_table` + `create_minciencias_product_files_table` + `add_revision_and_training_center_to_minciencias_products_table`.
- Nuevo `Admin/MincienciasProductoController`, nuevo módulo de permisos `minciencias`.
- Campo de centro de formación en el formulario de creación del co-investigador.

---

## BUG-030 — Visualización/descarga de archivos, luego eliminación de "Ver en el navegador"

Diagnóstico inicial: symlink `public/storage` faltante + implementación inconsistente en 9 módulos. Decisión final del usuario: **eliminar completamente la opción "Ver en el navegador"** en los 11 puntos del sistema, dejando solo "Descargar", estandarizado sobre `App\Concerns\StreamsPublicStorageFiles`.

**Test:** `BUG20260813030Test.php` — verifica que las 11 rutas `.ver` ya no existen y que "Descargar" responde `attachment` con extensión correcta en cada rol.

---

## BUG-031 / BUG-038 — `administrador_sistema` sin `training_center_id`

Causa raíz común de dos síntomas: el rol `administrador_sistema` podía crearse sin centro asignado.

**Archivos:**
- `app/Support/TrainingCenterAccess.php` — `administrador_sistema` agregado a `CENTRO_BOUND_ROLE_NAMES`.
- `app/Services/Admin/UserCreationService.php` — guard en `crearUsuario()` que lanza `ValidationException` si el rol requiere centro y no se envió.

**Nota histórica:** la migración de este bug originalmente usó `dropConstrainedForeignId()` + recreate para volver nullable `minciencias_products.training_center_id`, lo que borró datos reales de 2 productos existentes (sin backup disponible). Corregido en memoria del proyecto: `Blueprint::change()` funciona nativamente en este Laravel sin `doctrine/dbal`, así que ese drop+recreate no era necesario.

---

## BUG-032 — Director subía documentos a semilleros de otro centro

`DocumentoSemilleroController::store()` no validaba que el semillero perteneciera al centro del director.

**Fix:** nuevo método `ensureDelCentro()` invocado antes de guardar.

---

## BUG-033 — Cuentas "huérfanas" no gestionables

`UserOwnershipAccess::canManage()` bloqueaba la gestión de cuentas creadas antes de que existiera `created_by_user_id` (sin backfill).

**Fix:** `canManage()` retorna `true` cuando `created_by_user_id === null`, documentado como seguro porque cada caller ya filtra por centro/rol antes de llamarlo.

---

## BUG-034 — `UserEdit.php` forzaba centro a roles que no lo requieren

El componente Livewire asignaba el centro del admin editor a **cualquier** usuario editado, incluyendo `co_investigador` (que debe quedar sin centro).

**Fix:** `update()` solo asigna `training_center_id` cuando `TrainingCenterAccess::roleRequiresTrainingCenter($this->role)` es verdadero; si no, lo pone en `null` explícitamente.

---

## BUG-035 — Conteos de dashboard excluían al propio usuario

`scopeUserQueryForList()` excluye al usuario que consulta — correcto para listados de "gestionar a otros", pero incorrecto quando se usaba también para conteos/reportes.

**Fix:** nuevo `TrainingCenterAccess::scopeUserQueryForMetrics()` que no excluye al usuario, para usar en dashboards/reportes.

---

## BUG-036 — Falta de índice único en `lider_proyecto_user_id`

Solo existía validación a nivel de aplicación; riesgo de condición de carrera permitiendo dos proyectos con el mismo líder.

**Fix:** migración `2026_08_28_010000_add_unique_index_to_lider_proyecto_user_id_in_projects_table.php` con `$table->unique('lider_proyecto_user_id')`.

---

## BUG-037 — `CoinvestigadorController::destroy()` reportaba éxito sin desvincular

No verificaba el número de filas afectadas por `updateExistingPivot()`; con 0 filas igual mostraba éxito.

**Fix:** se chequea el retorno entero de `updateExistingPivot()` y se redirige con `error` si es 0.

---

## BUG-039 — `'co_investigador'` hardcodeado como "el" rol global

`TrainingCenterAccess` asumía que `co_investigador` era el único rol global, en vez de derivarlo de `CENTRO_BOUND_ROLE_NAMES`.

**Fix:** nuevo método privado `globalRoleNames()` que usa `Role::whereNotIn(...)` para derivar los roles globales dinámicamente.

---

## BUG-040 — Catálogo "Tipo de Vinculación"

Reemplazo completo de `linkage_types` por exactamente: **Planta, Contratista, Otros** (conservando la fila "Planta" existente por estar vinculada a un usuario real).

**Fix colateral:** typo de columna `descripccion` → `descripcion` corregido en el seeder.

---

## BUG-041 — Eliminación completa de "Áreas del Conocimiento"

Eliminación total (backend y frontend) de "Áreas del Conocimiento" y "Grandes Áreas de Conocimiento": rutas, controladores, modelos, vistas, seeder, enlaces del sidebar y las tablas `knowledge_areas`/`knowledge_grand_areas` (migración `2026_08_28_020000_drop_knowledge_areas_and_knowledge_grand_areas_tables.php`).

**Test:** `BUG20260813041Test.php` — confirma que rutas, tablas y modelos ya no existen, y que el sidebar de admin no muestra ningún enlace relacionado.

---

## BUG-042 — Eliminación completa de "Ficha" y "Jornada"

En el formulario "crear programa de formación": eliminación total de la opción "Ficha" (que auto-creaba filas de catálogo `TrainingRecord`) y de "Jornada" (`JornadaEnum`), incluyendo columnas, modelo, controlador, rutas y enlace del sidebar.

**Migración:** `2026_08_28_030000_drop_ficha_and_jornada_from_training_programs.php`.

**Test:** `BUG20260813042Test.php`.

---

## BUG-043 — Campo CVLAC en el perfil

En el formulario de actualizar perfil (vista única compartida entre roles): se agregó el campo **CVLAC** (columna `cvlac_link` ya existía, solo se expuso en el formulario) y se removió "Programa de Formación" **únicamente de este formulario** (el catálogo en sí permanece intacto en el resto del sistema).

**Test:** `BUG20260813043Test.php`.

---

## BUG-044 — "Nivel de Formación", "Fecha de Vinculación" y catálogo "Cargo/Posición"

En el formulario de perfil, exclusivos para `co_investigador`:
- **Nivel de Formación** (Técnico/Tecnólogo/Pregrado/Posgrado — nuevo `NivelFormacionEnum`).
- **Fecha de Vinculación** (date picker).

Además, el campo "Cargo / Posición" (`entity_position_id`, ya dinámico pero no editable desde la interfaz) se hizo gestionable vía **Catálogos Simples**, sembrado con 13 valores institucionales (se conservó la fila "Administrador" por estar vinculada al perfil del super admin vía FK con CASCADE).

**Corrección de nombre:** el catálogo mantiene el nombre visible **"Cargo/Posición"** en sidebar, títulos del controlador y la página de Catálogos Simples (no se renombró para igualar el nombre interno del catálogo, sino al revés).

**Migración:** `2026_08_28_040000_add_nivel_formacion_and_fecha_vinculacion_to_people_table.php`.

**Test:** `BUG20260813044Test.php`.

---

## BUG-045 — Código de semillero alfanumérico

El campo "Código" al crear un semillero pasó de aceptar solo enteros a aceptar **alfanumérico** (`seedlings.codigo` de `integer` a `varchar(50)`).

**Detalle técnico importante:** la primera versión de la migración usó SQL crudo específico de MySQL (`ALTER TABLE ... MODIFY`), lo que rompió 229 tests corriendo sobre SQLite. Se corrigió usando `Schema::table()->change()` (portable entre MySQL y SQLite sin `doctrine/dbal`). La sugerencia automática de "siguiente código" se recalculó en PHP (no con `MAX()` SQL, que es lexicográfico sobre varchar).

**Migración:** `2026_08_28_050000_change_codigo_to_string_in_seedlings_table.php`.

**Test:** `BUG20260813045Test.php`.

---

## BUG-046 — `Undefined variable $url` en Archivos de Semillero

Bug preexistente (no introducido por esta sesión) en `resources/views/lider_semillero/archivos/index.blade.php`: línea con `<a href="{{ $url }}">` usando una variable inexistente, solo se manifestaba con al menos un archivo en la lista.

**Fix:** enlace cambiado a `route('lider-sem.archivos.descargar', $f)`.

**Test:** `BUG20260813046Test.php`.

---

## BUG-047 — Formulario "Registrar aprendiz"

En el formulario del lider_proyecto: se agregaron **Teléfono** y **Correo Electrónico**; el campo de texto libre "Nombre de tecnólogo" se reemplazó por **"Programa de Formación"**, conectado al catálogo real `training_programs` que administra `administrador_sistema`.

**Migración:** `2026_08_28_060000_add_fields_and_training_program_to_project_learners_table.php` (agrega `telefono`, `email`, `training_program_id`; elimina `nombre_tecnologo`).

**Archivos:** `ProjectLearner` (relación `trainingProgram()`), `LiderProyecto/AprendizController`, reportes de los 3 roles que mostraban `nombre_tecnologo`.

**Test:** `BUG20260813047Test.php`.

---

## BUG-048 — Barra de avances real en proyectos (lider_semillero)

La "barra de avances" que veía `lider_semillero` en cada proyecto era decorativa: un porcentaje calculado solo por fecha transcurrida (`fecha_inicio`/`fecha_fin`), sin relación con evidencias reales.

**Nuevo modelo de avance — 3 fases con peso fijo:**
- Formulación — **30%** — aprobación de 1 sola etapa (líder de semillero).
- Ejecución — **50%** — aprobación de 1 sola etapa (líder de semillero).
- Producto Final — **20%** — flujo de 2 etapas sin cambios (líder de semillero → director de semilleros).

**Dropdown "Tipo" de evidencia** (lider_proyecto → subir evidencia), ampliado de 2 a 4 opciones, en este orden exacto:
1. Evidencia de investigación y/o desarrollo (sin aprobación)
2. Formulación (30%)
3. Ejecución (50%)
4. Producto final (20% — 2 etapas)

**Archivos principales:**
- `app/Enums/TipoEvidenciaEnum.php` — 2 casos nuevos (`Formulacion`, `Ejecucion`).
- `app/Services/LiderSemillero/RevisionEvidenciaService.php` — nuevos `aprobarEtapaUnica()` / `rechazarEtapaUnica()`, que **no** cascadean a `estado_revision_director` (a diferencia de la aprobación de Producto Final).
- `app/Http/Controllers/LiderProyecto/EvidenciaController.php` — dropdown y lógica de creación ampliados.
- `app/Http/Controllers/LiderSemillero/ProductosController.php` — el query ahora incluye Formulación/Ejecución además de Producto Final; branch por tipo al aprobar/rechazar.
- `app/Http/Controllers/LiderSemillero/ProyectosController.php` — `calcularAvance()` reescrito con la lógica real de fases (se eliminó la lógica de fechas).
- `app/Models/Project.php` — relaciones `evidenciasFormulacion()` / `evidenciasEjecucion()` agregadas.
- `resources/views/components/app-layout.blade.php` — badges de notificación del sidebar (lider_semillero y lider_proyecto) ampliados para incluir los 2 tipos nuevos, evitando que quedaran desincronizados con lo que la página ya mostraba.
- No se tocó `DirectorSemilleros/RevisionProductoController` ni los reportes PDF/Excel (fuera de alcance).

**Test:** `BUG20260813048Test.php` (9 tests) — orden exacto del dropdown, aprobación de etapa única sin disparar la del director, el director no ve Formulación/Ejecución, Producto Final sigue exigiendo doble aprobación, y las 4 combinaciones de avance (0/30/80/100%).

---

## BUG-049 — Página "Productos" agrupada por proyecto (lider_semillero)

La página `lider-sem.productos` mostraba una **tabla plana** con todas las evidencias de Formulación/Ejecución/Producto Final de todo el semillero mezcladas (con columna "Proyecto" para identificarlas). Se pidió agruparlas por proyecto.

**Decisiones confirmadas con el usuario:**
- Solo aparecen los **proyectos que ya tienen al menos una evidencia** (no todos los proyectos del semillero).
- Presentación como **acordeón colapsable**; los proyectos con evidencias pendientes aparecen expandidos por defecto.

**Archivos:**
- `app/Http/Controllers/LiderSemillero/ProductosController.php` — `index()` agrupa el resultado por `project_id` (`groupBy`), calcula `pendientes_count` por grupo y ordena los grupos con pendientes primero.
- `resources/views/lider_semillero/productos/index.blade.php` — reescrita como acordeón Alpine.js (`x-show`/`x-transition`, sin depender de plugins adicionales de Alpine); cada proyecto es una tarjeta con su líder de proyecto, contador de pendientes y su propia tabla de productos.

**Test:** `BUG20260813049Test.php` (4 tests) — proyectos sin evidencias no aparecen, las evidencias quedan bajo el proyecto correcto, contador/expansión por defecto, y que aprobar sigue funcionando tras el cambio.

---

## Cierre de sesión (verificación `/fix-verify`)

- **253/253 tests pasando** (709 assertions).
- 13 migraciones nuevas en esta sesión, todas `Ran`, ninguna pendiente.
- Sin rastros de código muerto de los módulos eliminados (`KnowledgeArea`, `KnowledgeGrandArea`, `TrainingRecord`, `JornadaEnum`) en `app/`, `resources/`, `routes/` ni `database/`.
- `php artisan view:clear` y `composer dump-autoload` ejecutados sin errores.
- Toda la rama sigue **sin commitear**, por instrucción explícita del usuario durante la sesión.

**Verificación manual pendiente (recomendada, no bloqueante):**
- [ ] Navegar el acordeón de "Productos" (lider_semillero) con un semillero real que tenga varios proyectos.
- [ ] Confirmar visualmente la barra de avances en 0/30/80/100% con evidencias reales aprobadas.
- [ ] Revisar en el navegador el catálogo "Cargo/Posición" en Catálogos Simples (BUG-044) y el formulario de perfil con CVLAC/Nivel de Formación (BUG-043/044).
