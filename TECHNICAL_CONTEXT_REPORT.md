# REPORTE DE CONTEXTO TÉCNICO DEL PROYECTO

Generado: 2026-05-26  
Repositorio: sistema_documental  
Rama actual: feature/aldana_refactoring

---

## 📋 INFORMACIÓN GENERAL DEL PROYECTO

**Nombre:** GIDESTH (Sistema Documental)  
**Tipo:** Aplicación web educativa para gestión de semilleros de investigación y documentación  
**URL Local:** http://localhost  
**Base de datos:** MySQL (sistema_documental)

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

### Frontend
| Componente | Versión | Descripción |
|-----------|---------|------------|
| **Tailwind CSS** | 4.0.7 | Framework CSS |
| **Vite** | 7.0.4 | Bundler (con laravel-vite-plugin) |
| **Axios** | 1.7.4 | Cliente HTTP |

### Librerías adicionales
| Paquete | Versión | Propósito |
|---------|---------|----------|
| **barryvdh/laravel-dompdf** | 3.1 | Generación de PDFs |
| **phpoffice/phpspreadsheet** | 5.5 | Exportación a Excel |
| **laravel/tinker** | 2.10.1 | REPL interactivo |

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
├── Concerns/             # Traits compartidos (validaciones, reglas)
├── Enums/                # Enumeraciones (EstadoEnum, TipoDocumentoEnum, etc.)
├── Http/
│   ├── Controllers/      # Controladores organizados por módulo
│   │   ├── Admin/
│   │   ├── AsesorSemillero/
│   │   ├── DirectorInvestigacion/
│   │   ├── DirectorSemilleros/
│   │   ├── InvestigadorAsociado/
│   │   ├── LiderSemillero/
│   │   ├── SuperAdmin/
│   │   └── Web/
│   ├── Middleware/       # Middleware custom (EnsureUserIsActive, RedirectDirectorToModule, etc.)
│   ├── Requests/         # Form Requests organizados por módulo
│   └── Responses/
├── Livewire/             # Componentes Livewire
│   ├── Actions/
│   ├── Admin/
│   │   └── Users/        # Components de gestión de usuarios
│   ├── Auth/
│   ├── Settings/
│   └── Shared/
├── Mail/                 # Clases de notificación/email
├── Models/               # Modelos Eloquent (~40+ modelos)
├── Policies/             # Políticas de autorización
│   ├── DirectorPolicy
│   ├── GroupProductPolicy
│   ├── GrupoPolicy
│   ├── ProductoPolicy
│   └── ProyectoPolicy
├── Providers/            # Service providers
├── Services/             # Clases de servicios
│   ├── Director/
│   └── Investigador/
└── Support/              # Clases utilitarias
    ├── TrainingCenterAccess.php  # Control de acceso a centros de formación
    └── RoleModuleLinks.php        # Mapeo roles-módulos
```

### Directorios en `database/`
- **migrations/** - 50+ migraciones para tablas de dominio
- **factories/** - Factory para generar datos de prueba
- **seeders/** - Seeders para datos iniciales

### Directorios en `resources/`
```
resources/views/
├── admin/
├── asesor_semillero/
├── components/
├── dashboard/
├── director_investigacion/
├── director_semilleros/
├── flux/
├── investigador/
├── layouts/
├── lider_semillero/
├── livewire/              # Vistas para componentes Livewire
├── partials/
├── settings/
├── super-admin/
└── emails/
```

---

## 🏛️ PATRONES ARQUITECTURALES DETECTADOS

### 1. **Service Layer**
Ubicación: `app/Services/`

Ejemplo: `Director/`, `Investigador/` contienen lógica de negocio reutilizable.

**Patrón:**
```php
// app/Services/Director/GestionarGrupo.php
public function crearGrupo(array $data): ResearchGroup {
    // lógica compleja
}
```

### 2. **Actions (Fortify)**
Ubicación: `app/Actions/Fortify/`

Acciones específicas de autenticación y gestión de usuarios.

### 3. **Concerns (Traits)**
Ubicación: `app/Concerns/`

Reutilización de validaciones:
- `ProfileValidationRules` - reglas de validación para perfiles
- `PasswordValidationRules` - reglas para contraseñas

**Patrón:**
```php
class UserController extends Controller {
    use ProfileValidationRules;
    
