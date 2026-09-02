# Datos Cargados en Base de Datos
## Sistema Documental SENA - GIDESTH

**Regenerado:** 2026-09-02 — reescrito completo contra `database/seeders/*.php` actuales (6 roles). La versión anterior, de 2026-04-08, describía los 7 roles y catálogos del diseño previo al rediseño de agosto de 2026.
**Comando para cargar:** `php artisan db:seed`

---

## ⚠️ El seeding actual está roto — `db:seed` falla antes de crear usuarios

`database/seeders/DatabaseSeeder.php` llama a `ResearchGroupSeeder::class`, que referencia `App\Models\ResearchGroup` — **modelo eliminado** en el rediseño de roles de agosto (junto con la entidad "grupo de investigación" completa). El orden de ejecución es:

```
DepartmentSeeder → CitySeeder → TrainingCenterSeeder → RolesAndPermissionsSeeder
    → ResearchGroupSeeder   ← 💥 Error fatal: clase App\Models\ResearchGroup no existe
    → UserSeeder            ← nunca se ejecuta
    → SuperAdminSeeder       ← nunca se ejecuta
    → [8 seeders de catálogos] ← nunca se ejecutan
```

Con el código actual, `php artisan db:seed` en una base de datos nueva crea departamentos, ciudades, centros de formación y los roles/permisos — y **se detiene ahí**: ningún usuario ni catálogo (líneas de investigación, cargos, tipos de vinculación, Minciencias, etc.) llega a sembrarse. Todo el contenido de este documento a partir de aquí describe lo que **debería** sembrarse (leído directamente del código de cada seeder), no lo que efectivamente entra en una base de datos nueva hoy.

**Fix trivial disponible:** quitar la línea `ResearchGroupSeeder::class` de `DatabaseSeeder.php` (y opcionalmente eliminar el archivo `database/seeders/ResearchGroupSeeder.php`, que ya no tiene ninguna otra referencia). Ver si quieres que lo aplique.

---

## CREDENCIALES DE ACCESO POR ROL

> **Contraseña universal para todos los usuarios de prueba:** `Password123!` (definidas en `UserSeeder`; el super administrador puede sobreescribirse con las variables de entorno `SUPER_ADMIN_EMAIL` / `SUPER_ADMIN_PASSWORD` / `SUPER_ADMIN_DOCUMENT`, usadas por `SuperAdminSeeder`).

### Super Administrador
| Campo | Valor |
|-------|-------|
| Email | superadmin@sena.edu.co |
| Contraseña | Password123! |
| Documento | 900000001 |
| Rol | super_administrador |
| Centro | ninguno (rol sin `training_center_id`) |

### Administradores del Sistema (2 usuarios, uno por centro)
| Email | Documento | Centro | Creado por |
|-------|-----------|--------|------------|
| ydmoreno@sena.edu.co | 34327134 | Centro de Formación Agroindustrial (9116) | superadmin |
| jovalenciap@sena.edu.co | 10304952 | Centro de la Industria, la Empresa y los Servicios (9527) | superadmin |

### Directores de Semilleros (2 usuarios)
| Email | Documento | Centro | Creado por |
|-------|-----------|--------|------------|
| directorsem@sena.edu.co | 1076504087 | Centro de Formación Agroindustrial (9116) | ydmoreno (admin) |
| dirsemillero@sena.edu.co | 52345678 | Centro de la Industria, la Empresa y los Servicios (9527) | jovalenciap (admin) |

### Líderes de Semillero (2 usuarios)
| Email | Documento | Centro | Creado por |
|-------|-----------|--------|------------|
| lidersem@sena.edu.co | 87654321 | Centro de Formación Agroindustrial (9116) | directorsem |
| liderIndustrialsem@sena.edu.co | 103049521 | Centro de la Industria, la Empresa y los Servicios (9527) | dirsemillero |

### Líderes de Proyecto (2 usuarios)
| Email | Documento | Centro | Creado por |
|-------|-----------|--------|------------|
| liderproyecto@sena.edu.co | 55114455 | Centro de Formación Agroindustrial (9116) | lidersem |
| liderproyectoIndu@sena.edu.co | 55114456 | Centro de la Industria, la Empresa y los Servicios (9527) | liderIndustrialsem |

### Co-investigadores (2 usuarios — rol global, sin centro)
| Email | Documento | Centro | Creado por |
|-------|-----------|--------|------------|
| coinvestigador@sena.edu.co | 33333333 | *(ninguno)* | ydmoreno (admin) |
| coinvestigadorIndu@sena.edu.co | 33333334 | *(ninguno)* | jovalenciap (admin) |

**Total de usuarios que crea `UserSeeder` (una vez arreglado el paso 5): 11** (1 super admin + 2 admin + 2 director de semilleros + 2 líder de semillero + 2 líder de proyecto + 2 co-investigador), respetando la cadena de creación real del sistema (`created_by_user_id`).

Cada usuario recibe automáticamente un `Person` asociado (nombre, apellido, género) con `entity_position_id` y `linkage_type_id` fijos vía `crearPersonaSiFalta()` en `UserSeeder`.

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
- **Grupos de Investigación** (`research_groups`) — el `ResearchGroupSeeder` que aún los siembra es código muerto que rompe el resto del `db:seed` (ver advertencia al inicio de este documento).

---

## ESTADÍSTICAS GENERALES (una vez corregido el seeding)

| Elemento | Cantidad |
|----------|----------|
| Usuarios totales | 11 |
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
