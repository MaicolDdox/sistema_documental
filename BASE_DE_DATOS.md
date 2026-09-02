# Datos Cargados en Base de Datos
## Sistema Documental SENA - GIDESTH

**Regenerado:** 2026-09-02 — reescrito completo contra `database/seeders/*.php` actuales (6 roles). La versión anterior, de 2026-04-08, describía los 7 roles y catálogos del diseño previo al rediseño de agosto de 2026.
**Comando para cargar:** `php artisan db:seed`

---

## ✅ El seeding roto ya se corrigió

`database/seeders/DatabaseSeeder.php` llamaba a `ResearchGroupSeeder::class`, que referenciaba `App\Models\ResearchGroup` — **modelo eliminado** en el rediseño de roles de agosto. Eso hacía que `php artisan db:seed` fallara justo después de crear roles/permisos, antes de llegar a los catálogos. **Ya se quitó `ResearchGroupSeeder` de la cadena y se eliminó el archivo** — el orden actual es:

```
DepartmentSeeder → CitySeeder → TrainingCenterSeeder → RolesAndPermissionsSeeder
    → SuperAdminSeeder
    → [8 seeders de catálogos]
```

## SIN USUARIOS DE PRUEBA EN EL SEEDING POR DEFECTO

El proyecto **ya no incluye un `UserSeeder`** con cuentas de prueba (nombres, emails `@sena.edu.co`, contraseña compartida `Password123!`): ese archivo se eliminó a propósito para no publicar datos de usuarios — aunque fueran ficticios — en el repositorio público de GitHub, que además queda expuesto en cada deploy automático a producción.

`php artisan db:seed` hoy solo crea:
- Departamentos, ciudades y centros de formación (catálogos geográficos).
- Roles y permisos (`RolesAndPermissionsSeeder`).
- **Un único super administrador bootstrap** (`SuperAdminSeeder`, ver abajo) — necesario para poder entrar al sistema por primera vez.
- Los catálogos de dominio (líneas, áreas, cargos, tipos de vinculación, Minciencias).

Si necesitas usuarios de prueba adicionales (director de semilleros, líder de semillero, líder de proyecto, co-investigador) para probar el sistema en **local**, créalos manualmente desde la interfaz (con el super admin bootstrap) o con un seeder/script propio que **no** se versione en git (por ejemplo, agregándolo a `.gitignore`).

### Super Administrador bootstrap (`SuperAdminSeeder`)
Único usuario que crea el seeding por defecto. Configurable por variables de entorno — **cámbialas en cualquier entorno que no sea tu máquina local**:

| Variable de entorno | Valor por defecto si no se define |
|---|---|
| `SUPER_ADMIN_EMAIL` | `superadmin@sena.edu.co` |
| `SUPER_ADMIN_PASSWORD` | `Password123!` |
| `SUPER_ADMIN_DOCUMENT` | `900000001` |

⚠️ Si se despliega a producción sin definir estas 3 variables en el `.env` del servidor, el sistema queda con un super administrador de credenciales públicas y conocidas. Definirlas es un paso obligatorio antes de cualquier `db:seed` en un entorno real.

---

## ROLES Y PERMISOS

### Roles del sistema (6 roles)

| Rol | Descripción | ¿Requiere centro? |
|-----|-------------|:---:|
| `super_administrador` | Acceso total — sin permisos Spatie asignados, controlado por middleware de rol, no por permisos finos | No |
| `administrador_sistema` | Gestión de un centro de formación | Sí |
| `director_semilleros` | Coordinación de semilleros del centro | Sí |
| `lider_semillero` | Coordinación de un semillero específico | Sí |
| `lider_proyecto` | Ejecución de un proyecto específico | Sí |
| `co_investigador` | Colabora en proyectos de cualquier centro y gestiona sus productos Minciencias personales | No — rol global |

### Permisos por módulo (81 permisos totales, 11 módulos)

