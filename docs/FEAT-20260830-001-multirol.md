## PLAN DE FEATURE — GIDESTH
**ID:** FEAT-20260830-001
**Tipo:** A (nueva capacidad estructural transversal) + B (nuevo campo/flujo en módulo existente de gestión de usuarios)
**Nombre:** Usuarios multi-rol con "rol activo" seleccionable

### Descripción
Un usuario puede tener asignado **un rol principal** más **uno o varios roles adicionales**. Al iniciar sesión, el sistema se comporta exactamente igual que hoy (dashboard y sidebar del rol principal). Un nuevo ítem del sidebar, **"Mis roles"**, permite ver los roles asignados y cambiar el **rol activo**: al cambiar, el dashboard, el sidebar y las acciones que el usuario realice pasan a ser completamente las del rol elegido — no una mezcla de ambos — con la opción de volver al rol principal en cualquier momento.

### Roles con acceso
- **Todos los roles** pueden tener el ítem "Mis roles" (solo visible si tienen ≥ 2 roles asignados).
- La **asignación** de roles adicionales a otros usuarios es exclusiva de `super_administrador` y `administrador_sistema`.
- `administrador_sistema` **no puede** asignarse roles adicionales a sí mismo (restricción explícita del cliente).

### ¿Requiere training_center_id?
**Compartido, no requiere cambios de esquema.** Se confirmó con el usuario que `training_center_id` sigue siendo una sola columna por usuario, válida para todos sus roles "centro-bound" simultáneamente (un usuario no puede ser admin del Centro A y líder de semillero del Centro B a la vez). Esto evita crear una tabla `user_role_training_center`.

### Decisión de aislamiento (confirmada con el usuario)
Spatie Permission no tiene concepto de "rol activo": si un usuario tiene 2 roles asignados, técnicamente **ya** podría acceder a las rutas de ambos módulos hoy (cada módulo solo valida `role:X` de Spatie, no importa qué esté "activo"). El usuario pidió el aislamiento **estricto**: si el rol activo no es el rol del módulo al que se intenta entrar (por URL directa o cualquier otro medio), la petición debe rechazarse con 403, obligando a cambiar de rol activo primero desde "Mis roles". Esto requiere tocar el middleware de **todos** los grupos de rutas por rol.

---

### Archivos a CREAR (nuevos)
1. `app/Support/ActiveRoleContext.php` — servicio central: resuelve el rol activo actual (sesión → si no existe, rol principal vía `RoleModuleLinks::pickPrimaryRoleNameFromNames`), expone `current()`, `assignedRoles(User $user)`, `switchTo(User $user, string $role)` con validación de que el usuario realmente tiene ese rol asignado, y `isPrimary()`.
2. `app/Http/Middleware/EnsureActiveRole.php` — middleware parametrizable (`active_role:lider_semillero` o `active_role:administrador_sistema,super_administrador` para módulos compartidos) que compara contra `ActiveRoleContext::current()`; si no coincide, aborta 403 con mensaje indicando qué rol debe activar primero.
3. `app/Http/Controllers/RoleSwitchController.php` — `index()` (pantalla "Mis roles": lista los roles asignados con su label vía `RoleModuleLinks::labelForRoleName()`, marca visualmente cuál está activo) y `switch(Request $request)` (valida, guarda en sesión, redirige al dashboard del rol elegido reusando `RoleModuleLinks::urlForRoleName()`).
4. `resources/views/roles/mis-roles.blade.php` — pantalla de selección de rol activo.
5. `tests/Feature/Regression/BUG20260813050Test.php` — núcleo: cambio de rol, aislamiento 403 en módulo no activo, vuelta al rol principal, sesión se resetea al rol principal en un nuevo login.
6. `tests/Feature/Regression/BUG20260813051Test.php` — asignación de roles adicionales: solo super_administrador/administrador_sistema pueden asignar, administrador_sistema no puede autoasignarse.

