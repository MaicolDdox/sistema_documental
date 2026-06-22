# PROMPT MAESTRO — Refactoring, Bugs y Arquitectura
## Sistema Documental SENA GIDESTH — Laravel 12 + Livewire 4

> **Instrucciones de uso:** Este documento es un prompt estructurado para ser entregado a una IA de código (Claude Code, Cursor, Copilot, etc.). Contiene todas las tareas de refactoring, corrección de bugs y mejoras de arquitectura que debe ejecutar sobre este repositorio. Cada sección incluye el contexto, el problema, la solución esperada y los criterios de verificación.

---

## CONTEXTO DEL PROYECTO

Eres un desarrollador senior Laravel trabajando en el sistema **Sistema Documental SENA GIDESTH**, ubicado en `c:\laragon\www\sistema_documental`. Es una aplicación **Laravel 12 + Livewire 4 + Tailwind CSS 4 + Spatie Laravel Permission + Laravel Fortify**. El sistema gestiona actividad investigativa del SENA con 7 roles de usuario: `super_administrador`, `administrador_sistema`, `director_investigacion`, `director_semilleros`, `lider_semillero`, `asesor_semillero`, `investigador_asociado`.

**Antes de comenzar cualquier tarea:**
1. Lee el archivo del código afectado completamente
2. Verifica que el cambio no rompe funcionalidad existente
3. Sigue el estilo de código existente del proyecto
4. No agregues dependencias nuevas salvo que se indique
5. No modifiques tests existentes sin autorización explícita

---

## BLOQUE 1: BUGS CRÍTICOS — SEGURIDAD Y AUTORIZACIÓN

### BUG-01: Endpoints API sin validación de scope
**Archivos:**
- `app/Http/Controllers/AsesorSemillero/ProductoController.php` — métodos `apiProyectosPorSemillero()` y `apiAutoresPorProyecto()`
- `app/Http/Controllers/LiderSemillero/ProductosController.php` — método `apiAutoresPorProyecto()`

**Problema:** Los endpoints de API interna (`/api/semillero/{id}/proyectos`, `/api/proyecto/{id}/autores`) no verifican que el recurso pertenezca al usuario autenticado. Cualquier usuario autenticado puede consultar proyectos o autores de cualquier semillero/proyecto pasando un ID arbitrario.

**Solución requerida:** En cada método de API, antes de consultar datos:
1. Verificar que el semillero/proyecto pertenece al usuario autenticado
2. Si no pertenece, retornar `response()->json(['error' => 'No autorizado'], 403)`
3. Usar el helper `AsesorSemilleroContext` o `TrainingCenterAccess` ya existentes en el proyecto

**Criterio de verificación:** Un usuario de centro A no puede obtener datos de un semillero de centro B pasando su ID en la URL.

---

### BUG-02: Creación de usuario sin transacción de base de datos
**Archivo:** `app/Http/Controllers/Admin/UsuarioController.php` — método `store()`

**Problema:** El método crea primero el `User` y luego el `Person` en llamadas separadas. Si falla la creación del `Person`, queda un `User` huérfano sin datos personales, corrompiendo la base de datos.

**Código problemático actual (aproximado):**
```php
$user = User::create([...]);
Person::create(['user_id' => $user->id, ...]);
```

**Solución requerida:** Envolver ambas operaciones en `DB::transaction()`:
```php
DB::transaction(function () use ($validated, $request) {
    $user = User::create([...]);
    Person::create(['user_id' => $user->id, ...]);
});
```
Aplicar el mismo patrón en `app/Services/Director/InvestigadorService.php` método `crearInvestigador()` y en `app/Http/Controllers/DirectorSemilleros/LiderSemilleroController.php` método `store()`.

**Criterio de verificación:** Si la creación de `Person` lanza una excepción, no debe existir el `User` en la base de datos.

---

### BUG-03: Sesión de semillero activo sin validación de ownership
**Archivo:** `app/Http/Controllers/AsesorSemillero/SemilleroActivoController.php` — método `store()`
**Archivo:** `app/Http/Controllers/AsesorSemillero/AprendizController.php` — método `getSemilleroDelAsesor()` (o similar)