| Módulo | Cantidad |
|---|:---:|
| `usuarios` | 8 |
| `catalogos` | 4 |
| `semilleros` | 12 |
| `proyectos` | 10 |
| `aprendices` | 8 |
| `productos` | 12 |
| `minciencias` | 4 |
| `evidencias` | 7 |
| `documentos` | 3 |
| `archivos_semillero` | 3 |
| `reportes` | 10 |

`usuarios.*` implementa una **matriz de creación exclusiva**: `usuarios.crear_director_semilleros`, `usuarios.crear_co_investigador`, `usuarios.crear_lider_semillero`, `usuarios.crear_lider_proyecto` — cada rol solo puede crear el rol inmediatamente debajo de él en la jerarquía, nunca "cualquier rol".

### Permisos asignados por rol

| Rol | Permisos asignados | Nota |
|---|:---:|---|
| `super_administrador` | 0 | Acceso por middleware de rol (`role:super_administrador`), no por permisos Spatie |
| `administrador_sistema` | 36 | |
| `director_semilleros` | 42 | El rol con más permisos — "administrador operativo" de los semilleros de su centro |
| `lider_semillero` | 19 | |
| `lider_proyecto` | 0 | ⚠️ Sin permisos Spatie asignados — el seeder trae un comentario "por ahora solo existe como rol asignable, sin permisos propios"; la autorización de este rol hoy depende solo del middleware `role:lider_proyecto` + `active_role:lider_proyecto` en `routes/lider_proyecto.php`, no de permisos finos |
| `co_investigador` | 0 | ⚠️ Mismo caso que `lider_proyecto` — sin permisos Spatie, solo middleware de rol |

---

## CENTROS DE FORMACIÓN (`TrainingCenterSeeder`)

| Código | Nombre | Departamento | Ciudad |
|--------|--------|-------------|--------|
| 9527 | Centro de la Industria, la Empresa y los Servicios | Huila | Neiva |
| 9116 | Centro de Formación Agroindustrial | Huila | Campoalegre |

## DEPARTAMENTOS Y CIUDADES

- `DepartmentSeeder`: **34 departamentos** de Colombia (lista completa).
- `CitySeeder`: **1123 municipios** de Colombia, cada uno vinculado a su departamento por nombre.

---

## CATÁLOGOS SEMBRADOS

### Líneas de Investigación (6) — `LineasInvestigacionesSeeder`
Producción Agropecuaria · Desarrollo Agroindustrial de Base Tecnológica · Empresarismo e Inteligencia de Mercados de Base Tecnológica · Gestión Ambiental y Aprovechamiento Sostenible de los Recursos Naturales · TIC Aplicadas al Desarrollo Sostenible · Innovación y Transformación Educativa

### Líneas Tecnológicas (6) — `LineasTecnologicasSeeder`
Línea de Economía Popular y Campesina · Línea de Materiales y Biotecnología · Línea de Usuario, Comercialización y Logística · Línea de Producción y Transformación · Línea de TICs e Inteligencia Artificial · Línea de Sociedad, Cultura y Pedagogía

### Áreas Temáticas (7) — `AreasTematicasSeeder`
Agrícola · Agroindustrial · Desarrollo de Software · Pecuaria · Administrativo · Ambiental · Pedagogía

### Modalidades de Proyecto (4) — `ModalidadesProyectosSeeder`
Capacidad Instalada · Recursos Internos SENA · Recursos Externos Convenios · Otros

> Nota: estos nombres son distintos del `ModalidadEnum` (`virtual`/`presencial`) usado en `training_programs` — son catálogos independientes que comparten el nombre "modalidad" por casualidad.

### Tipos de Investigación (5) — `TiposInvestigacionesSeeder`
Investigación Aplicada · Investigación Formativa · Desarrollo Tecnológico · Investigación Exploratoria · Innovación

### Tipo de Vinculación (3) — `LinkageTypesSeeder`
| Nombre | Descripción |
|--------|-------------|
| Planta | Personal vinculado a la planta del centro de formación |
| Contratista | Vinculado mediante contrato de prestación de servicios |
| Otros | Otro tipo de vinculación no contemplado en las anteriores |