### Archivos a MODIFICAR (existentes)
1. `bootstrap/app.php` — registrar el alias de middleware `active_role`.
2. `routes/web.php` — el bloque `/dashboard` deja de resolver por la cadena fija de `if/elseif hasRole(...)` y usa `ActiveRoleContext::current()`; agregar `active_role:administrador_sistema,super_administrador` al grupo `admin.` (único prefijo hoy compartido sin `role:` propio en la mayoría de sus rutas).
3. `routes/lider_semillero.php`, `routes/lider_proyecto.php`, `routes/director_semilleros.php`, `routes/co_investigador.php`, `routes/super_admin.php` — agregar `active_role:<rol>` junto al `role:<rol>` ya existente en cada grupo.
4. `app/Livewire/Auth/Login.php` — al autenticar, inicializar el rol activo en sesión con el rol principal (mismo criterio que usa hoy para redirigir).
5. `resources/views/components/app-layout.blade.php` — `$menuContext` pasa a leer `ActiveRoleContext::current()` en vez de solo la ruta actual + prioridad; nuevo ítem **"Mis roles"** en el sidebar (visible solo si `count(assignedRoles) > 1`), con indicador de cuál está activo y acceso directo para volver al rol principal.
6. `app/Livewire/Admin/Users/UserEdit.php` + su vista — el `<select>` de un solo rol pasa a: "Rol principal" (select, comportamiento actual) + "Roles adicionales" (checkboxes), editable solo por `super_administrador`/`administrador_sistema`, bloqueado cuando `administrador_sistema` edita su propio usuario.
7. `app/Livewire/Admin/Users/UserCreate.php` — mismo patrón al crear (a confirmar alcance: ¿se permiten roles adicionales desde la creación o solo después vía edición?).
8. `app/Services/Admin/UserCreationService.php` — soportar múltiples `assignRole()` si se habilita en creación.
9. Policy de usuarios correspondiente — regla explícita de quién puede tocar "roles adicionales" y la restricción de autoasignación de `administrador_sistema`.

### Orden de implementación recomendado (dividido en fases por el tamaño — 15+ archivos)

**Fase 1 — Núcleo de rol activo (sin tocar aislamiento todavía)**
1. `ActiveRoleContext` + tests unitarios.
2. `EnsureActiveRole` + registro en `bootstrap/app.php` (sin aplicarlo aún a ningún grupo de rutas).
3. `Login.php` inicializa el rol activo en sesión.
4. `RoleSwitchController` + vista "Mis roles" + rutas.
5. `/dashboard` y `app-layout.blade.php` usan el rol activo real; ítem "Mis roles" visible en el sidebar.

**Fase 2 — Aislamiento estricto por módulo (alto riesgo, uno a la vez)**
6. Aplicar `active_role:` a `lider_semillero`, correr suite completa.
7. Aplicar a `lider_proyecto`, correr suite completa.
8. Aplicar a `director_semilleros`, correr suite completa.
9. Aplicar a `co_investigador`, correr suite completa.
10. Aplicar a `super_admin` y al grupo `admin.` compartido, correr suite completa.

**Fase 3 — Asignación de roles adicionales en administración**
11. `UserEdit` + Policy + tests.
12. `UserCreate` + `UserCreationService` (si aplica) + tests.

**Fase 4 — Cierre**
13. `/fix-verify` de toda la feature + verificación manual con un usuario de prueba multi-rol real.

### Dependencias con código existente
- `RoleModuleLinks` (prioridad de rol, labels, URLs de dashboard) se reutiliza tal cual, no se reemplaza.
- Toca directamente 3 bugs ya cerrados en esta sesión: BUG-031/038 (guard de `training_center_id` en `UserCreationService`), BUG-034 (`UserEdit` ya tiene lógica condicional de centro que hay que preservar al agregar roles adicionales).
- `TrainingCenterAccess::CENTRO_BOUND_ROLE_NAMES` no cambia — un usuario multi-rol sigue teniendo un solo `training_center_id` válido para todos sus roles centro-bound.