    public function store(Request $request) {
        $validated = $request->validate($this->profileRules());
    }
}
```

### 4. **Enumeraciones**
Ubicación: `app/Enums/`

Estados y tipos como Enums tipados:
- `EstadoEnum` (Activo, Inactivo)
- `TipoDocumentoEnum`
- `GeneroEnum`
- `JornadaEnum`
- `ModalidadEnum`
- `RolGrupoEnum`
- `TipoParticipacionEnum`
- `TipoProyectoOrigenEnum`

### 5. **Políticas de Autorización (Policies)**
Ubicación: `app/Policies/`

Control de acceso basado en recursos:
- `DirectorPolicy`
- `GroupProductPolicy`
- `GrupoPolicy`
- `ProductoPolicy`
- `ProyectoPolicy`

**Patrón:**
```php
// DirectorPolicy.php
public function update(User $user, Director $director): bool {
    return $user->id === $director->user_id;
}
```

### 6. **Middleware Customizado**
Ubicación: `app/Http/Middleware/`

- `EnsureUserIsActive` - Verifica usuario activo
- `PreventBackHistory` - Previene botón atrás
- `RedirectDirectorToModule` - Redirige según rol
- `RequireTrainingCenter` - Requiere centro de formación

### 7. **Control de Acceso por Centro (Multi-tenancy simplificado)**
Ubicación: `app/Support/TrainingCenterAccess.php`

Implementa lógica de restricción por `training_center_id`:
- **Super administrador**: ve todos los centros
- **Admin de centro**: restringido a su centro
- **Roles ligados a sede**: `director_semilleros`, `lider_semillero`, `asesor_semillero`, `director_investigacion`, `investigador_asociado`

### 8. **Componentes Livewire Reactivos**
Ubicación: `app/Livewire/`

Ejemplo: `Admin/Users/UserIndex.php`
```php
class UserIndex extends Component {
    use WithPagination;
    
    #[Url]
    public string $search = '';
    
    public function render() {
        $users = User::when($this->search, ...)
                     ->paginate(15);
        return view('livewire.admin.users.user-index', compact('users'));
    }
}
```

**Características:**
- Atributos `#[Url]` para sincronizar estado con URL
- `WithPagination` para paginación
- Métodos públicos como acciones (ej: `toggleEstado()`)

### 9. **Form Requests**
Ubicación: `app/Http/Requests/`

Validación centralizada en clases FormRequest:
- `StorePersonRequest`
- `StoreCityRequest`
- `StoreDepartmentRequest`
- Organizados por módulo

---

## 🔐 SISTEMA DE AUTENTICACIÓN Y AUTORIZACIÓN

### Autenticación
- **Driver:** Laravel Fortify
- **Verificación de email:** Implementada (`MustVerifyEmail`)
- **2FA:** Soporte con `TwoFactorAuthenticatable`
- **Remember me:** Disponible

### Autorización - Roles (Spatie Permission)
El proyecto define estos roles principales:
```
- super_administrador          # Acceso global
- administrador_sistema        # Admin de un centro
- director_semilleros         # Gestiona semilleros
- lider_semillero             # Lidera un semillero
- asesor_semillero            # Asesora semilleros
- director_investigacion      # Gestiona investigación
- investigador_asociado       # Participa en investigación
- admin (legacy)              # Retrocompatibilidad
```

### Model User
**Ubicación:** `app/Models/User.php`

**Características:**
```php
- HasRoles (Spatie Permission)
- HasFactory
- Notifiable
- TwoFactorAuthenticatable
- MustVerifyEmail

// Relaciones clave
- trainingCenter(): BelongsTo
- person(): HasOne
- researchGroups(): BelongsToMany
- seedlings(): BelongsToMany
- createdSeedlings(), ledSeedlings(): HasMany
- createdProjects(), projectAuthors(): HasMany
- groupProducts(): HasMany

// Scopes
- scopeActive()  // Solo usuarios activos

// Helpers
- getNameAttribute()           // Nombre desde Person
- initials()                   // Iniciales para avatares
- routeNotificationForMail()   // Email prioritario
```