**Problema:** El asesor puede cambiar el `seedling_id` en sesión a cualquier valor sin verificar que ese semillero le pertenece. Luego el filtrado de aprendices, proyectos y productos se hace en base a ese ID de sesión.

**Solución requerida:**
1. En `SemilleroActivoController::store()`, antes de guardar en sesión, verificar que el semillero existe Y que el usuario autenticado es asesor o líder de ese semillero:
```php
$semillero = Seedling::findOrFail($request->seedling_id);
$esMiembro = DB::table('seedling_advisors')
    ->where('seedling_id', $semillero->id)
    ->where('user_id', auth()->id())
    ->exists();
if (!$esMiembro) abort(403);
session(['semillero_activo_id' => $semillero->id]);
```

**Criterio de verificación:** Un asesor no puede activar un semillero de otro centro aunque conozca su ID.

---

### BUG-04: Strings mágicos en lugar de Enums
**Archivos afectados:**
- `app/Http/Controllers/Admin/DashboardController.php` (líneas con `'activo'`, `'inactivo'`)
- `app/Http/Controllers/AsesorSemillero/DashboardController.php` (líneas con `'pendiente'`, `'aprobado'`, `'rechazado'`, `'en_revision'`)
- `app/Http/Controllers/AsesorSemillero/ExportarReporteController.php`
- Cualquier otro controller que use strings de estado directamente

**Problema:** Se usan strings literales como `'pendiente'`, `'activo'`, `'aprobado'` en comparaciones y queries en lugar de los enums PHP ya definidos en `app/Enums/`.

**Solución requerida:** Reemplazar todos los strings mágicos de estado con sus Enums correspondientes:
```php
// MAL
->where('estado', 'activo')
->where('estado_revision', 'pendiente')

// BIEN
->where('estado', EstadoEnum::Activo)
->where('estado_revision', EstadoRevisionEnum::Pendiente)
```
Hacer el reemplazo en TODOS los archivos del directorio `app/`. Primero hacer un grep de `'activo'`, `'inactivo'`, `'pendiente'`, `'aprobado'`, `'rechazado'`, `'en_revision'` en contextos de query para identificar todos los casos.

**Criterio de verificación:** `grep -r "'activo'" app/Http/Controllers` no debe devolver resultados en contextos de comparación de estado.

---

### BUG-05: whereRaw('0 = 1') como anti-patrón de autorización
**Archivo:** `app/Http/Controllers/Admin/DashboardController.php` (líneas ~50-52)

**Problema:** Se usa `whereRaw('0 = 1')` para retornar queries vacíos cuando el usuario no tiene autorización, en lugar de hacer una verificación explícita de acceso.

**Código problemático:**
```php
$userQuery = User::query()->whereRaw('0 = 1');
$groupQuery = ResearchGroup::query()->whereRaw('0 = 1');
```

**Solución requerida:** Reemplazar con una verificación explícita:
```php
if (!$this->userHasAccessToCenter($user)) {
    abort(403, 'No tienes acceso a este centro de formación');
}
```
O si el comportamiento correcto es mostrar datos vacíos, documentarlo claramente con un comentario y usar un scope nombrado: `User::none()` (disponible en Laravel).

**Criterio de verificación:** No debe haber ningún `whereRaw('0 = 1')` en el codebase.

---

## BLOQUE 2: BUGS FUNCIONALES — LÓGICA DE NEGOCIO

### BUG-06: Envío de credenciales comentado
**Archivo:** `app/Http/Controllers/Admin/UsuarioController.php` (líneas ~310-313)

**Problema:** El envío de credenciales al crear un usuario está comentado:
```php
// Mail::to($user->email)->send(new \App\Mail\UserCredentialsMail($user, $validated['password']));
```

**Solución requerida:**
1. Crear la clase Mailable si no existe: `app/Mail/UserCredentialsMail.php`
2. Crear la vista del email: `resources/views/emails/user-credentials.blade.php`
3. El email debe incluir: nombre del usuario, email, contraseña temporal, enlace al sistema, instrucciones para cambiar contraseña
4. Descomentar y activar el envío
5. Aplicar el mismo patrón en `app/Http/Controllers/DirectorSemilleros/LiderSemilleroController.php` donde usa `Mail::raw()` sin Mailable

