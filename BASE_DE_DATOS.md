# Datos Cargados en Base de Datos
## Sistema Documental SENA - GIDESTH

**Generado:** 2026-04-08  
**Comando para cargar:** `php artisan db:seed`

---

## CREDENCIALES DE ACCESO POR ROL

> **Contraseña universal para todos los usuarios de prueba:** `Password123!`

### Super Administrador
| Campo | Valor |
|-------|-------|
| Email | superadmin@sena.edu.co |
| Contraseña | Password123! |
| Documento | 900000001 |
| Rol | super_administrador |
| Centro | Centro de Formación Agroindustrial (9116) |

---

### Administradores del Sistema (2 usuarios)
| Email | Contraseña | Documento | Centro |
|-------|------------|-----------|--------|
| ydmoreno@sena.edu.co | Password123! | 34327134 | Centro de Formación Agroindustrial (9116) |
| jovalenciap@sena.edu.co | Password123! | 10304952 | Centro de la Industria, la Empresa y los Servicios (9527) |

---

### Directores de Semilleros (2 usuarios)
| Email | Contraseña | Documento | Centro |
|-------|------------|-----------|--------|
| directorsem@sena.edu.co | Password123! | 1076504087 | Centro de Formación Agroindustrial (9116) |
| dirsemillero@sena.edu.co | Password123! | 52345678 | Centro de la Industria, la Empresa y los Servicios (9527) |

---

### Líderes de Semillero (2 usuarios)
| Email | Contraseña | Documento | Centro |
|-------|------------|-----------|--------|
| lidersem@sena.edu.co | Password123! | 87654321 | Centro de Formación Agroindustrial (9116) |
| liderIndustrialsem@sena.edu.co | Password123! | 103049521 | Centro de la Industria, la Empresa y los Servicios (9527) |

---

### Asesores de Semillero (2 usuarios)
| Email | Contraseña | Documento | Centro |
|-------|------------|-----------|--------|
| asesorsem@sena.edu.co | Password123! | 55114455 | Centro de Formación Agroindustrial (9116) |
| asesorIndu@sena.edu.co | Password123! | 55114456 | Centro de la Industria, la Empresa y los Servicios (9527) |

---

### Directores de Investigación (2 usuarios)
| Email | Contraseña | Documento | Centro | Grupo |
|-------|------------|-----------|--------|-------|
| dirgrupo1@sena.edu.co | Password123! | 11111111 | Centro de la Industria (9527) | GIDESTH Industria |
| dirgrupo2@sena.edu.co | Password123! | 22222222 | Centro Agroempresarial (9116) | GIDESTH Agroindustrial |

---

### Investigadores Asociados (2 usuarios)
| Email | Contraseña | Documento | Centro |
|-------|------------|-----------|--------|
| investigador@sena.edu.co | Password123! | 33333333 | Centro de Formación Agroindustrial (9116) |
| investigadorIndu@sena.edu.co | Password123! | 33333334 | Centro de la Industria, la Empresa y los Servicios (9527) |

---

## ROLES Y PERMISOS

### Roles del sistema (7 roles)

| Rol | Descripción |
|-----|-------------|
| `super_administrador` | Acceso total al sistema (78 permisos) |
| `administrador_sistema` | Gestión del centro de formación |
| `director_investigacion` | Gestión del grupo de investigación |
| `director_semilleros` | Coordinación de semilleros del centro |
| `lider_semillero` | Coordinación de un semillero específico |
| `asesor_semillero` | Operación directa del semillero |
| `investigador_asociado` | Registro de proyectos y productos propios |

### Permisos por módulo (78 permisos totales)

#### Módulo: `usuarios`
`usuarios.listar` · `usuarios.crear` · `usuarios.editar` · `usuarios.activar_desactivar` · `usuarios.asignar_rol` · `usuarios.revocar_rol` · `usuarios.crear_investigador_asociado` · `usuarios.crear_lider_semillero` · `usuarios.asignar_credenciales`

#### Módulo: `catalogos`
`catalogos.crear` · `catalogos.leer` · `catalogos.editar` · `catalogos.eliminar`

#### Módulo: `grupos`
`grupos.crear` · `grupos.leer` · `grupos.editar` · `grupos.activar_desactivar` · `grupos.gestionar_miembros` · `grupos.vincular_investigador` · `grupos.desvincular_investigador` · `grupos.cambiar_rol_interno`