### Riesgos identificados
- **Es el cambio de mayor alcance de toda la sesión**: toca autenticación, sesión, el middleware de TODAS las rutas protegidas por rol, y el layout compartido entre los 7 roles. Un error aquí puede bloquear el acceso legítimo de cualquier usuario del sistema — por eso la Fase 2 se aplica módulo por módulo con la suite completa entre cada uno, no de una vez.
- El grupo `admin.` en `routes/web.php` hoy **no** tiene `role:` middleware en la mayoría de sus rutas (el comentario en el código dice explícitamente "los permisos finos se controlan dentro de las vistas/componentes") — aplicar `active_role` ahí sin mapear bien quién lo usa puede romper accesos hoy válidos.
- Los ~253 tests de regresión existentes usan `actingAs($user)` con un solo rol asignado — deberían seguir pasando sin cambios (con un solo rol, el rol activo por defecto es siempre ese rol), pero hay que confirmarlo módulo por módulo en la Fase 2.
- `UserEdit`/`UserCreationService` ya fueron tocados por BUG-031/034/038 — cualquier regresión aquí afecta 3 bugs ya cerrados en esta misma sesión.

---

## Estado de implementación

### Fase 1 — COMPLETADA (2026-08-30)

**Descubrimiento importante antes de implementar:** ya existía una base parcial de multi-rol (columna `users.primary_role_name`, `RoleModuleLinks::moduleLinksWithPrimary()`, y un selector de roles funcional en el header). Se decidió con el usuario **reusar y ajustar esa base** en vez de construir un selector nuevo desde cero — esto redujo el alcance real de la Fase 1 frente al plan original.

- **BUG-20260813-050** (bug latente encontrado y corregido de una vez, según lo pidió el usuario): el `/dashboard` genérico y el middleware `RedirectDirectorToModule` decidían el destino con `hasRole()` suelto en un orden fijo que no coincidía con `RoleModuleLinks::LOGIN_ROLE_PRIORITY`. Un usuario multi-rol podía caer en el panel equivocado. Corregido usando `RoleModuleLinks::primaryRoleNameForUser()` como única fuente de verdad en ambos lugares. Test: `tests/Feature/Regression/BUG20260813050Test.php`.
- **BUG-20260813-051** (núcleo de rol activo):
  - `app/Support/ActiveRoleContext.php` (nuevo) — rol activo en sesión (`current()`, `switchTo()`, `initializeForUser()`, `isPrimary()`, `reset()`).
  - `app/Http/Middleware/EnsureActiveRole.php` (nuevo) — registrado como alias `active_role` en `bootstrap/app.php`, **todavía sin aplicar a ningún grupo de rutas** (eso es la Fase 2).
  - `app/Http/Controllers/RoleSwitchController.php` (nuevo) + ruta `POST /mis-roles/cambiar` (`roles.switch`).
  - `app/Livewire/Auth/Login.php` y `app/Http/Responses/LoginResponse.php` (login normal y login vía 2FA de Fortify, respectivamente) inicializan el rol activo al iniciar sesión.
  - `resources/views/components/app-layout.blade.php` — el selector de roles del header ("Roles y Módulos") ahora se llama **"Mis roles"**; sus enlaces pasan de `<a href>` directos a formularios `POST` contra `roles.switch` (para que el cambio de contexto quede en sesión antes de redirigir); se eliminó el override que forzaba `menuContext = 'super_administrador'` siempre que el usuario tuviera ese rol (bloqueaba el cambio de contexto); `$menuContext` ahora prioriza el rol activo de sesión sobre la detección por URL.
  - Test: `tests/Feature/Regression/BUG20260813051Test.php` (5 tests).
- **261/261 tests pasando** tras la Fase 1 completa.

### Fase 2 — COMPLETADA (2026-08-30)

**BUG-20260813-052.** Se agregó `active_role:<rol>` junto al `role:<rol>` de Spatie ya existente en los 6 grupos de rutas por rol:
- `routes/lider_semillero.php`, `routes/lider_proyecto.php`, `routes/director_semilleros.php`, `routes/co_investigador.php`, `routes/super_admin.php`, `routes/admin.php` (el módulo real de `administrador_sistema`; distinto del grupo compartido `admin.` en `routes/web.php`).

**Decisión de alcance:** el grupo `admin.` compartido de catálogos en `routes/web.php` (departamentos, ciudades, programas de formación, etc.) **NO** recibió `active_role`, deliberadamente — hoy no tiene `role:` de Spatie tampoco (accesible a cualquier usuario autenticado, permisos finos en las vistas), es compartido entre roles por diseño, y restringirlo arriesgaba romper accesos legítimos sin ningún beneficio real de aislamiento (no es un "módulo de un rol", es un catálogo transversal).