---

## 📊 BASE DE DATOS

### Motor
- **Driver:** MySQL (configurable en .env)
- **Base de datos:** sistema_documental
- **Conexión:** 127.0.0.1:3306 (usuario root sin contraseña en dev)

### Tablas principales (50+ migraciones)

**Catálogo:**
- departments, cities, training_centers, training_records, training_programs
- entity_positions, linkage_types, investigation_types, minciencias_typologies

**Dominio:**
- users, people (información personal)
- research_groups, seedlings, projects, grupo_productos
- research_lines, technological_lines, thematic_areas, knowledge_areas

**Relacionales:**
- research_group_users, seedling_members
- project_authors, product_authors, product_evidence

### Sesiones y Cache
- **SESSION_DRIVER:** database (sesiones en BD)
- **CACHE_STORE:** database (caché en BD)
- **QUEUE_CONNECTION:** database (colas en BD)

---

## 🧪 TESTING

### Configuración
**Archivo:** `phpunit.xml`

```xml
<testsuites>
    <testsuite name="Unit">
        <directory>tests/Unit</directory>
    </testsuite>
    <testsuite name="Feature">
        <directory>tests/Feature</directory>
    </testsuite>
</testsuites>

<!-- Testing usa SQLite en memoria -->
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

### Tests existentes (11 archivos)
**Feature Tests:**
- `Auth/AuthenticationTest.php`
- `Auth/PasswordConfirmationTest.php`
- `Auth/PasswordResetTest.php`
- `Auth/TwoFactorChallengeTest.php`
- `DashboardTest.php`
- `ExampleTest.php`
- `Settings/PasswordUpdateTest.php`
- `Settings/ProfileUpdateTest.php`
- `Settings/TwoFactorAuthenticationTest.php`

**Unit Tests:**
- `ExampleTest.php`

### Ejecución
```bash
composer test          # Ejecuta todos los tests
composer test:lint     # Solo linting con Pint
```

---

## 🎨 FRONTEND Y CONVENCIONES

### Estilos
- **Framework CSS:** Tailwind CSS 4.0
- **Estrategia:** Utility-first con clases compuestas

Ejemplo de componente:
```html
<div class="sgd-card bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
    <p class="text-sm font-medium text-slate-500">{{ $label }}</p>
</div>
```

### Componentes Livewire
**Convenciones:**
- Ubicación: `app/Livewire/[Modulo]/[Nombre].php`
- Vistas: `resources/views/livewire/[modulo]/[vista].blade.php`
- Traits: `WithPagination` para listados
- Atributos: `#[Url]` para parámetros en URL

### Vistas Blade
**Organización por módulo:**
- `resources/views/admin/`
- `resources/views/director_semilleros/`
- `resources/views/lider_semillero/`
- `resources/views/asesor_semillero/`
- `resources/views/investigador/`

**Layouts compartidos:**
- `resources/views/layouts/app.blade.php`
- `resources/views/components/` (componentes Blade reutilizables)

### Vite Config
```javascript
// vite.config.js
export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
```

---

## 📝 CONVENCIONES DE CÓDIGO DETECTADAS

### Type Hinting
✅ **Completamente tipado**
```php
public function index(Request $request): View {}
public function create(int $userId, ?User $user = null): Collection {}
```

### Naming Conventions
- **Modelos:** PascalCase (User, ResearchGroup, Seedling)
- **Métodos:** camelCase (toggleEstado, routeNotificationForMail)
- **Propiedades:** snake_case (training_center_id, primer_nombre)
- **Constantes:** SNAKE_CASE (CENTRO_BOUND_ROLE_NAMES)

### Enums
✅ **Uso extenso de Enums para tipos y estados**
```php
enum EstadoEnum: string {
    case Activo = 'activo';
    case Inactivo = 'inactivo';
}

// En casts del modelo:
protected function casts(): array {
    return [
        'estado' => EstadoEnum::class,
        'tipo_documento' => TipoDocumentoEnum::class,
    ];
}
```