#### Módulo: `semilleros`
`semilleros.crear` · `semilleros.listar` · `semilleros.ver_detalle` · `semilleros.editar` · `semilleros.activar_desactivar` · `semilleros.reasignar_lider` · `semilleros.ver_integrantes` · `semilleros.ver_asesores` · `semilleros.ver_proyectos` · `semilleros.ver_productos` · `semilleros.ver_evidencias` · `semilleros.gestionar_miembros`

#### Módulo: `proyectos`
`proyectos.crear` · `proyectos.listar` · `proyectos.ver_detalle` · `proyectos.editar` · `proyectos.activar_desactivar` · `proyectos.ver_ajeno` · `proyectos.gestionar_autores` · `proyectos.listar_semillero` · `proyectos.crear_semillero` · `proyectos.vincular_integrantes`

#### Módulo: `aprendices`
`aprendices.registrar` · `aprendices.buscar_por_documento` · `aprendices.vincular_proyecto` · `aprendices.desvincular_proyecto` · `aprendices.listar_autores` · `aprendices.listar` · `aprendices.editar` · `aprendices.ver_detalle`

#### Módulo: `productos`
`productos.crear` · `productos.listar` · `productos.ver_detalle` · `productos.editar` · `productos.aprobar` · `productos.rechazar` · `productos.cambiar_a_en_revision` · `productos.ver_estado_revision` · `productos.ver_observaciones` · `productos.registrar`

#### Módulo: `evidencias`
`evidencias.subir_proyecto` · `evidencias.subir_producto` · `evidencias.listar` · `evidencias.eliminar_propia` · `evidencias.eliminar_cualquiera` · `evidencias.ver_del_grupo` · `evidencias.ver_del_semillero`

#### Módulo: `documentos`
`documentos.subir` · `documentos.listar` · `documentos.eliminar_propio`

#### Módulo: `archivos_semillero`
`archivos_semillero.subir` · `archivos_semillero.listar` · `archivos_semillero.eliminar`

#### Módulo: `asesores_externos`
`asesores_externos.registrar` · `asesores_externos.vincular_semillero` · `asesores_externos.desvincular_semillero` · `asesores_externos.listar`

#### Módulo: `reportes`
`reportes.globales_centro` · `reportes.usuarios_por_rol` · `reportes.grupos_con_metricas` · `reportes.semilleros_con_metricas` · `reportes.proyectos_por_estado` · `reportes.productos_por_estado` · `reportes.exportar_pdf_excel` · `reportes.productos_por_investigador` · `reportes.productos_por_anio` · `reportes.aprobados_vs_rechazados` · `reportes.aprendices_por_semillero`

---

## CENTROS DE FORMACIÓN

| Código | Nombre | Departamento | Ciudad |
|--------|--------|-------------|--------|
| 9527 | Centro de la Industria, la Empresa y los Servicios | Huila | Neiva |
| 9116 | Centro de Formación Agroindustrial | Huila | Campoalegre |

---

## GRUPOS DE INVESTIGACIÓN

| Nombre | Código | Centro | Estado |
|--------|--------|--------|--------|
| GIDESTH Agroindustrial | 9116 | Centro de Formación Agroindustrial (9116) | Activo |
| GIDESTH Industria | 9527 | Centro de la Industria, la Empresa y los Servicios (9527) | Activo |

---

## PROGRAMAS DE FORMACIÓN

| Nombre | Código | Tipo | Jornada | Modalidad | Estado |
|--------|--------|------|---------|-----------|--------|
| Análisis y Desarrollo de Software | 2502601 | Tecnólogo | Diurna | Presencial | Activo |
| Gestión Administrativa | 2603450 | Tecnólogo | Nocturna | Virtual | Activo |

---

## CATÁLOGOS CARGADOS

### Áreas Temáticas (7)
- Ambiental
- Agrícola
- Agroindustrial
- Pecuaria
- Pedagógico
- TIC
- Emprendimiento

---

### Líneas de Investigación (5)
- Producción Agropecuaria sostenible
- Desarrollo Agroindustrial de base tecnológica
- Empresarismo e inteligencia de mercados de base
- Gestión ambiental y aprovechamiento sostenible de los recursos naturales
- TIC aplicado al desarrollo sostenible

---