Test: `tests/Feature/Regression/BUG20260813052Test.php` (4 tests) — 403 por URL directa a un módulo de un rol asignado pero no activo, el módulo del rol activo funciona normal, cambiar de rol invierte cuál está bloqueado, y un usuario de un solo rol nunca recibe 403 por esta causa.

**265/265 tests pasando** tras la Fase 2 completa.

### Fase 3 — COMPLETADA, luego CORREGIDA de alcance (2026-08-30)

**Primer intento (BUG-20260813-053, revertido):** se agregó el fieldset en `UserEdit.php` (edición). El usuario reportó que no lo veía en ningún rol. Verificación: `UserEdit` (ruta `admin.users.manage.edit`) **no está enlazada en ninguna vista del sistema** — componente huérfano. Además, `super_administrador` no tiene NINGÚN flujo de edición de usuarios hoy (`AdminUsuarioController`/`UsuarioSistemaController` solo tienen `index`/`create`/`store`/`toggleEstado`/`destroy`, sin `edit`/`update`). El usuario corrigió el alcance: la opción va **solo en creación**, no en edición. BUG-053 y su test se revirtieron por completo.

**BUG-20260813-054 (implementación correcta).** Checkbox de revelado progresivo **"¿Este usuario tiene más roles?"** (Alpine `x-show`) en los 3 formularios reales de creación:
- `super-admin/usuarios/create.blade.php` (`AdminUsuarioController` — super_administrador crea `administrador_sistema`).
- `admin/director_semilleros/create.blade.php` (`Admin\UsuarioController::createDirectorSemilleros`/`storeDirectorSemilleros`).
- `admin/coinvestigadores/create.blade.php` (`Admin\UsuarioController::createCoinvestigador`/`storeCoinvestigador`).

El rol principal de cada pantalla sigue exactamente igual (fijo, sin selector). Backend:
- `RoleAssignmentMatrix::additionalRoleOptionNamesFor(string $primaryRole)` (nuevo): universo de roles adicionales = todos **excepto** `super_administrador` y excepto el rol principal de esa pantalla.
- `UserCreationService::crearUsuario()`: nuevo parámetro `additional_roles`; el guard de "el rol exige centro" ahora considera también los adicionales; asigna los roles adicionales dentro de la misma transacción atómica.
- Los 2 controladores sanean `additional_roles` contra `additionalRoleOptionNamesFor()` y solo los aceptan si `tiene_mas_roles` viene marcado (nunca confían en el array del formulario tal cual).
- `Admin\UsuarioController::storeConRolFijo()`: se corrigió también el cálculo de `training_center_id` para `co_investigador` — antes era `null` siempre; ahora hereda el centro del admin creador si algún rol adicional lo exige (p. ej. `lider_semillero`).

Test: `tests/Feature/Regression/BUG20260813054Test.php` (6 tests) — creación con adicionales en los 3 formularios, el checkbox sin marcar ignora `additional_roles` aunque lleguen en el request, no se puede inyectar `super_administrador`, y `co_investigador` hereda centro solo cuando corresponde.

**271/271 tests pasando** tras la corrección de la Fase 3.

### BUG-20260813-055 (hallado en verificación manual, corregido 2026-08-30)

El usuario probó manualmente la feature y reportó que "Mis roles" no aparecía. Investigación: **no es que faltara** — el botón del header solo muestra el nombre del rol actual (no dice literalmente "Mis roles" hasta abrirlo), y coexistía con una tarjeta **"Accesos a otros módulos"** en `resources/views/admin/dashboard.blade.php` que ya existía **antes de esta sesión**. Esa tarjeta usaba `<a href>` directo — al no pasar por `roles.switch`, el rol activo de sesión nunca se actualizaba, así que desde la Fase 2 (aislamiento) esos enlaces devolvían **403**: una regresión real introducida sin saberlo, porque esa tarjeta no era visible en el código que se tocó directamente en las fases anteriores.