### Formatting
- **PSR-12** con Laravel Pint (linting via `composer lint`)
- **Indentación:** 4 espacios
- **Línea máxima:** Se respeta en archivos existentes

### Docblocks
- Uso de docblocks en métodos públicos (rara vez multilinea)
- Type hints en docblocks para claridad en tipos complejos

### Comentarios
- Mínimos, solo para lógica no-obvia
- Separadores ASCII para secciones: `// ─────────────────────`

---

## 🔍 EJEMPLO DE PATRÓN: TrainingCenterAccess

**Ubicación:** `app/Support/TrainingCenterAccess.php`

Este archivo es central para entender la filosofía del proyecto:

```php
final class TrainingCenterAccess {
    // Roles que requieren un centro de formación asignado
    const CENTRO_BOUND_ROLE_NAMES = [
        'director_semilleros', 'lider_semillero', 'asesor_semillero',
        'director_investigacion', 'investigador_asociado',
    ];
    
    // Métodos estáticos para control de acceso
    public static function isSuperAdmin(?User $user): bool
    public static function scopedToTrainingCenter(?User $user): bool
    public static function validateCentroBoundRoleAssignment(...)
    public static function scopeUserQueryForList(Builder $query, ?User $user): Builder
    public static function centersForSelect(?User $user): Collection
    public static function rolesForUserForm(?User $auth): Collection
}
```

**Uso típico:**
```php
// En controladores/Livewire
$users = TrainingCenterAccess::scopeUserQueryForList(
    User::with(['person', 'roles']),
    auth()->user()
)->paginate(15);
```

---

## 📦 DEPENDENCIAS CLAVE

### Production
- ✅ `laravel/framework` - Core
- ✅ `livewire/livewire` - Componentes reactivos
- ✅ `livewire/flux` - UI components
- ✅ `spatie/laravel-permission` - Roles & permisos
- ✅ `laravel/fortify` - Autenticación
- ✅ `barryvdh/laravel-dompdf` - PDFs
- ✅ `phpoffice/phpspreadsheet` - Excel
- ✅ `laravel/tinker` - REPL

### Development
- ✅ `phpunit/phpunit` - Testing
- ✅ `laravel/pint` - Code formatting
- ✅ `laravel/sail` - Docker dev environment
- ✅ `mockery/mockery` - Mocking

---

## 🚀 CONFIGURACIÓN DE DESARROLLO

### Scripts disponibles
```bash
composer setup          # Setup inicial (install, migrate, npm install, build)
composer dev           # Inicia servidor + queue + npm dev (concurrently)
composer test          # Tests + linting
composer lint          # Pint linting
npm run build          # Build para producción
npm run dev            # Dev server Vite con HMR
```

### Variables de entorno clave (.env)
```
APP_NAME=GIDESTH
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sistema_documental
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io  # Configurado para Mailtrap
```

---

## 🎯 PATRONES NO ENCONTRADOS (Información negativa)

Estos patrones **NO** se usan en el proyecto:

- ❌ **Repositories** - Se consulta directamente con Eloquent
- ❌ **Events & Listeners** - No hay eventos de dominio
- ❌ **Jobs & Queues** - Queue está configurada pero no hay Jobs definidos
- ❌ **Observers** - No hay observadores automáticos
- ❌ **API Resources** - No hay respuestas JSON tipadas (es aplicación Livewire/Blade)
- ❌ **DTOs** - No hay Data Transfer Objects explícitos
- ❌ **Sanctum/Passport** - Solo Fortify para autenticación web
- ❌ **Seeders complejos** - Solo estructura base

---

## 💡 OBSERVACIONES CLAVE

1. **Multi-tenancy simplificado:** El campo `training_center_id` actúa como tenant, con lógica centralizada en `TrainingCenterAccess`

2. **Arquitectura modular:** Controladores, Livewire y Vistas organizadas por rol/módulo (Admin, DirectorSemilleros, etc.)

