# REPORTE DE CONTEXTO TÉCNICO DEL PROYECTO

Generado: 2026-09-02 (reescritura completa — la versión anterior, de 2026-05-26, describía la arquitectura de 7 roles previa al rediseño de agosto de 2026)
Repositorio: sistema_documental
Rama en el momento de escribir esto: `fix/superadmin-eliminar-usuarios-y-roles-muertos` (con el trabajo de las sesiones de agosto — rediseño de roles y sistema multi-rol — sin commitear)

---

## 📋 INFORMACIÓN GENERAL DEL PROYECTO

**Nombre:** GIDESTH (Sistema Documental) — `.env.example` usa `APP_NAME=SIGESI` como nombre de marca en producción
**Tipo:** Aplicación web educativa para gestión de semilleros de investigación y documentación
**Base de datos:** MySQL (`sistema_documental`)

---

## 🔧 STACK TECNOLÓGICO

### Backend
| Componente | Versión | Descripción |
|-----------|---------|------------|
| **Laravel** | 12.51.0 | Framework PHP |
| **PHP** | 8.2.28 | Versión de PHP |
| **Livewire** | 4.0 | Componentes reactivos sin JavaScript explícito |
| **Flux** | 2.9.0 | Sistema de componentes UI para Livewire |
| **Laravel Fortify** | 1.30 | Stack de autenticación |
| **Spatie Permission** | 6.24 | Gestión de roles y permisos |
| **Resend** (`resend/resend-laravel`) | 1.4 | Envío de correo transaccional |

### Frontend
| Componente | Versión | Descripción |
|-----------|---------|------------|
| **Tailwind CSS** | 4.0.7 | Framework CSS |
| **Vite** | 7.0.4 | Bundler (con laravel-vite-plugin) |
| **Axios** | 1.7.4 | Cliente HTTP |
| **Chart.js** | 4.5.1 | Gráficos (uso puntual — varias cards con Chart.js fueron eliminadas por no tener función real, ver `docs/SESION_2026-08-13_REDISENO_ROLES.md`, BUG-023) |

### Librerías adicionales
| Paquete | Versión | Propósito |
|---------|---------|----------|
| **barryvdh/laravel-dompdf** | 3.1 | Generación de PDFs |
| **phpoffice/phpspreadsheet** | 5.5 | Exportación a Excel |
| **laravel/tinker** | 2.10.1 | REPL interactivo |
| **laravel-shift/blueprint** | 2.13 (dev) | Generador de migraciones/modelos |

### Testing
| Componente | Versión | Descripción |
|-----------|---------|------------|
| **PHPUnit** | 11.5.3 | Framework de testing unitario/integración |
| **Mockery** | 1.6 | Mocking y stubbing |
| **Faker** | 1.23 | Generación de datos de prueba |
| **laravel-test-assertions** | 2.8 | Aserciones específicas de Laravel |
| **collision** | 8.6 | Mejor reporte de errores |

---

## 🗂️ ESTRUCTURA DEL PROYECTO

### Directorios principales en `app/`
```
app/
├── Actions/              # Acciones de negocio (Fortify)
├── Concerns/              # Traits compartidos (validaciones, reglas)
├── Enums/                 # Enumeraciones (ver tabla abajo)
├── Http/
│   ├── Controllers/       # Controladores organizados por rol
│   │   ├── Admin/                  # administrador_sistema
│   │   ├── SuperAdmin/             # super_administrador
│   │   ├── DirectorSemilleros/     # director_semilleros
│   │   ├── LiderSemillero/         # lider_semillero
│   │   ├── LiderProyecto/          # lider_proyecto
│   │   ├── Coinvestigador/         # co_investigador
│   │   ├── Web/                    # catálogos compartidos entre roles
│   │   └── Settings/               # perfil, password, cuenta (todos los roles)
│   ├── Middleware/        # Middleware custom (ver tabla abajo)
│   ├── Requests/          # Form Requests organizados por módulo
│   └── Responses/         # LoginResponse (Fortify, inicializa rol activo)
├── Livewire/
│   ├── Actions/           # Logout
│   ├── Admin/Users/        # UserCreate, UserEdit, UserIndex
│   ├── Auth/               # Login
│   ├── Settings/           # Appearance, TwoFactor
│   └── Shared/             # UserNameDisplay
├── Mail/                   # Clases de notificación/email
├── Models/                 # ~28 modelos Eloquent (ver lista abajo)
├── Policies/                # ⚠️ vacío (ver nota en "Patrones no encontrados")
├── Providers/               # Service providers
├── Services/
│   ├── Admin/               # NotificacionService, UserCreationService
│   └── LiderSemillero/      # ProyectoLiderService, RevisionEvidenciaService
└── Support/                 # Clases utilitarias centrales (ver tabla abajo)
```