**Decisión del usuario:** eliminar la tarjeta duplicada y dejar el selector del header como único punto de cambio de rol; se le agregó una etiqueta visible "Mis roles:" + icono para que sea más fácil de encontrar.

**Archivos:**
- `resources/views/admin/dashboard.blade.php` — se quitó la tarjeta "Accesos a otros módulos".
- `app/Http/Controllers/Admin/DashboardController.php` — se quitó la variable `$roleModuleNav` (ya sin uso).
- `resources/views/components/app-layout.blade.php` — el botón del selector de roles ahora incluye ícono + etiqueta "Mis roles:" visible, no solo el nombre del rol activo.

Test: `tests/Feature/Regression/BUG20260813055Test.php` (2 tests).

**273/273 tests pasando.**

### BUG-20260813-056 (hallado en verificación manual, corregido 2026-08-30)

El usuario probó con un `director_semilleros` con roles adicionales: cambiar de rol activo funcionaba, pero **volver** (clic en el logo SIGESI, o cualquier navegación de vuelta) daba 403. Se lanzó una auditoría exhaustiva (agente `Explore`) para encontrar TODOS los puntos con el mismo patrón, no solo el reportado.

**Causa raíz:** varios puntos decidían "a dónde te mando" con el **rol principal** (`primaryRoleNameForUser`/`dashboardUrlForUser`) o con **permisos acumulados de todos los roles asignados** (`@can`/`@hasrole`), en vez del **rol activo** (`ActiveRoleContext::current()`), navegando con `<a href>`/`redirect()` directos que no pasan por `roles.switch`.

**Corregido:**
1. Logo SIGESI (`app-layout.blade.php`, variable `$dashboardUrl`) — ahora apunta al dashboard del rol **activo**, no al principal.
2. Ruta genérica `/dashboard` (`routes/web.php`) y middleware `RedirectDirectorToModule` — decidían con `primaryRoleNameForUser()`, ahora con `ActiveRoleContext::current()`. Esto arregló de una sola vez ~16 breadcrumbs "Administración/Catálogos" en vistas de admin que usaban `route('dashboard')`, sin tocar esos 16 archivos.
3. `resources/views/dashboard/home.blade.php` — su redirección de respaldo duplicada usaba `hasRole()`; alineada al rol activo (código hoy inalcanzable, pero consistente).
4. `Login.php::mount()` — le faltaba inicializar el rol activo como ya hace `login()` (en la práctica el middleware `guest` de Laravel intercepta antes de llegar aquí y usa `/dashboard`, ya corregido en el punto 2 — se deja el fix igual por defensa en profundidad).
5. Bloque `@else` final del sidebar (`app-layout.blade.php` ~L564-616) — 3 enlaces (`dir-sem.dashboard`, `admin.usuarios.index`, `admin.catalogos.index`) gateados por `@can()`/`@hasrole()` de TODOS los roles asignados, no del activo. Convertidos a formularios `roles.switch`, igual que el selector "Mis roles".
6. `RoleModuleLinks::urlForRoleName()` ahora acepta `?string` (antes exigía `string` no-nulo) — evita un `TypeError` real que apareció al aplicar el fix del logo para un usuario sin ningún rol asignado.
7. `RoleSwitchController` — refuerzo defensivo menor en su fallback de URL.

Test: `tests/Feature/Regression/BUG20260813056Test.php` (5 tests).

**278/278 tests pasando.**

**Hallazgo aparte, documentado como BUG-20260813-057 (NO corregido, decisión del usuario: dejarlo para después):** las rutas de `external-advisors`, `minciencias-typologies`, `minciencias-subcategories`, `training-programs`, `training-centers` en el grupo `admin.` compartido de `routes/web.php` no tienen ningún middleware `role:` — cualquier usuario autenticado activo puede entrar sin importar su rol. Preexistente a esta sesión, no relacionado con `roles.switch`/rol activo — es un hueco de autorización distinto.