**Criterio de verificación:** Al crear un usuario desde admin, ese usuario recibe un email con sus credenciales.

---

### BUG-07: Falta de validación en subida de archivos
**Archivos:**
- `app/Http/Controllers/AsesorSemillero/EvidenciaController.php`
- `app/Http/Controllers/LiderSemillero/ArchivosSemilleroController.php`
- `app/Http/Controllers/LiderSemillero/DocInternaController.php`
- `app/Http/Controllers/DirectorInvestigacion/DocumentoGrupoController.php`
- `app/Http/Controllers/DirectorSemilleros/DocumentoSemilleroController.php`

**Problema:** Las subidas de archivos no tienen validación consistente de:
- Tipos de archivo permitidos
- Tamaño máximo
- Nombre seguro (prevención de path traversal)

**Solución requerida:** En todos los métodos `store()` de los controllers listados, agregar validación explícita:
```php
$request->validate([
    'archivo' => [
        'required',
        'file',
        'max:10240', // 10MB
        'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg,zip',
    ],
]);
```
Y usar siempre `hashName()` o `store()` de Laravel (no el nombre original del archivo) para guardar en disco.

**Criterio de verificación:** Intentar subir un archivo `.php` o `.exe` debe devolver un error de validación.

---

### BUG-08: Operaciones de archivo sin manejo de excepciones
**Archivos:**
- `app/Http/Controllers/AsesorSemillero/EvidenciaController.php` (métodos `store()`)
- `app/Http/Controllers/AsesorSemillero/ProductoController.php` (método `destroy()`)
- Cualquier controller que use `Storage::disk()` sin try-catch

**Problema:** Las operaciones de Storage pueden fallar (disco lleno, permisos, archivo no existe) sin manejo de error, causando excepciones no controladas.

**Solución requerida:**
```php
try {
    $path = $request->file('archivo')->store('evidencias/proyectos', 'public');
} catch (\Exception $e) {
    \Log::error('Error subiendo archivo: ' . $e->getMessage());
    return back()->withErrors(['archivo' => 'Error al subir el archivo. Intenta de nuevo.']);
}
```
Aplicar en TODOS los métodos que llamen a `Storage::disk()->put()`, `$request->file()->store()`, `Storage::disk()->delete()`.

---

### BUG-09: Typo en campo de base de datos
**Archivo(s) a buscar:** Hacer grep de `descripccion` en todo el proyecto

**Problema:** El campo `descripcion` puede estar escrito como `descripccion` (doble 'c') en algún controller o migration, causando queries que no retornan datos correctamente.

**Solución requerida:**
1. Ejecutar: buscar `descripccion` en todo el directorio `app/` y `database/`
2. Corregir todas las ocurrencias a `descripcion`
3. Si existe en alguna migración ya ejecutada, crear una migración de corrección

---

## BLOQUE 3: REFACTORING — CALIDAD DE CÓDIGO

### REFACT-01: Reemplazar DB::table() por Eloquent en controllers
**Archivos principales:**
- `app/Http/Controllers/AsesorSemillero/EvidenciaController.php`
- `app/Http/Controllers/AsesorSemillero/ProductoController.php`
- `app/Http/Controllers/AsesorSemillero/ProyectoController.php`
- Cualquier controller que use `DB::table('project_seedlings')`, `DB::table('seedling_members')`, `DB::table('seedling_advisors')`

**Problema:** Se usan queries raw `DB::table()` para acceder a tablas de pivote en lugar de usar las relaciones Eloquent definidas en los modelos.

**Solución requerida:** Reemplazar cada `DB::table('project_seedlings')->where(...)` por su equivalente Eloquent:
```php
// MAL
$projectIds = DB::table('project_seedlings')
    ->where('seedling_id', $semillero->id)
    ->pluck('project_id');

// BIEN
$projectIds = $semillero->projects()->pluck('projects.id');
```
Verificar que los modelos `Seedling`, `Project` y `User` tienen las relaciones `belongsToMany` correctamente definidas antes de hacer el reemplazo. Si faltan relaciones, agregarlas primero.