### Líneas Tecnológicas (3)
- Diseño de Productos, Producción y Transformación, Materiales y Biotecnología
- TIC's e Inteligencia Artificial, Usuario, Comercialización y Logística
- Sociedad, Cultura y Pedagogía, Economía Popular y Campesina, o Línea SENA se transforma

---

### Tipos de Vinculación (9)
| # | Nombre | Descripción |
|---|--------|-------------|
| 1 | Aprendiz SENA | Aprendiz vinculado mediante contrato de aprendizaje con el SENA |
| 2 | Practicante universitario | Estudiante universitario en práctica profesional |
| 3 | Voluntario | Participante voluntario sin contrato formal |
| 4 | Investigador externo | Investigador vinculado mediante convenio interinstitucional |
| 5 | Instructor SENA | Instructor del SENA que participa como integrante |
| 6 | Contrato de prestación de servicios | Vinculado mediante contrato de prestación de servicios |
| 7 | Tecnólogo | Aprendiz en programa de nivel Tecnólogo |
| 8 | Técnico | Aprendiz en programa de nivel Técnico |
| 9 | Cursos cortos | Participante en cursos cortos complementarios |

---

### Cargos/Posiciones en Entidades (15)
- Articulador Tecnoparque
- Dinamizador Sennova
- Facilitador Tecnoacademia
- Investigador experto
- Líder de grupo de investigación
- Líder de semillero
- Auxiliar editorial
- Personal técnico de laboratorio
- Responsable de propiedad intelectual
- Instructor investigador
- Aprendiz semillero
- Titulada
- Externos
- Tecno academia
- Articulación con la media

---

### Modalidades de Proyecto (3)
| Nombre | Descripción |
|--------|-------------|
| Propuesta | El proyecto se encuentra en fase de propuesta, aún no ha iniciado |
| En curso | El proyecto está actualmente en ejecución |
| Finalizado | El proyecto ha concluido su ejecución |

---

### Tipos de Investigación (2)
| Nombre | Descripción |
|--------|-------------|
| Aplicada | Investigación orientada a resolver problemas prácticos concretos |
| Innovación | Investigación orientada a desarrollar nuevos productos, procesos o servicios |

---

### Tipologías Minciencias (4 tipologías, 13 subcategorías)

#### Generación de Nuevo Conocimiento (GNC)
- Artículos de investigación
- Libros resultado de investigación
- Capítulos de libro

#### Apropiación Social del Conocimiento (ASC)
- Estrategias de comunicación
- Eventos científicos
- Circulación de conocimiento

#### Desarrollo Tecnológico e Innovación (DTI)
- Software
- Plantas piloto
- Prototipos
- Productos empresariales industriales

#### Formación de Recursos Humanos (FRH)
- Tesis de doctorado
- Trabajos de maestría
- Trabajos de pregrado
- Cursos de corta duración

---

### Grandes Áreas de Conocimiento (6 áreas, con subareas)

#### 1. Ciencias Naturales
Matemáticas · Ciencias de la computación · Ciencias físicas · Ciencias químicas · Ciencias de la Tierra y medioambientales

#### 2. Ingeniería y Tecnología
Ingeniería civil · Ingeniería eléctrica, electrónica · Ingeniería mecánica · Ingeniería química · Ingeniería de materiales

#### 3. Ciencias Médicas y de Salud
Medicina básica · Medicina clínica · Ciencias de la salud · Biotecnología en salud

#### 4. Ciencias Agrícolas
Agricultura, silvicultura, y pesca · Ciencias animales y lechería · Veterinaria · Biotecnología agrícola

#### 5. Ciencias Sociales
Psicología · Economía y negocios · Ciencias de la educación · Sociología · Derecho

#### 6. Humanidades
Historia y arqueología · Idiomas y literatura · Filosofía, ética y religión · Arte

---

## ESTADÍSTICAS GENERALES

| Elemento | Cantidad |
|----------|----------|
| Usuarios totales | 13 |
| Roles | 7 |
| Permisos | 78 |
| Centros de formación | 2 |
| Grupos de investigación | 2 |
| Programas de formación | 2 |
| Áreas temáticas | 7 |
| Líneas de investigación | 5 |
| Líneas tecnológicas | 3 |
| Tipos de vinculación | 9 |
| Cargos en entidades | 15 |
| Modalidades de proyecto | 3 |
| Tipos de investigación | 2 |
| Tipologías Minciencias | 4 |
| Subcategorías Minciencias | 13 |
| Grandes áreas de conocimiento | 6 |