### Directorios en `database/`
- **migrations/** — 13+ migraciones nuevas solo en agosto de 2026 (rediseño de roles + multi-rol), todas `Ran`, ninguna pendiente.
- **factories/** — Factories para datos de prueba.
- **seeders/** — `RolesAndPermissionsSeeder` (6 roles, 11 módulos de permisos), seeders de catálogos.

### Directorios en `resources/`
```
resources/views/
├── admin/
├── super-admin/
├── director_semilleros/
├── lider_semillero/
├── lider_proyecto/
├── co_investigador/
├── components/            # app-layout.blade.php (sidebar + selector "Mis roles")
├── dashboard/
├── emails/
├── flux/
├── layouts/
├── livewire/
├── partials/
└── settings/
```

> Ya no existen `asesor_semillero/`, `director_investigacion/` ni `investigador/` — se eliminaron junto con esos roles en agosto de 2026.

---

## 🏛️ PATRONES ARQUITECTURALES DETECTADOS

### 1. **Service Layer**
Ubicación: `app/Services/`. Hoy solo cubre `Admin/` (creación de usuarios, notificaciones) y `LiderSemillero/` (creación de proyecto, revisión de evidencias en 2 etapas). Los servicios de `Director/` e `Investigador/` del diseño anterior fueron eliminados junto con esos roles — el resto de controladores por rol (`DirectorSemilleros`, `LiderProyecto`, `Coinvestigador`, `SuperAdmin`) implementan su lógica directamente en el controlador.

### 2. **Actions (Fortify)**
Ubicación: `app/Actions/Fortify/`. Acciones específicas de autenticación y gestión de usuarios (`CreateNewUser`, etc.).

### 3. **Concerns (Traits)**
Ubicación: `app/Concerns/`.
- `ProfileValidationRules` — reglas de validación de perfil (incluye CVLAC, Nivel de Formación, Fecha de Vinculación para `co_investigador`).
- `PasswordValidationRules` — reglas de contraseña.
- `StreamsPublicStorageFiles` — descarga estandarizada de archivos de `storage/app/public` (reemplazó las distintas implementaciones de "Ver en el navegador"/"Descargar" que existían por módulo).

### 4. **Enumeraciones**
Ubicación: `app/Enums/` — 10 enums:

| Enum | Casos |
|------|-------|
| `EstadoEnum` | Activo, Inactivo |
| `EstadoRevisionEnum` | Pendiente, EnRevision, Aprobado, Rechazado |
| `GeneroEnum` | Masculino, Femenino, PrefieroNoDecirlo |
| `ModalidadEnum` | Virtual, Presencial |
| `NivelFormacionEnum` | Tecnico, Tecnologo, Pregrado, Posgrado |
| `TipoDocumentoEnum` | DocumentoIdentidad, CedulaCiudadana, Pasaporte, CedulaExtrangera |
| `TipoEvidenciaEnum` | Desarrollo, Formulacion, Ejecucion, ProductoFinal |
| `TipoParticipacionEnum` | Origen, Aliado, Cooperacion |
| `TipoProyectoOrigenEnum` | SGPS, CapacidadInstalada, Formativa, IniciativaCentro, Articulacion, Semilleros, Otro |
| `RolGrupoEnum` | Director, InvestigadorLider, InvestigadorAsociado, Integrante — ⚠️ **sin ninguna referencia en el código fuera de su propio archivo**; era del flujo de `ResearchGroup` ya eliminado, candidato a limpieza |

`JornadaEnum` fue eliminado (`BUG-20260813-042`, junto con el campo "Jornada" del programa de formación).

### 5. **Políticas de Autorización (Policies)**
Ubicación: `app/Policies/` — **actualmente vacío**. Las policies del diseño anterior (`DirectorPolicy`, `GroupProductPolicy`, `GrupoPolicy`, `ProductoPolicy`, `ProyectoPolicy`) se eliminaron junto con los modelos `ResearchGroup`/`Product`/`GroupProduct`. La autorización hoy se hace con permisos Spatie (`$this->authorize('modulo.accion')`) y validaciones manuales de ownership/centro en cada controlador (p. ej. `ensureDelCentro()`, `ensureOwnedLiderProyecto()`), no con clases `Policy`.

### 6. **Middleware Customizado**
Ubicación: `app/Http/Middleware/`

| Middleware | Función |
|---|---|
| `EnsureUserIsActive` | Bloquea usuarios con `estado = inactivo` |
| `PreventBackHistory` | Cabeceras no-cache contra el botón atrás |
| `RedirectDirectorToModule` | Redirige `/dashboard` según el rol activo |
| `RequireTrainingCenter` | Exige `training_center_id` en roles que lo requieren |
| `EnsureActiveRole` (alias `active_role`) | Aísla el acceso a un grupo de rutas al **rol activo** de sesión, no solo al rol asignado — núcleo del sistema multi-rol |

### 7. **Control de Acceso por Centro (Multi-tenancy simplificado)**
Ubicación: `app/Support/TrainingCenterAccess.php`

```php
public const CENTRO_BOUND_ROLE_NAMES = [
    'administrador_sistema',
    'director_semilleros',
    'lider_semillero',
    'lider_proyecto',
];
```

`co_investigador` es el **único rol global** — no está en esta lista y nunca requiere `training_center_id`. Además de `scopeUserQueryForList()` (listados, excluye al propio usuario), existe `scopeUserQueryForMetrics()` (conteos/dashboards, no excluye al usuario) — separados desde `BUG-20260813-035` para no romper métricas al reutilizar el scope de listados.

### 8. **Sistema de rol activo / multi-rol**
Ubicación: `app/Support/ActiveRoleContext.php`, `app/Support/RoleAssignmentMatrix.php`, `app/Support/RoleModuleLinks.php`.

- `ActiveRoleContext` — resuelve/cambia el rol activo en sesión (`current()`, `switchTo()`, `initializeForUser()`, `isPrimary()`).
- `RoleAssignmentMatrix` — universo de roles adicionales permitidos por pantalla de creación/edición (nunca permite inyectar `super_administrador`).
- `RoleModuleLinks` — prioridad de rol para login/redirección, labels legibles y URL del dashboard de cada rol.

### 9. **Componentes Livewire Reactivos**
Ubicación: `app/Livewire/`. Solo el módulo de usuarios de Admin (`UserCreate`, `UserEdit`, `UserIndex`) usa Livewire para CRUD — el resto del sistema (todos los módulos por rol) es controladores + Blade tradicional, no Livewire.

### 10. **Form Requests**
Ubicación: `app/Http/Requests/`, organizados por módulo/catálogo (`StoreTrainingProgramRequest`, etc.). Los Form Requests del diseño anterior ligados a `Product`/`GroupProduct`/`ResearchGroup` fueron eliminados.

---

## 🔐 SISTEMA DE AUTENTICACIÓN Y AUTORIZACIÓN

### Autenticación
- **Driver:** Laravel Fortify.
- **Verificación de email:** Implementada (`MustVerifyEmail`).
- **2FA:** Soporte con `TwoFactorAuthenticatable`, QR code.
- **Correo transaccional:** Resend (reemplazó la configuración de Mailtrap del entorno de desarrollo anterior).

### Autorización — Roles (Spatie Permission)
El proyecto define exactamente **6 roles** (`RolesAndPermissionsSeeder`):
```
- super_administrador     # Acceso global, sin training_center_id
- administrador_sistema   # Admin de un centro (ligado a centro)
- director_semilleros     # Gestiona semilleros de su centro (ligado a centro)
- lider_semillero         # Lidera un semillero (ligado a centro)
- lider_proyecto          # Lidera un proyecto (ligado a centro)
- co_investigador         # Colabora en proyectos de cualquier centro — rol global
```

11 módulos de permisos: `usuarios`, `catalogos`, `semilleros`, `proyectos`, `aprendices`, `productos`, `evidencias`, `documentos`, `archivos_semillero`, `minciencias`, `reportes`.

> **Nota de autorización abierta (`BUG-20260813-057`, sin resolver por decisión explícita):** las rutas de `external-advisors`, `minciencias-typologies`, `minciencias-subcategories`, `training-programs`, `training-centers` en el grupo compartido `admin.` de `routes/web.php` no tienen middleware `role:` — cualquier usuario autenticado puede acceder sin importar su rol.

### Model User
**Ubicación:** `app/Models/User.php`

```php
- HasRoles (Spatie Permission), HasFactory, Notifiable, TwoFactorAuthenticatable
- MustVerifyEmail

// fillable clave
- training_center_id, created_by_user_id, primary_role_name, estado

// Relaciones clave
- trainingCenter(): BelongsTo
- createdBy(): BelongsTo (self — quién creó esta cuenta, regla de "solo el creador edita")
- person(): HasOne
```

`created_by_user_id` respalda la regla de propiedad (`UserOwnershipAccess::canManage()`): en general solo quien creó una cuenta puede gestionarla, con excepción explícita para cuentas huérfanas (creadas antes de que existiera esta columna, `BUG-20260813-033`).

---

## 📊 BASE DE DATOS

### Motor
- **Driver:** MySQL en producción/desarrollo; **SQLite en memoria** en tests (`phpunit.xml`).
- **Conexión dev:** 127.0.0.1:3306.

### Tablas principales

**Catálogo:**
- `departments`, `cities`, `training_centers`, `training_programs`, `training_program_types`
- `entity_positions`, `linkage_types`, `investigation_types`, `minciencias_typologies`, `minciencias_subcategories`
- `research_lines`, `technological_lines`, `thematic_areas`, `project_modalities`
- `catalogos` (modelo genérico de tipo libre — ver nota de posible código muerto en `MODELO_NEGOCIO.md`)

**Dominio:**
- `users`, `people`
- `seedlings`, `projects`, `macro_projects`
- `minciencias_products`, `minciencias_product_files`

**Relacionales:**
- `project_learners` (aprendices — datos libres, no usuarios)
- `project_authors` (vincula usuarios `co_investigador` a proyectos)
- `project_evidences` (4 tipos, con columnas de revisión de 2 etapas y de notificación "punto rojo": `visto_por_lider_proyecto_at`, `visto_por_lider_semillero_at`)
- `seedling_advisors`, `seedling_files`, `seedling_internal_documents`

**Tablas eliminadas en el rediseño de agosto de 2026:** `research_groups`, `research_group_users`, `products`, `product_authors`, `product_evidences`, `group_products`, `group_product_reviews`, `project_seedlings`, `seedling_members`, `knowledge_areas`, `knowledge_grand_areas`, `training_records`.

### Sesiones y Cache
- **SESSION_DRIVER:** database
- **CACHE_STORE:** database
- **QUEUE_CONNECTION:** database (configurada, sin Jobs definidos — igual que antes)

---

## 🧪 TESTING

### Configuración
**Archivo:** `phpunit.xml` — SQLite en memoria (`DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`).

### Tests existentes — **80 archivos, 294 tests pasando** (807 assertions, ~86s)
```
tests/
├── Feature/
│   ├── Admin/                 (1 archivo)
│   ├── Auth/                  (3 archivos)
│   ├── Coinvestigador/        (1 archivo)
│   ├── LiderProyecto/         (1 archivo)
│   ├── LiderSemillero/        (1 archivo)
│   ├── Regression/            (69 archivos — 1 por cada BUG-20260813-NNN corregido)
│   ├── DashboardTest.php
│   └── ExampleTest.php
└── Unit/
    └── ExampleTest.php
```

Cada test de `Regression/` corresponde a un bug/reforma con ID secuencial documentado en `docs/CHANGELOG-sesion-2026-08.md` / `docs/FEAT-20260830-001-multirol.md`, con docblock explicando causa raíz y solución — convención establecida en `docs/SESION_2026-08-13_REDISENO_ROLES.md`.

### Ejecución
```bash
composer test          # PHPUnit + Pint
php artisan test        # Solo PHPUnit
composer lint            # Solo Pint
```

---

## 🎨 FRONTEND Y CONVENCIONES

### Estilos
Tailwind CSS 4.0, utility-first. Color primario institucional `#39A900` (verde SENA) usado de forma consistente en formularios y botones.

### Vistas Blade
Organización por rol: `resources/views/{admin,super-admin,director_semilleros,lider_semillero,lider_proyecto,co_investigador}/`.
Layout compartido: `resources/views/components/app-layout.blade.php` — sidebar con badges de notificación "punto rojo" y el selector **"Mis roles"** (solo visible con ≥2 roles asignados).

### Vite Config
Sin cambios respecto al reporte anterior: Tailwind vía `@tailwindcss/vite`, `laravel-vite-plugin`.

---

## 📝 CONVENCIONES DE CÓDIGO DETECTADAS

Sin cambios respecto al reporte anterior — siguen vigentes:
- Type hinting completo (PHP 8.2+).
- Naming: modelos PascalCase, métodos camelCase, propiedades snake_case, constantes SNAKE_CASE.
- Enums tipados en casts de modelo.
- PSR-12 vía Laravel Pint (`composer lint`).
- Separadores ASCII de sección: `// ─────────────────────────────────────────────`.
- Comentarios mínimos, solo para lógica no-obvia (varios docblocks de clase explican decisiones de diseño no evidentes — p. ej. por qué `MincienciasProduct` no se vincula a ningún semillero).

---

## 🔍 EJEMPLO DE PATRÓN: TrainingCenterAccess

Sin cambios de fondo respecto al reporte anterior, salvo la lista de roles ligados a centro (ver arriba) y la separación `scopeUserQueryForList()` / `scopeUserQueryForMetrics()`. Sigue siendo el archivo más importante del proyecto para entender el aislamiento de datos.

---

## 📦 DEPENDENCIAS CLAVE

Sin cambios de fondo respecto al reporte anterior, con la adición de `resend/resend-laravel` (correo) y `laravel-shift/blueprint` (dev, generador de scaffolding).

---

## 🚀 CONFIGURACIÓN DE DESARROLLO

### Scripts disponibles
```bash
composer setup          # Setup inicial (install, migrate, npm install, build)
composer dev             # Inicia servidor + queue + npm dev (concurrently)
composer test            # Tests + linting
composer lint             # Pint linting
npm run build             # Build para producción
npm run dev               # Dev server Vite con HMR
```

### Variables de entorno clave (`.env.example`, valores de producción)
```
APP_NAME=SIGESI
APP_ENV=production
APP_DEBUG=false

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

MAIL_MAILER=smtp
MAIL_SCHEME=smtps
MAIL_PORT=465
```

---

## 🎯 PATRONES NO ENCONTRADOS (Información negativa)

Sin cambios respecto al reporte anterior — siguen sin usarse: Repositories, Events & Listeners, Jobs, Observers, API Resources, DTOs, Sanctum/Passport. Se agrega:

- ❌ **Policies activas** — la carpeta `app/Policies/` existe pero está vacía; la autorización se resuelve con permisos Spatie + checks manuales de ownership en cada controlador, no con clases `Policy` registradas.

---

## 💡 OBSERVACIONES CLAVE

1. **Dos rediseños grandes desde la versión anterior de este reporte:** colapso de 7 a 6 roles (agosto, `SESION_2026-08-13_REDISENO_ROLES.md`) y sistema de rol activo/multi-rol (`FEAT-20260830-001-multirol.md`), ambos sin commitear en la rama actual al momento de escribir esto.
2. **`co_investigador` es el único rol sin centro fijo** — puede colaborar en proyectos de varios centros y gestiona productos Minciencias personales revisados por el centro que él mismo elige.
3. **El flujo de "producto" se dividió en dos flujos independientes:** evidencias de proyecto (dentro de un semillero, 2 etapas para producto final) y productos Minciencias personales (fuera de cualquier semillero, 1 etapa, revisado por un admin de centro). Ya no existe el flujo único "asesor → líder → investigador → director" del diseño anterior.
4. **Suite de regresión disciplinada:** 69 de los 80 archivos de test son de regresión, cada uno atado a un ID de bug documentado — reduce el riesgo de reintroducir bugs ya corregidos, pero también significa que la cobertura sigue el orden en que se encontraron los bugs, no una cobertura sistemática por módulo.
5. **Restos de código muerto identificados pero no limpiados:** `RolGrupoEnum` (sin referencias), el modelo/CRUD genérico `Catalogo` (sin FKs de otras tablas apuntándole).
6. **Hueco de autorización documentado y pendiente:** el grupo de catálogos compartidos en `routes/web.php` no tiene middleware `role:` (`BUG-20260813-057`).

---

**Fin del reporte**