---

### REFACT-02: Extraer lógica de dashboard a servicio o query scope
**Archivo:** `app/Http/Controllers/Admin/DashboardController.php`

**Problema:** El método `index()` contiene lógica compleja de ramificación para determinar qué datos mostrar según el rol del usuario. Esta lógica es difícil de testear y mantener.

**Solución requerida:**
1. Crear clase `app/Services/DashboardService.php`
2. Mover la lógica de determinación de scope a este servicio
3. El controller debe quedar reducido a:
```php
public function index()
{
    $metrics = app(DashboardService::class)->getMetricsForUser(auth()->user());
    return view('admin.dashboard', compact('metrics'));
}
```

---

### REFACT-03: Estandarizar manejo de errores con return back()->with()
**Problema:** Los controllers mezclan tres patrones distintos de manejo de errores:
1. `return redirect()->back()->withErrors([...])`
2. `return back()->with('error', '...')`
3. `throw new \Exception('...')`
4. `abort(403)`

**Solución requerida:** Estandarizar en TODOS los controllers:
- Errores de validación: usar Form Request classes (`app/Http/Requests/`)
- Errores de autorización: usar `abort(403)` o `$this->authorize()`
- Errores de operación: usar `return back()->with('error', 'mensaje')`
- Éxito: usar `return redirect()->route('ruta')->with('success', 'mensaje')`

Crear o completar los Form Request faltantes en `app/Http/Requests/` para los controllers que validan directamente en el método.

---

### REFACT-04: Usar service layer consistentemente
**Contexto:** El proyecto tiene `app/Services/Director/` e `app/Services/Investigador/` pero los controllers del módulo `AsesorSemillero/` y `DirectorSemilleros/` no usan servicios — toda la lógica está en el controller.

**Solución requerida:**
1. Crear `app/Services/AsesorSemillero/AprendizService.php` con lógica de creación y actualización de aprendices
2. Crear `app/Services/AsesorSemillero/ProyectoService.php` con lógica de creación de proyectos
3. Crear `app/Services/DirectorSemilleros/SemilleroService.php` con lógica de creación de semilleros y reasignación de líderes
4. Mover la lógica de negocio desde los controllers a estos servicios
5. Los controllers deben solo: validar input → llamar servicio → redirigir con mensaje

---

### REFACT-05: Normalizar imports no usados
**Acción:** En todos los archivos de `app/Http/Controllers/` y `app/Models/`, ejecutar una revisión de imports `use` y eliminar los que no se usan.

**Ejemplo específico:**
- `app/Http/Controllers/Admin/CatalogoController.php`: importa `QueryException` sin usar

**Herramienta:** Ejecutar `./vendor/bin/pint --test` para detectar issues de estilo, luego `./vendor/bin/pint` para corregirlos automáticamente.

---

## BLOQUE 4: LIMPIEZA DE VISTAS

### VIEW-01: Eliminar código comentado en vistas Blade
**Acción:** Buscar en `resources/views/` todos los bloques de código HTML/PHP comentados (`{{-- ... --}}` con bloques grandes) que no sean comentarios de documentación y eliminarlos.

```bash
grep -r "{{--" resources/views/ | grep -v "^Binary"
```

Mantener solo comentarios que expliquen lógica no obvia. Eliminar código comentado (código antiguo que fue reemplazado).

---

### VIEW-02: Estandarizar mensajes de éxito/error en vistas
**Problema:** Algunas vistas muestran el flash message de `success` y otras de `status` o `message`. No hay consistencia.

**Solución requerida:**
1. Revisar el layout principal (`resources/views/layouts/app.blade.php` o similar)
2. Asegurarse de que muestre tanto `session('success')` como `session('error')`
3. En todos los controllers, usar `->with('success', '...')` para éxito y `->with('error', '...')` para errores no de validación
4. Verificar que las vistas de cada módulo no tengan su propio sistema de alertas redundante

---

### VIEW-03: Completar el componente Appearance de Settings
**Archivo:** `app/Livewire/Settings/Appearance.php` + vista correspondiente