3. **Type Safety:** PHP 8.2+ con tipos completos, Enums para dominios, Spatie Permission para RBAC

4. **Reactividad sin frontend:** Livewire 4.0 + Flux proporciona UX moderna sin necesidad de framework JS separado

5. **Formatos mixtos:** Genera PDFs y Excel para reportes/exportaciones

6. **Trait-based validation:** Reutilización de reglas de validación mediante Concerns/Traits

7. **Scope-based queries:** Uso extenso de `when()` y scopes para reutilización de lógica de filtrado

---

## 🛠️ SKILLS RECOMENDADAS PARA CLAUDE CODE

Basándose en el contexto técnico detectado:

### Nivel ALTO (Esencial)
1. **laravel-refactoring** - Para mejoras de código en modelos y controladores
2. **livewire-components** - Para crear/mejorar componentes Livewire
3. **database-migrations** - Para modificaciones de esquema
4. **laravel-testing** - Para escribir/reparar tests
5. **permissions-rbac** - Para gestionar roles y permisos con Spatie

### Nivel MEDIO (Muy Útil)
6. **tailwind-styling** - Para ajustes CSS con Tailwind
7. **eloquent-queries** - Para optimización de queries
8. **blade-templating** - Para mejorar vistas
9. **form-validation** - Para refinar validaciones
10. **pdf-excel-export** - Para reportes con DomPDF y PHPSpreadsheet

### Nivel BAJO (Contextual)
11. **api-routes** (si se expande API)
12. **job-queues** (si se activan colas asincrónicas)
13. **email-notifications** (si se implementan más emails)

---

## 🤖 AGENTES RECOMENDADOS PARA CLAUDE CODE

### Agentes especializados sugeridos

1. **Agent: Data Modeler**
   - Uso: Entender relaciones Eloquent complejas
   - Trigger: "Analizar estructura de relaciones en Users/ResearchGroups"

2. **Agent: Permission Architect**
   - Uso: Diseñar políticas de acceso y roles
   - Trigger: "¿Cómo debería estructura los permisos para...?"

3. **Agent: Migration Expert**
   - Uso: Crear migraciones sin romper datos
   - Trigger: "Necesito agregar una columna a la tabla users sin migración rollback"

4. **Agent: Livewire Specialist**
   - Uso: Crear componentes Livewire complejos
   - Trigger: "Crear un componente Livewire que haga..."

5. **Agent: Test Writer**
   - Uso: Escribir tests para nuevas features
   - Trigger: "Escribe tests para..."

6. **Agent: Performance Analyst**
   - Uso: Optimizar queries N+1 y relaciones Eloquent
   - Trigger: "Analizar performance de esta query"

7. **Agent: Security Auditor**
   - Uso: Validar permisos y autenticación
   - Trigger: "¿Es segura esta implementación de...?"

---

## 📖 RECURSOS INTERNOS

**Archivos clave a estudiar:**
- `app/Support/TrainingCenterAccess.php` - Control de acceso multi-tenant
- `app/Models/User.php` - Modelo raíz con todas las relaciones
- `app/Http/Controllers/Admin/DashboardController.php` - Patrón de controlador avanzado
- `app/Livewire/Admin/Users/UserIndex.php` - Componente Livewire con paginación
- `app/Concerns/ProfileValidationRules.php` - Trait de validaciones reutilizable
- `phpunit.xml` - Configuración de testing
- `vite.config.js` - Configuración del bundler

---

## ✅ CHECKLIST PARA CONFIGURACIÓN INICIAL

- [ ] Laravel 12.51.0 soportado por IDE
- [ ] PHP 8.2+ linting activado
- [ ] Spatie Permission documentation bookmarked
- [ ] Livewire 4.0 documentation disponible
- [ ] Tailwind CSS IntelliSense instalado en VS Code
- [ ] PHPUnit runner configurado en IDE
- [ ] MySQL conectado y running
- [ ] `composer install && npm install` ejecutados
- [ ] `.env` configurado con credenciales
- [ ] `php artisan migrate` ejecutado
- [ ] `npm run dev` y `php artisan serve` funcionando

---

**Fin del reporte**