**Nota menor sin BUG-ID (no reportada por el usuario, encontrada durante la investigación):** al probar `lider_proyecto` como rol adicional de un usuario recién creado, su dashboard da 404 — porque ese módulo asume que el usuario ya lidera un `Project` real (`LiderProyectoContext::miProyecto()` con `firstOrFail()`), algo que no existe automáticamente solo por tener el rol asignado. No es un bug de esta feature — es una precondición de negocio preexistente (un lider_proyecto necesita que le asignen un proyecto). Queda anotado por si se vuelve a reportar.

### BUG-20260813-058 (hallado en verificación manual, corregido 2026-08-30)

El usuario probó exhaustivamente los 2 usuarios multi-rol reales (32 y 33) y encontró que `lider_proyecto` como rol secundario nunca puede recibir un proyecto asignado — confirmado que el error real ahí es **404** (no 403; el 403 original se resolvió con BUG-056, esto era un problema distinto que solo se hizo visible después).

**Causa raíz:** `LiderSemillero/ProyectosController.php` filtraba los `lider_proyecto` asignables por `created_by_user_id = Auth::id()` (quién creó la cuenta) — nadie puede ser su propio creador, así que un `lider_proyecto` otorgado como rol secundario (no creado a través del flujo normal "líder de semillero crea → asigna proyecto") nunca aparece en ningún listado, de ningún líder de semillero del sistema. Contraste: `DirectorSemilleros/SemilleroController.php` (asignar líder de semillero a un semillero) filtra por `training_center_id`, no por creador — de ahí la asimetría que el usuario detectó ("con semillero sí me autoasigno, con proyecto no").

**3 opciones evaluadas con el usuario:** (A) quitar `lider_proyecto` de los roles secundarios asignables, (B) reemplazar el filtro por centro, (C) sumar el centro sin quitar el filtro por creador. **Se eligió C** — la más segura, no quita ningún comportamiento existente.

**Corregido:** `ProyectosController::validarProyecto()` y `datosFormulario()` (`lideresProyecto`) — el filtro pasa de `where('created_by_user_id', Auth::id())` a `where(fn($q) => $q->where('created_by_user_id', Auth::id())->orWhere('training_center_id', $miCentro))`. No se tocó `LiderProyectoController::index()` (gestión de cuentas creadas — se queda solo por creador, es un flujo distinto de "asignar a un proyecto").

Test: `tests/Feature/Regression/BUG20260813058Test.php` (4 tests) — comportamiento viejo intacto, nuevo caso (mismo centro, creado por otro) funciona, otro centro sigue bloqueado, dropdown del formulario refleja el cambio.

**282/282 tests pasando.**

### BUG-20260813-059 (hallado en verificación manual, corregido 2026-08-30)

`TrainingCenterAccess::scopeUserQueryForList()` excluía a `super_administrador` y al propio usuario que consulta, pero no a otras cuentas `administrador_sistema`. Antes de multi-rol nunca se notaba (solo hay un admin por centro, y ese admin ya se excluía a sí mismo). Con multi-rol, un `administrador_sistema` de OTRO centro puede aparecer en el listado si además tiene un rol global (ej. `co_investigador`) — se ve por ese rol global sin importar el centro, sin que el sistema sepa que también administra otro centro.

**Corregido:** se agregó `whereDoesntHave('roles', ... 'administrador_sistema')` **solo** dentro de `scopeUserQueryForList()` — no en `scopeUserQueryForMetrics()`, para no romper los conteos exactos de BUG-035. Afecta: `admin.usuarios.index`, el widget "usuarios recientes" del dashboard admin, y `Livewire\Admin\Users\UserIndex`.

Test: `tests/Feature/Regression/BUG20260813059Test.php` (4 tests) — admin de otro centro con rol global ya no aparece, verificación directa del scope, los conteos de métricas siguen intactos, y co_investigador (sin ser admin) sigue visible por rol global como antes.

**286/286 tests pasando.**

### BUG-20260813-060 (2026-08-30) — roles adicionales también en edición

El usuario pidió extender la gestión de roles adicionales (hasta ahora solo en creación, BUG-054) a las pantallas de **editar usuario**, para `administrador_sistema` y `super_administrador`.