**Problema:** El componente existe pero no implementa ninguna lógica de cambio de tema.

**Solución requerida:**
1. Si no se va a implementar el cambio de tema en esta iteración, eliminar la ruta y el enlace de navegación a esta página
2. Si sí se va a implementar: agregar soporte para tema claro/oscuro guardado en `localStorage` vía Alpine.js (ya disponible con Flux/Livewire)

Decidir una opción y ejecutarla. No dejar el componente vacío y accesible.

---

### VIEW-04: Auditar y verificar todas las vistas referenciadas en includes
**Acción:** Ejecutar el siguiente análisis:
1. Buscar todos los `@include(` en `resources/views/`
2. Para cada include, verificar que el archivo `.blade.php` referenciado existe
3. Si no existe, crear la vista vacía con el markup mínimo o eliminar el include

```bash
grep -rh "@include(" resources/views/ | grep -oP "'[^']+'" | sort -u
```

Reportar cada include roto y resolverlo.

---

## BLOQUE 5: ARQUITECTURA

### ARCH-01: Crear tabla dedicada para documentos del grupo
**Problema:** `app/Http/Controllers/DirectorInvestigacion/DocumentoGrupoController.php` usa la tabla `seedling_internal_documents` para almacenar documentos del grupo de investigación. Esto es un workaround documentado con un TODO en el código.

**Solución requerida:**
1. Crear migración: `php artisan make:migration create_group_documents_table`
2. La tabla debe tener: `id`, `research_group_id` (FK), `user_id` (FK), `nombre`, `archivo`, `tipo`, `timestamps`
3. Crear modelo `GroupDocument.php` con relaciones a `ResearchGroup` y `User`
4. Actualizar `DocumentoGrupoController.php` para usar el nuevo modelo
5. Actualizar las vistas correspondientes del director
6. Crear migración de datos para mover registros existentes si los hay

---

### ARCH-02: Implementar Soft Deletes en modelos críticos
**Modelos que deben tener Soft Delete:**
- `app/Models/Project.php` — proyectos no deben eliminarse permanentemente
- `app/Models/Product.php` — productos con historial de revisión
- `app/Models/Seedling.php` — semilleros con historial

**Solución requerida por modelo:**
1. Agregar `use SoftDeletes;` al modelo
2. Crear migración que agregue la columna `deleted_at` a la tabla correspondiente
3. Actualizar los controllers de `destroy()` para usar soft delete (ya es automático con el trait)
4. Agregar scope `withTrashed()` en los lugares donde se necesite ver el historial

---

### ARCH-03: Cambiar DB_CONNECTION por defecto a MySQL
**Archivo:** `.env.example`

**Problema:** El archivo `.env.example` configura SQLite pero el sistema usa migraciones con sintaxis MySQL (`MODIFY`, `ENUM`). Esto causa error inmediato en instalaciones nuevas.

**Solución requerida:** Actualizar `.env.example`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sistema_documental
DB_USERNAME=root
DB_PASSWORD=
```
Y agregar instrucción en comentario: `# Crear base de datos en MySQL antes de ejecutar migrate`

---

### ARCH-04: Crear README.md de instalación
**Archivo a crear:** `README.md` en la raíz del proyecto

**Contenido mínimo requerido:**
1. Descripción del proyecto
2. Requisitos previos (PHP 8.2+, MySQL 8+, Node 18+, Composer 2+)
3. Pasos de instalación:
   - Clonar repositorio
   - `composer install`
   - Copiar `.env.example` a `.env`
   - Configurar base de datos MySQL
   - `php artisan key:generate`
   - Crear base de datos MySQL vacía
   - `php artisan migrate`
   - `php artisan db:seed`
   - `npm install && npm run build`
   - `php artisan serve`
4. Credenciales de acceso por defecto (referencia a `BASE_DE_DATOS.md`)
5. Estructura de roles
6. Comandos útiles de desarrollo

---

### ARCH-05: Agregar índices de base de datos faltantes
**Problema:** Las tablas con columnas de búsqueda frecuente pueden no tener índices, causando queries lentos con volumen de datos.