Reemplazó por completo la lista anterior de 9 tipos (`BUG-20260813-040`).

### Cargo / Posición (13) — `CargosEntidadesSeeder`
Investigador(a) SENNOVA · Instructor(a) · Experto(a) Tecnoparque · Facilitador(a) Tecnoacademia · Administrativo · Apoyo Técnico Tecnoparque · Dinamizador Extensionismo Tecnológico · Dinamizador SENNOVA · Dinamizador Tecnoacademia · Líder Grupo de investigación · Líder Semillero de Investigación · Servicios Tecnológicos · Otro:

Reemplazó la lista anterior de 15 cargos (`BUG-20260813-044`); se usa en el campo "Cargo/Posición" del perfil de todos los usuarios.

### Tipos de Programa (3) y Programas de Formación (2) — `TrainingProgramsSeeder`
| Tipo de Programa |
|---|
| Tecnólogo |
| Técnico |
| Especialización Tecnológica |

| Programa | Tipo | Modalidad | Estado |
|---|---|---|---|
| Análisis y Desarrollo de Software (ADSO) | Tecnólogo | Presencial | Activo |
| Gestión Administrativa | Tecnólogo | Virtual | Activo |

> Los campos "Ficha" y "Jornada" que existían en el diseño anterior fueron eliminados por completo del programa de formación (`BUG-20260813-042`) — ya no aparecen en este seeder ni en el formulario.

### Tipologías y Subcategorías Minciencias (4 tipologías, 14 subcategorías) — `MincienciasSeeder`

| Tipología | Código | Subcategorías |
|---|---|---|
| Generación de Nuevo Conocimiento | GNC | Artículos de investigación · Libros resultado de investigación · Capítulos de libro |
| Apropiación Social del Conocimiento | ASC | Estrategias de comunicación · Eventos científicos · Circulación de conocimiento |
| Desarrollo Tecnológico e Innovación | DTI | Software · Plantas piloto · Prototipos · Productos empresariales industriales |
| Formación de Recursos Humanos | FRH | Tesis de doctorado · Trabajos de maestría · Trabajos de pregrado · Cursos de corta duración |

⚠️ A diferencia de los demás seeders de catálogo, `MincienciasSeeder` **no usa `firstOrCreate`** — inserta siempre con `DB::table()->insert()`. Ejecutar `db:seed` más de una vez duplica las 4 tipologías y las 14 subcategorías.

---

## CATÁLOGOS DEL DISEÑO ANTERIOR — YA NO EXISTEN

Estos catálogos aparecían en la versión previa de este documento y **fueron eliminados por completo** (tabla, modelo, seeder, controlador, vistas) en el rediseño de agosto de 2026 — no intentes sembrarlos ni buscarlos en la interfaz:

- **Grandes Áreas de Conocimiento** y **Áreas de Conocimiento** (`knowledge_grand_areas`, `knowledge_areas`) — `BUG-20260813-041`.
- **Grupos de Investigación** (`research_groups`) — junto con el `ResearchGroupSeeder` que los sembraba (código muerto, ya eliminado).

---

## ESTADÍSTICAS GENERALES

| Elemento | Cantidad |
|----------|----------|
| Usuarios que crea `db:seed` | 1 (solo el super administrador bootstrap) |
| Roles | 6 |
| Permisos | 81 |
| Módulos de permisos | 11 |
| Centros de formación | 2 |
| Departamentos | 34 |
| Ciudades/municipios | 1123 |
| Líneas de investigación | 6 |
| Líneas tecnológicas | 6 |
| Áreas temáticas | 7 |
| Modalidades de proyecto | 4 |
| Tipos de investigación | 5 |
| Tipos de vinculación | 3 |
| Cargos/posiciones | 13 |
| Tipos de programa de formación | 3 |
| Programas de formación | 2 |
| Tipologías Minciencias | 4 |
| Subcategorías Minciencias | 14 |