**Hallazgo antes de implementar:** `administrador_sistema` ya tenía una pantalla de edición real (`admin.usuarios.edit`/`update`). `super_administrador` **no tenía ninguna** — sus 2 controladores (`AdminUsuarioController`, `UsuarioSistemaController`) solo tenían `index/create/store/toggle-estado/destroy`. Se confirmó con el usuario construir la edición completa desde cero para super_administrador.

**Corregido/agregado:**
- `App\Support\RoleAssignmentMatrix::syncAdditionalRoles()` (nuevo, reutilizable): agrega/quita roles adicionales comparando contra el universo permitido — usado por los 3 flujos de edición.
- `Admin\UsuarioController::edit()/update()` + `admin/usuarios/edit.blade.php` — checkbox agregado a la pantalla ya existente.
- `SuperAdmin\AdminUsuarioController::edit()/update()` (nuevos) + vista nueva `super-admin/usuarios/edit.blade.php` + rutas `super-admin.administradores.edit/update` — edición completa de cuentas `administrador_sistema`.
- `SuperAdmin\UsuarioSistemaController::edit()/update()` (nuevos) + vista nueva `super-admin/usuarios-sistema/edit.blade.php` + rutas `super-admin.usuarios-sistema.edit/update` — edición completa de cualquier otro rol (excepto admin/super).
- Enlaces "Editar" agregados a ambos listados de super_administrador.

**Cuidado especial:** las nuevas búsquedas de usuario en los controladores de super_administrador usan `whereHas`/`whereDoesntHave` directo (igual que sus propios `index()`), **no** `TrainingCenterAccess::scopeUserQueryForList()` — porque BUG-059 (mismo día) hizo que ese scope excluya a todas las cuentas `administrador_sistema`, lo que habría roto silenciosamente la edición de admins por parte de super_administrador si se hubiera reutilizado.

Test: `tests/Feature/Regression/BUG20260813060Test.php` (6 tests) — administrador_sistema agrega roles a otro, no puede a sí mismo, super_administrador edita administrador_sistema y usuario del sistema, no se puede inyectar super_administrador, y desmarcar quita el rol.

**292/292 tests pasando.**

**Nota (no corregida, fuera de este alcance):** `SuperAdmin\UsuarioSistemaController::create()` (crear "usuario del sistema") nunca tuvo el checkbox de roles adicionales de BUG-054 — quedó fuera del alcance original (solo se cubrieron las 3 pantallas de la matriz de creación exclusiva). Queda como posible mejora futura si se pide.

### BUG-20260813-061 (hallado en verificación manual, corregido 2026-08-30)

El usuario reportó que el checkbox de BUG-060 no aparecía al usar el lápiz de "Usuarios y Roles". Causa: **el mismo patrón de error que BUG-053** — el checkbox se había puesto en `admin/usuarios/edit.blade.php` (página de página completa), pero el lápiz de esa pantalla en realidad abre un **modal Alpine.js separado** dentro de `admin/usuarios/index.blade.php` (mismo endpoint `admin.usuarios.update`, formulario del DOM completamente distinto, poblado vía JS con `openEditar(id, data)`). La página de página completa no está enlazada desde ningún lado.

**Corregido:** el checkbox se agregó al modal real. Como el modal es único y compartido para editar usuarios con distintos roles principales (no se re-renderiza por servidor por fila), la lista de roles adicionales se maneja en Alpine: un array estático de opciones (`administrador_sistema`, `director_semilleros`, `lider_semillero`, `lider_proyecto`, `co_investigador` — nunca `super_administrador`) filtrado en el cliente para excluir el rol principal seleccionado en ese momento (`x-model="editRol"` en el select). El botón de lápiz de cada fila ahora también envía los roles adicionales actuales del usuario (`additionalRoles`) en el payload JSON que abre el modal, para precargar los checkboxes.

Test: `tests/Feature/Regression/BUG20260813061Test.php` (2 tests) — el checkbox real aparece en el listado (no en la página muerta), y guardar desde ese mismo endpoint sigue funcionando.

**294/294 tests pasando.**

### Pendiente
- **Fase 4** — cierre con `/fix-verify` + verificación manual.

---
Acción siguiente:
"Usa el agente feature-builder para implementar la Fase 1 de FEAT-20260830-001" (una vez confirmada por el usuario).