**Solución requerida:** Crear una migración que agregue los siguientes índices:
```php
// En users
$table->index('training_center_id');
$table->index('estado');
$table->index('numero_documento');

// En research_group_users
$table->index(['research_group_id', 'user_id']);

// En seedling_advisors
$table->index(['seedling_id', 'user_id']);

// En project_authors
$table->index(['project_id', 'user_id']);

// En group_products
$table->index(['author_id', 'estado_revision']);

// En products
$table->index(['project_id', 'estado_revision']);
```

---

## BLOQUE 6: MÓDULO INCOMPLETO — SUPER ADMINISTRADOR

### FEAT-01: Completar módulo Super Administrador
**Contexto:** El módulo `/super-admin` solo tiene dashboard y asignación de admins. El super admin necesita gestión completa.

**Funcionalidades a implementar:**

#### 6.1 Gestión de Centros de Formación
- Crear controller: `app/Http/Controllers/SuperAdmin/TrainingCenterController.php`
- Rutas en `routes/super_admin.php`: CRUD completo + toggle activo
- Vistas en `resources/views/super-admin/training-centers/`
- El super admin puede crear, editar y activar/desactivar centros

#### 6.2 Dashboard Global mejorado
- Actualizar `app/Http/Controllers/SuperAdmin/DashboardController.php`
- Agregar métricas: productos aprobados/rechazados del sistema, semilleros activos por centro, investigadores por grupo
- Vista tipo tabla con métricas por centro de formación

#### 6.3 Gestión de todos los usuarios
- El super admin debe poder ver todos los usuarios del sistema sin filtro de centro
- Reutilizar el componente Livewire `UserIndex` con scope `null` (sin filtro)

---

## ORDEN DE EJECUCIÓN RECOMENDADO

Ejecutar las tareas en este orden para evitar conflictos:

```
1. ARCH-03 (cambiar .env.example)
2. ARCH-04 (crear README.md)
3. BUG-04 (strings mágicos → enums)  ← no rompe nada, solo mejora
4. REFACT-05 (limpiar imports)        ← seguro, no rompe nada
5. VIEW-01 (limpiar comentarios)      ← seguro
6. VIEW-02 (estandarizar mensajes)
7. VIEW-04 (auditar includes)
8. BUG-01 (scope en APIs)             ← seguridad crítica
9. BUG-02 (transacciones DB)          ← integridad de datos
10. BUG-03 (validar sesión semillero)
11. BUG-05 (eliminar whereRaw 0=1)
12. BUG-07 (validación archivos)
13. BUG-08 (try-catch en Storage)
14. BUG-06 (implementar Mailable)
15. BUG-09 (typo descripccion)
16. REFACT-01 (DB::table → Eloquent)
17. REFACT-02 (extraer DashboardService)
18. REFACT-03 (estandarizar errores)
19. REFACT-04 (service layer asesor/director semilleros)
20. ARCH-01 (tabla group_documents)
21. ARCH-02 (soft deletes)
22. ARCH-05 (índices DB)
23. VIEW-03 (Appearance component)
24. FEAT-01 (módulo super admin completo)
```

---

## REGLAS DE CALIDAD PARA ESTE PROYECTO

Al ejecutar cualquiera de las tareas anteriores, cumplir siempre:

1. **No romper funcionalidad existente:** Si un cambio afecta vistas o rutas, verificar que la ruta sigue funcionando
2. **Mantener el estilo del proyecto:** PHP 8.2+, arrow functions donde aplique, match expressions para condiciones simples
3. **Usar los Enums existentes:** `EstadoEnum`, `EstadoRevisionEnum`, `RolGrupoEnum` en `app/Enums/`
4. **Usar los helpers existentes:** `TrainingCenterAccess`, `AsesorSemilleroContext`, `DirectorContext`, `InvestigadorContext`
5. **No crear nuevas dependencias** en `composer.json` sin aprobación
6. **Un commit por bloque:** Hacer commit al terminar cada BLOQUE (1-6), no mezclar cambios de distintos bloques
7. **Después de cada cambio de migración:** Verificar que `php artisan migrate:fresh --seed` completa sin errores
8. **Verificar que `php artisan route:list`** no muestra errores después de cambios en routes
