# Estado del Sistema — Antes y Después de las Correcciones
## Sistema Documental SENA GIDESTH

**Documento generado:** 2026-04-08  
**Versión base analizada:** rama `main` — commit `ee90201`  
**Referencia de correcciones:** [PROMPT_IA_REFACTORING.md](PROMPT_IA_REFACTORING.md)

---

## ÍNDICE

1. [Resumen Ejecutivo](#resumen-ejecutivo)
2. [Arquitectura General](#arquitectura-general)
3. [Seguridad y Autorización](#seguridad-y-autorización)
4. [Módulos por Rol](#módulos-por-rol)
5. [Modelos y Base de Datos](#modelos-y-base-de-datos)
6. [Servicios y Lógica de Negocio](#servicios-y-lógica-de-negocio)
7. [Vistas y Frontend](#vistas-y-frontend)
8. [Calidad de Código](#calidad-de-código)
9. [DevOps e Instalación](#devops-e-instalación)
10. [Tabla de Impacto por Corrección](#tabla-de-impacto-por-corrección)

---

## RESUMEN EJECUTIVO

### Estado actual (ANTES)
El sistema está **funcionalmente operativo** para los 7 roles principales. Todos los módulos de negocio están implementados y enrutados. Sin embargo, presenta **35 problemas identificados** distribuidos en:
- 9 bugs de seguridad y lógica
- 5 oportunidades de refactoring
- 4 mejoras de vistas
- 5 cambios de arquitectura
- 1 módulo incompleto (Super Admin)

El sistema puede **usarse en desarrollo** pero **no es seguro para producción** en su estado actual.

### Estado esperado (DESPUÉS)
Sistema production-ready con:
- Seguridad robusta en todos los endpoints
- Capa de servicios consistente
- Integridad de datos garantizada con transacciones y soft deletes
- Módulo Super Admin completo
- Código limpio y mantenible

---

## ARQUITECTURA GENERAL

### ANTES

```
┌─────────────────────────────────────────────────────────────┐
│                     RUTAS (9 archivos)                      │
│  web.php │ admin.php │ super_admin.php │ director_inv.php   │
│  director_sem.php │ lider_sem.php │ asesor_sem.php          │
│  investigador.php │ settings.php                            │
└────────────────────────┬────────────────────────────────────┘
                         │
┌────────────────────────▼────────────────────────────────────┐
│               CONTROLLERS (57 archivos)                     │
│  ┌──────────────┐  ┌─────────────────────────────────────┐  │
│  │ Admin/ (8)   │  │ AsesorSemillero/ (8)                │  │
│  │ SuperAdmin/(2)│  │ DirectorInvestigacion/ (6)          │  │
│  │ Web/ (15)    │  │ DirectorSemilleros/ (6)             │  │
│  │ Livewire/    │  │ LiderSemillero/ (8)                 │  │
│  │  Settings/(4)│  │ InvestigadorAsociado/ (6)           │  │
│  └──────────────┘  └─────────────────────────────────────┘  │
│  ⚠️ Lógica de negocio mezclada con lógica HTTP             │
│  ⚠️ Servicios solo en Director/ e Investigador/            │
└────────────────────────┬────────────────────────────────────┘
                         │ (directo, sin capa uniforme)
┌────────────────────────▼────────────────────────────────────┐
│               MODELOS (48 archivos)                         │
│  ⚠️ Sin SoftDeletes en Project, Product, Seedling          │
│  ⚠️ Typo 'descripccion' en múltiples modelos               │
│  ⚠️ Relaciones BelongsToMany no usadas (DB::table directo) │
└────────────────────────┬────────────────────────────────────┘
                         │
┌────────────────────────▼────────────────────────────────────┐
│          BASE DE DATOS MySQL (76 migraciones)               │
│  ⚠️ Sin índices en columnas de búsqueda frecuente          │
│  ⚠️ Sin tabla dedicada group_documents                     │
│  ⚠️ Documentos del grupo usan tabla seedling_internal_docs │
└─────────────────────────────────────────────────────────────┘
```

### DESPUÉS

```
┌─────────────────────────────────────────────────────────────┐
│                     RUTAS (9 archivos)                      │
│  Igual que antes + super_admin.php completo (3→9 rutas)     │
└────────────────────────┬────────────────────────────────────┘
                         │
┌────────────────────────▼────────────────────────────────────┐
│               CONTROLLERS (57+ archivos)                    │
│  Responsabilidad única: validar → llamar servicio → redirect│
│  Sin lógica de negocio directa                              │
│  + SuperAdmin/TrainingCenterController (nuevo)              │
└────────────────────────┬────────────────────────────────────┘
                         │ (vía inyección de dependencias)
┌────────────────────────▼────────────────────────────────────┐
│               SERVICIOS (8 archivos)                        │
│  Director/InvestigadorService ✅ (ya existía)               │
│  Director/RevisionProductoService ✅ (ya existía)           │
│  Director/ReporteService ✅ (ya existía)                    │
│  Investigador/ProyectoService ✅ (ya existía)               │
│  Investigador/ProductoService ✅ (ya existía)               │
│  Investigador/EvidenciaService ✅ (ya existía)              │
│  AsesorSemillero/AprendizService ✨ (nuevo)                 │
│  AsesorSemillero/ProyectoService ✨ (nuevo)                 │
│  DirectorSemilleros/SemilleroService ✨ (nuevo)             │
│  DashboardService ✨ (nuevo, reemplaza lógica en controller) │
└────────────────────────┬────────────────────────────────────┘
                         │
┌────────────────────────▼────────────────────────────────────┐
│               MODELOS (48 archivos)                         │
│  ✅ SoftDeletes en Project, Product, Seedling               │
│  ✅ Typo 'descripccion' corregido a 'descripcion'           │
│  ✅ Relaciones BelongsToMany usadas correctamente           │
│  ✅ GroupDocument modelo nuevo                              │
└────────────────────────┬────────────────────────────────────┘
                         │
┌────────────────────────▼────────────────────────────────────┐
│          BASE DE DATOS MySQL (76+3 migraciones)             │
│  ✅ Índices en users, research_group_users, etc.            │
│  ✅ Tabla group_documents dedicada                          │
│  ✅ Columnas deleted_at en Project, Product, Seedling       │
└─────────────────────────────────────────────────────────────┘
```

---

## SEGURIDAD Y AUTORIZACIÓN

### ANTES vs DESPUÉS por punto

| Punto | Antes | Después |
|-------|-------|---------|
| **Endpoints API internos** | Cualquier usuario autenticado puede consultar proyectos/autores de CUALQUIER semillero con solo conocer el ID | Solo se retornan datos si el semillero/proyecto pertenece al usuario autenticado |
| **Sesión semillero activo** | El asesor puede poner en sesión el ID de cualquier semillero del sistema | Se verifica que el asesor es miembro del semillero antes de guardarlo en sesión |
| **Redirección open redirect** | `redirect_to` en `SemilleroActivoController` acepta cualquier URL, incluso externa | Solo acepta URLs relativas del propio sistema (whitelist de rutas internas) |
| **Validación de archivos** | Inconsistente entre controllers: algunos sin validación de tipo/tamaño | Todos los controllers de upload validan tipo de archivo y máximo 10MB |
| **Archivos ejecutables** | Es posible subir archivos `.php`, `.exe`, `.sh` si se conoce la URL del endpoint | Regla `mimes:` en todos los endpoints bloquea tipos peligrosos |
| **Form Requests** | `authorize()` siempre retorna `true` en algunos Requests | `authorize()` verifica el permiso Spatie correspondiente |
| **Storage exceptions** | Fallos de disco o permisos causan errores 500 sin mensaje al usuario | Todos los `Storage::disk()` envueltos en try-catch con mensaje de error controlado |

### Detalle: Endpoint API antes/después

**ANTES — `apiProyectosPorSemillero($semillero_id)`:**
```php
// Sin ninguna verificación de acceso
public function apiProyectosPorSemillero($semillero_id)
{
    $projectIds = DB::table('project_seedlings')
        ->where('seedling_id', $semillero_id)  // cualquier ID funciona
        ->pluck('project_id');
    ...
}
```

**DESPUÉS:**
```php
public function apiProyectosPorSemillero($semillero_id)
{
    // Verificar que este semillero pertenece al asesor autenticado
    $semillero = Seedling::findOrFail($semillero_id);
    $esMiembro = DB::table('seedling_advisors')
        ->where('seedling_id', $semillero->id)
        ->where('user_id', auth()->id())
        ->exists();
    if (!$esMiembro) {
        return response()->json(['error' => 'No autorizado'], 403);
    }
    // ... resto del método
}
```

---

## MÓDULOS POR ROL

### ROL: SUPER ADMINISTRADOR

| Funcionalidad | Antes | Después |
|---------------|-------|---------|
| Dashboard global | ✅ Métricas básicas (usuarios, grupos, semilleros, centros) | ✅ + métricas por centro de formación en tabla, tendencias |
| Asignación admins a centros | ✅ Funcional | ✅ Sin cambios |
| **Gestión de centros de formación** | ❌ No existe en el módulo super admin | ✅ CRUD completo: crear, editar, toggle activo |
| **Ver todos los usuarios** | ❌ No existe en el módulo super admin | ✅ Livewire `UserIndex` sin filtro de centro |
| Rutas disponibles | 3 rutas (dashboard + 2 de linkAdmin) | 9+ rutas |

**Rutas antes (`routes/super_admin.php`):**
```php
GET  /super-admin/dashboard
GET  /super-admin/centros-administradores
POST /super-admin/centros-administradores
```

**Rutas después:**
```php
GET    /super-admin/dashboard
GET    /super-admin/centros-administradores
POST   /super-admin/centros-administradores
GET    /super-admin/centros                     ← nuevo
GET    /super-admin/centros/create              ← nuevo
POST   /super-admin/centros                     ← nuevo
GET    /super-admin/centros/{id}/edit           ← nuevo
PUT    /super-admin/centros/{id}                ← nuevo
PATCH  /super-admin/centros/{id}/toggle-activo  ← nuevo
GET    /super-admin/usuarios                    ← nuevo
```

---

### ROL: ADMINISTRADOR DEL SISTEMA

| Funcionalidad | Antes | Después |
|---------------|-------|---------|
| Creación de usuario | ✅ Funciona, pero User y Person se crean sin transacción | ✅ Envuelto en `DB::transaction()` |
| Envío de credenciales | ❌ Código comentado `// Mail::to()...` — el usuario NO recibe email | ✅ Mailable implementado + email enviado al crear usuario |
| Asignación de rol | ✅ Funcional | ✅ Sin cambios |
| Toggle usuario | ✅ Funcional | ✅ Sin cambios |
| Dashboard | ⚠️ Usa `whereRaw('0 = 1')` como anti-patrón | ✅ Usa `User::none()` con comentario explicativo |
| Estados en queries | ⚠️ `->where('estado', 'activo')` string literal | ✅ `->where('estado', EstadoEnum::Activo)` |

**Detalle — Creación usuario antes/después:**

| | Antes | Después |
|--|-------|---------|
| Integridad de datos | User puede quedar huérfano si Person falla | Ambos se crean o ninguno (transacción) |
| Notificación | Usuario NO recibe sus credenciales | Usuario recibe email con email + contraseña + link |
| Email clase | No existe `UserCredentialsMail` | `app/Mail/UserCredentialsMail.php` creado |
| Vista email | No existe | `resources/views/emails/user-credentials.blade.php` creado |

---

### ROL: DIRECTOR DE INVESTIGACIÓN

| Funcionalidad | Antes | Después |
|---------------|-------|---------|
| Dashboard | ✅ Funcional | ✅ Sin cambios |
| Gestión investigadores | ✅ Funcional, con transacciones en service | ✅ Sin cambios |
| Revisión productos | ✅ Funcional | ✅ Sin cambios |
| Documentos del grupo | ⚠️ Usa tabla `seedling_internal_documents` (workaround) | ✅ Usa tabla dedicada `group_documents` |
| Macroproyectos | ✅ Funcional | ✅ Sin cambios |
| Reportes | ✅ Funcional | ✅ Sin cambios |

**Detalle — Documentos del grupo antes/después:**

| | Antes | Después |
|--|-------|---------|
| Tabla usada | `seedling_internal_documents` (compartida con semilleros) | `group_documents` (dedicada) |
| Integridad | Documentos del grupo aparecen mezclados con semilleros en queries | Separación total de documentos por entidad |
| Modelo | No existe `GroupDocument` | `app/Models/GroupDocument.php` creado |
| Migración | TODO en comentario del código | Migración + modelo + controller actualizados |

---

### ROL: DIRECTOR DE SEMILLEROS

| Funcionalidad | Antes | Después |
|---------------|-------|---------|
| Dashboard | ✅ Funcional | ✅ Sin cambios |
| Creación semilleros | ✅ Funcional (sin service) | ✅ Usa `SemilleroService::crear()` |
| Reasignación líder | ✅ Funcional (sin service) | ✅ Usa `SemilleroService::reasignarLider()` |
| Envío credenciales líder | ⚠️ Usa `Mail::raw()` sin Mailable | ✅ Usa `LiderCredentialsMail` Mailable |
| Lógica en controller | ⚠️ Controller hace todo (100+ líneas por método) | ✅ Controller delega a `SemilleroService` |

---

### ROL: LÍDER DE SEMILLERO

| Funcionalidad | Antes | Después |
|---------------|-------|---------|
| Dashboard | ✅ Funcional | ✅ Sin cambios |
| Info semillero | ✅ Funcional | ✅ Sin cambios |
| Integrantes | ✅ Funcional | ✅ Sin cambios |
| Asesores | ✅ Funcional | ✅ Sin cambios |
| Productos | ✅ Funcional | ✅ Sin cambios |
| Archivos | ✅ Funcional — sin validación de tipo/tamaño | ✅ Validación: max 10MB, mimes explícitos |
| Doc Interna | ✅ Funcional — sin validación de tipo/tamaño | ✅ Validación: max 10MB, mimes explícitos |
| API autores | ⚠️ Sin verificación de ownership | ✅ Con verificación de que el proyecto es del semillero del líder |

---

### ROL: ASESOR DE SEMILLERO

| Funcionalidad | Antes | Después |
|---------------|-------|---------|
| Dashboard | ✅ Funcional — usa strings mágicos `'pendiente'` | ✅ Usa `EstadoRevisionEnum::Pendiente` |
| Mis semilleros | ✅ Funcional | ✅ Sin cambios |
| **Semillero activo** | ⚠️ Cualquier asesor puede activar cualquier semillero del sistema | ✅ Solo puede activar semilleros propios |
| Aprendices | ✅ Funcional — lógica en controller | ✅ Usa `AprendizService` |
| Proyectos | ✅ Funcional — usa `DB::table('project_seedlings')` | ✅ Usa `$semillero->projects()` Eloquent |
| Productos | ✅ Funcional — lógica en controller | ✅ Usa `ProyectoService` |
| **API proyectos** | ❌ Sin scope: retorna proyectos de cualquier semillero | ✅ Retorna solo proyectos del asesor autenticado |
| **API autores** | ❌ Sin scope: retorna autores de cualquier proyecto | ✅ Retorna solo autores de proyectos del asesor |
| Evidencias — upload | ⚠️ Sin try-catch en operaciones Storage | ✅ Con manejo de excepciones |
| Reportes | ✅ Funcional — sin checks de permiso en export | ✅ Con verificación de permiso `reportes.exportar_pdf_excel` |

---

### ROL: INVESTIGADOR ASOCIADO

| Funcionalidad | Antes | Después |
|---------------|-------|---------|
| Dashboard | ✅ Funcional | ✅ Sin cambios |
| Proyectos | ✅ Funcional | ✅ Sin cambios |
| Productos | ✅ Funcional | ✅ Sin cambios |
| Bandeja semilleros | ⚠️ Formalización parcialmente implementada | ✅ Flujo completo de formalización documentado y corregido |
| Evidencias | ⚠️ Sin try-catch en Storage | ✅ Con manejo de excepciones |
| Reportes | ✅ Funcional | ✅ Sin cambios |

---

## MODELOS Y BASE DE DATOS

### Comparación de modelos críticos

#### Modelo `Project`

| Aspecto | Antes | Después |
|---------|-------|---------|
| Soft Delete | ❌ No existe — `destroy()` borra permanentemente | ✅ `use SoftDeletes;` + columna `deleted_at` |
| Typo | ❌ `'descripccion'` en fillable | ✅ `'descripcion'` corregido |
| Estado en queries | ❌ Comparado con string `'activo'` | ✅ Comparado con `EstadoEnum::Activo` |
| Impacto de borrado | Los proyectos eliminados desaparecen del historial | Los proyectos eliminados son recuperables y aparecen en historial |

#### Modelo `Product`

| Aspecto | Antes | Después |
|---------|-------|---------|
| Soft Delete | ❌ No existe — borrado permanente | ✅ `use SoftDeletes;` + columna `deleted_at` |
| `assigned_investigator_user_id` | ⚠️ Columna usada en código pero ausente del `fillable` | ✅ Documentado o agregado al fillable con comentario |
| Historial de revisión | Al borrar se pierde el historial de aprobaciones | Con soft delete el historial de `GroupProductReview` se conserva |

#### Modelo `Seedling`

| Aspecto | Antes | Después |
|---------|-------|---------|
| Soft Delete | ❌ No existe — borrado permanente | ✅ `use SoftDeletes;` + columna `deleted_at` |
| Typo | ❌ `'descripccion'` en fillable | ✅ `'descripcion'` corregido |
| Relaciones BelongsToMany | ⚠️ Definidas en el modelo pero los controllers usan `DB::table()` | ✅ Los controllers usan las relaciones Eloquent |

### Migraciones nuevas requeridas

| Migración | Propósito |
|-----------|-----------|
| `create_group_documents_table` | Tabla dedicada para documentos del grupo de investigación |
| `add_deleted_at_to_projects_table` | Soft delete en proyectos |
| `add_deleted_at_to_products_table` | Soft delete en productos |
| `add_deleted_at_to_seedlings_table` | Soft delete en semilleros |
| `add_indexes_to_frequent_queries` | Índices en columnas de búsqueda frecuente |

### Índices de base de datos

| | Antes | Después |
|--|-------|---------|
| `users.training_center_id` | ❌ Sin índice | ✅ Indexado |
| `users.estado` | ❌ Sin índice | ✅ Indexado |
| `users.numero_documento` | ❌ Sin índice | ✅ Indexado |
| `research_group_users.(research_group_id, user_id)` | ❌ Sin índice compuesto | ✅ Índice compuesto |
| `seedling_advisors.(seedling_id, user_id)` | ❌ Sin índice compuesto | ✅ Índice compuesto |
| `project_authors.(project_id, user_id)` | ❌ Sin índice compuesto | ✅ Índice compuesto |
| `group_products.(author_id, estado_revision)` | ❌ Sin índice | ✅ Indexado |
| `products.(project_id, estado_revision)` | ❌ Sin índice | ✅ Indexado |

**Impacto estimado de índices:** Con más de 1000 productos en el sistema, las consultas del dashboard del director pasan de escaneos de tabla completa a búsquedas indexadas. Mejora de rendimiento esperada: 10x-100x en tablas con datos reales.

---

## SERVICIOS Y LÓGICA DE NEGOCIO

### Mapa de servicios antes/después

| Servicio | Antes | Después | Controllers que lo usan |
|----------|-------|---------|------------------------|
| `Director/InvestigadorService` | ✅ Existe | ✅ Sin cambios | `DirectorInvestigacion/InvestigadorController` |
| `Director/RevisionProductoService` | ✅ Existe | ✅ Sin cambios | `DirectorInvestigacion/ProductoRevisionController` |
| `Director/ReporteService` | ✅ Existe | ✅ Sin cambios | `DirectorInvestigacion/ReporteGrupoController` |
| `Investigador/ProyectoService` | ✅ Existe | ✅ Sin cambios | `InvestigadorAsociado/ProyectoController` |
| `Investigador/ProductoService` | ✅ Existe | ✅ Sin cambios | `InvestigadorAsociado/ProductoController` |
| `Investigador/EvidenciaService` | ✅ Existe | ✅ Sin cambios | `InvestigadorAsociado/EvidenciaController` |
| `AsesorSemillero/AprendizService` | ❌ No existe (lógica en controller) | ✅ Creado | `AsesorSemillero/AprendizController` |
| `AsesorSemillero/ProyectoService` | ❌ No existe (lógica en controller) | ✅ Creado | `AsesorSemillero/ProyectoController` |
| `DirectorSemilleros/SemilleroService` | ❌ No existe (lógica en controller) | ✅ Creado | `DirectorSemilleros/SemilleroController`, `LiderSemilleroController` |
| `DashboardService` | ❌ No existe (lógica en DashboardController) | ✅ Creado | `Admin/DashboardController` |

### Patrón controller antes/después

**ANTES — Controller con lógica de negocio:**
```php
// AsesorSemillero/AprendizController::store() - ~80 líneas
public function store(StoreAprendizRequest $request)
{
    $validated = $request->validated();
    $semillero = $this->getSemilleroDelAsesor($request);
    
    // Lógica de negocio mezclada con HTTP
    $nombreCompleto = $this->splitNombreCompleto($validated['nombre_completo']);
    
    DB::transaction(function () use ($validated, $semillero, $nombreCompleto) {
        $user = User::create([...]);
        $person = Person::create([...]);
        
        // Vincular al semillero
        DB::table('seedling_members')->insert([...]);
        
        // Asignar role
        $user->assignRole('aprendiz_semillero');
    });
    
    return redirect()->route('asesor.aprendices.index')
        ->with('success', 'Aprendiz registrado');
}
```

**DESPUÉS — Controller delegando a servicio:**
```php
// AsesorSemillero/AprendizController::store() - ~15 líneas
public function store(StoreAprendizRequest $request)
{
    $semillero = AsesorSemilleroContext::getSemilleroActivo();
    
    $this->aprendizService->crear(
        $request->validated(),
        $semillero,
        auth()->user()
    );
    
    return redirect()->route('asesor.aprendices.index')
        ->with('success', 'Aprendiz registrado exitosamente.');
}
```

---

## VISTAS Y FRONTEND

### Comparación general

| Aspecto | Antes | Después |
|---------|-------|---------|
| **Mensajes flash** | Inconsistente: algunos usan `session('success')`, otros `session('status')`, otros `session('message')` | Estandarizado: `session('success')` para éxito, `session('error')` para error en todos los controllers y vistas |
| **Código comentado** | Bloques HTML comentados (`{{-- ... --}}`) con código antiguo en múltiples vistas | Eliminado todo código comentado, solo quedan comentarios explicativos |
| **Componente Appearance** | Livewire component vacío accesible desde `/settings/appearance` sin funcionalidad | Eliminado del menú de navegación y ruta desactivada hasta implementación real |
| **Includes rotos** | Posibles `@include()` apuntando a archivos inexistentes | Auditados y todos los includes apuntan a archivos existentes |
| **Layouts** | Layout principal muestra solo `session('success')` | Layout muestra `success` y `error` con estilos diferenciados |

### Componente Appearance — antes/después

| | Antes | Después |
|--|-------|---------|
| Estado | Componente registrado, ruta activa, enlace en menú | Ruta desactivada, enlace removido del menú de settings |
| Funcionalidad | Ninguna — carga una vista vacía | N/A (desactivado) |
| UX | El usuario ve una página en blanco sin explicación | El usuario no ve la opción en el menú |

---

## CALIDAD DE CÓDIGO

### Typos y strings mágicos

| Problema | Archivos afectados | Antes | Después |
|----------|-------------------|-------|---------|
| `descripccion` (typo) | `app/Models/Project.php`, `app/Models/Seedling.php`, `app/Http/Requests/AsesorSemillero/StoreEvidenciaRequest.php` | `'descripccion'` en fillable y validaciones | `'descripcion'` corregido en todos los archivos |
| Estados como string | 15+ archivos en `app/Http/Controllers/` | `->where('estado', 'activo')` | `->where('estado', EstadoEnum::Activo->value)` |
| Estados de revisión como string | 8+ archivos | `->where('estado_revision', 'pendiente')` | `->where('estado_revision', EstadoRevisionEnum::Pendiente->value)` |
| `whereRaw('0 = 1')` | `Admin/DashboardController.php` | Anti-patrón de autorización | `User::none()` con comentario explicativo |

### Imports no usados

| Archivo | Import problemático | Acción |
|---------|--------------------|-|
| `Admin/CatalogoController.php` | `use Illuminate\Database\QueryException;` | Eliminado |
| Varios controllers | Imports de modelos no usados | Eliminados via `pint` |

### Errores de consultas

| Patrón | Antes | Después |
|--------|-------|---------|
| `DB::table('project_seedlings')` en AsesorSemillero | Consulta raw directa a tabla pivot | `$semillero->projects()->pluck('projects.id')` |
| `DB::table('seedling_members')` en controllers | Consulta raw directa a tabla pivot | `$semillero->members()->pluck('users.id')` |
| `DB::table('seedling_advisors')` en controllers | Consulta raw directa a tabla pivot | `$semillero->advisors()->pluck('users.id')` |

### Manejo de errores — estandarización

| Patrón | Antes (mezclado) | Después (estandarizado) |
|--------|-----------------|------------------------|
| Error de validación | `$request->validate([...])` directo en método | `StoreXxxRequest` Form Request class |
| Error de autorización | `abort(403)` / `throw new Exception` / nada | `abort(403)` en todos los casos |
| Error de operación | Mezcla de `withErrors` / `with('error')` / excepción | `return back()->with('error', 'mensaje')` |
| Operación exitosa | Mezcla de `with('success')` / `with('status')` / `with('message')` | `return redirect()->route(...)->with('success', 'mensaje')` |

---

## DEVOPS E INSTALACIÓN

### Archivo `.env.example`

| | Antes | Después |
|--|-------|---------|
| DB_CONNECTION | `sqlite` | `mysql` |
| DB_HOST | `127.0.0.1` | `127.0.0.1` |
| DB_PORT | No definido | `3306` |
| DB_DATABASE | `database/database.sqlite` | `sistema_documental` |
| Comentario guía | No existe | `# Crear base de datos en MySQL antes de migrate` |
| Impacto | Error inmediato al hacer `migrate` con sintaxis MySQL | Setup funciona en primer intento |

### Documentación de instalación

| | Antes | Después |
|--|-------|---------|
| `README.md` | ❌ No existe | ✅ Creado con pasos completos de instalación |
| Guía de roles | ❌ No documentado | ✅ En `README.md` con referencia a `BASE_DE_DATOS.md` |
| Credenciales de prueba | ❌ No documentado | ✅ En `BASE_DE_DATOS.md` |
| Análisis de requerimientos | ❌ No documentado | ✅ En `ANALISIS_REQUERIMIENTOS.md` |
| Modelo de negocio | ❌ No documentado | ✅ En `MODELO_NEGOCIO.md` |

---

## TABLA DE IMPACTO POR CORRECCIÓN

Esta tabla muestra el impacto real de cada corrección del [PROMPT_IA_REFACTORING.md](PROMPT_IA_REFACTORING.md):

| ID | Tarea | Tipo | Criticidad | Impacto en Producción | Esfuerzo |
|----|-------|------|-----------|----------------------|---------|
| BUG-01 | Scope en APIs internas | Seguridad | 🔴 Crítico | Fuga de datos entre centros | Bajo |
| BUG-02 | Transacciones en creación de usuario | Integridad | 🔴 Crítico | Datos corruptos en BD | Bajo |
| BUG-03 | Validar sesión semillero activo | Seguridad | 🔴 Crítico | Acceso no autorizado a datos | Bajo |
| BUG-04 | Strings mágicos → Enums | Calidad | 🟡 Medio | Bugs silenciosos si enum cambia | Bajo |
| BUG-05 | Eliminar `whereRaw('0 = 1')` | Calidad | 🟡 Medio | Anti-patrón confuso | Bajo |
| BUG-06 | Implementar Mailable credenciales | Funcional | 🟠 Alto | Usuarios no reciben credenciales | Medio |
| BUG-07 | Validar archivos en upload | Seguridad | 🔴 Crítico | Archivos maliciosos subibles | Bajo |
| BUG-08 | Try-catch en Storage | Estabilidad | 🟠 Alto | Errores 500 sin mensaje | Bajo |
| BUG-09 | Corregir typo `descripccion` | Funcional | 🟡 Medio | Campos no se guardan correctamente | Bajo |
| REFACT-01 | `DB::table()` → Eloquent | Calidad | 🟢 Bajo | Sin impacto funcional | Medio |
| REFACT-02 | Extraer `DashboardService` | Calidad | 🟢 Bajo | Sin impacto funcional | Medio |
| REFACT-03 | Estandarizar manejo de errores | Calidad | 🟡 Medio | UX inconsistente actual | Alto |
| REFACT-04 | Service layer consistente | Calidad | 🟡 Medio | Mantenibilidad futura | Alto |
| REFACT-05 | Limpiar imports | Calidad | 🟢 Bajo | Sin impacto funcional | Bajo |
| VIEW-01 | Limpiar código comentado | Limpieza | 🟢 Bajo | Sin impacto funcional | Bajo |
| VIEW-02 | Estandarizar mensajes flash | UX | 🟡 Medio | Mensajes de error no visibles | Bajo |
| VIEW-03 | Completar/eliminar Appearance | UX | 🟢 Bajo | Página en blanco actual | Bajo |
| VIEW-04 | Auditar includes Blade | Estabilidad | 🟡 Medio | Posibles errores 500 en vistas | Bajo |
| ARCH-01 | Tabla `group_documents` | Arquitectura | 🟠 Alto | Datos mezclados en BD | Alto |
| ARCH-02 | Soft Deletes | Arquitectura | 🟠 Alto | Pérdida permanente de datos | Medio |
| ARCH-03 | `.env.example` MySQL | DevOps | 🔴 Crítico | Error en primera instalación | Bajo |
| ARCH-04 | Crear `README.md` | Documentación | 🟢 Bajo | Sin impacto funcional | Bajo |
| ARCH-05 | Índices de BD | Performance | 🟠 Alto | Queries lentos con datos reales | Bajo |
| FEAT-01 | Módulo Super Admin completo | Funcional | 🟠 Alto | Rol sin funcionalidad completa | Alto |

### Leyenda de criticidad
- 🔴 **Crítico:** Debe corregirse antes de cualquier uso en producción
- 🟠 **Alto:** Debe corregirse antes del primer release
- 🟡 **Medio:** Debe corregirse en el siguiente sprint
- 🟢 **Bajo:** Mejora de calidad, planificable a futuro

---

## ESTADO FUNCIONAL FINAL ESPERADO

Después de aplicar todas las correcciones del [PROMPT_IA_REFACTORING.md](PROMPT_IA_REFACTORING.md):

```
✅ Sistema production-ready
✅ 7 roles completamente funcionales (incluyendo Super Admin completo)
✅ Seguridad: endpoints scoped, archivos validados, sesiones validadas
✅ Integridad: transacciones DB, soft deletes, sin datos huérfanos
✅ Notificaciones: usuarios reciben credenciales por email al ser creados
✅ Arquitectura limpia: service layer consistente en todos los módulos
✅ Código limpio: sin typos, sin imports muertos, sin strings mágicos
✅ Performance: índices en columnas de búsqueda frecuente
✅ Documentación: README, modelo de negocio, análisis de requerimientos
✅ Instalación: .env.example correcto, setup funciona en primer intento
```

**Conteo de archivos que cambian:**
- Archivos modificados: ~45
- Archivos nuevos: ~15 (servicios, mailables, migraciones, modelo GroupDocument)
- Archivos eliminados: 0 (los 8 de basura ya fueron eliminados en sesión anterior)

**Migraciones nuevas:** 5
**Nuevos servicios:** 4
**Nuevos mailables:** 2
**Nuevas vistas:** 2 (email templates)
**Nuevos controllers:** 1 (SuperAdmin/TrainingCenterController)
