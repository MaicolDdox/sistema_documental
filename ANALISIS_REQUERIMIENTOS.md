# Análisis de Requerimientos vs Implementación
## Sistema Documental SENA - GIDESTH

**Fecha de análisis:** 2026-04-08  
**Documentación analizada:**
- Historias de usuario por rol (6 archivos `.xlsx`)
- Diagramas de flujo por rol (6 imágenes `.drawio.png`)
- Diagramas de actividades por rol (4 imágenes `.png`)
- Requerimientos y formularios por rol (7 archivos `.xlsx` + 1 `.pdf`)

---

## RESUMEN EJECUTIVO

| Rol | Requerimientos documentados | Implementados | Estado |
|-----|----------------------------|---------------|--------|
| Super Administrador | Gestión de centros y admins | Parcial | ⚠️ Incompleto |
| Administrador del Sistema | Usuarios, catálogos, reportes | Completo | ✅ |
| Director de Investigación | Investigadores, productos, reportes | Completo | ✅ |
| Director de Semilleros | Semilleros, líderes, reportes | Completo | ✅ |
| Líder de Semillero | Info semillero, miembros, productos, archivos | Completo | ✅ |
| Asesor de Semillero | Aprendices, proyectos, productos, evidencias | Completo | ✅ |
| Investigador Asociado | Proyectos, productos, evidencias, reportes | Completo | ✅ |

---

## ACIERTOS ✅

### 1. Arquitectura y Seguridad
- **Sistema multirol completo:** 7 roles definidos con rutas, middlewares y vistas independientes por rol.
- **78 permisos granulares** organizados en 12 módulos (`usuarios`, `catálogos`, `grupos`, `semilleros`, `proyectos`, `aprendices`, `productos`, `evidencias`, `documentos`, `archivos_semillero`, `asesores_externos`, `reportes`).
- **Autenticación de dos factores (2FA)** implementada mediante Laravel Fortify con soporte para códigos QR.
- **Middleware de estado activo** (`ensure.active`) para bloquear usuarios inactivos.
- **Políticas de autorización** (`Policies/`) implementadas para los modelos principales.
- **Spatie Laravel Permission** integrado correctamente para manejo de roles y permisos.

### 2. Módulo Administrador del Sistema
- CRUD completo de usuarios con activación/desactivación.
- Gestión completa de catálogos: departamentos, ciudades, centros de formación, cargos, tipos de vinculación.
- Gestión de grupos de investigación desde la vista admin.
- Gestión de programas de formación, registros de formación, tipos de programa.
- Configuración de líneas de investigación, tecnológicas y temáticas.
- Configuración de tipologías Minciencias y subcategorías.
- Configuración de áreas de conocimiento y grandes áreas.
- Configuración de modalidades de proyecto y tipos de investigación.
- Componentes Livewire para creación y edición de usuarios (en tiempo real).

### 3. Módulo Director de Investigación
- Gestión completa de investigadores del grupo (crear, vincular, desvincular, cambiar rol, toggle estado, resetear contraseña).
- Flujo de revisión y aprobación/rechazo de productos con observaciones.
- Gestión de documentos institucionales del grupo.
- Gestión de macroproyectos (catálogo con activación/desactivación).
- Reportes exportables en CSV y PDF.

### 4. Módulo Director de Semilleros
- CRUD completo de semilleros con toggle de estado.
- Gestión de líderes de semillero con toggle de estado.
- Vinculaciones semillero-líder gestionables.
- Gestión de documentos institucionales del semillero.
- Reportes exportables.
- Reasignación de líderes a semilleros.

### 5. Módulo Líder de Semillero
- Vista de información del semillero (solo lectura).
- Visualización de integrantes con tarjetas de estado de proyecto.
- CRUD de asesores con toggle de estado.
- Visualización de proyectos del semillero.
- Registro y flujo de aprobación/rechazo de productos.
- Asignación de investigador a producto.
- Gestión de archivos del semillero (subir/ver/descargar/eliminar).
- Documentación interna (actas, informes) con gestión completa.

### 6. Módulo Asesor de Semillero
- CRUD completo de aprendices con desactivación.
- CRUD completo de proyectos con vinculación de integrantes.
- CRUD completo de productos con desactivación.
- Gestión de evidencias para proyectos y productos.
- Exportación de reportes por módulo (semilleros, proyectos, productos, aprendices).
- Endpoints API internos para carga de datos dinámica (proyectos por semillero, autores por proyecto).
- Permisos granulares con middleware `can:` en cada ruta.

### 7. Módulo Investigador Asociado
- CRUD completo de proyectos con gestión de autores y finalización.
- CRUD completo de productos (propios y de grupo).
- Gestión de evidencias por proyecto y producto.
- Bandeja de semilleros con formalización.
- Vista de estados de productos.
- Reportes exportables en CSV y PDF filtrados por estado.

### 8. Modelos de Datos
- **48 modelos Eloquent** cubriendo todas las entidades del sistema.
- Relaciones bien definidas (hasMany, belongsTo, belongsToMany, morphTo).
- Enums PHP para estados y roles internos.
- Catálogos parametrizables desde la interfaz.

### 9. Generación de Documentos
- **Laravel DomPDF** integrado para exportación a PDF.
- **PHPSpreadsheet** integrado para exportación a Excel.
- Reportes disponibles para todos los roles que los requieren según documentación.

### 10. Frontend
- **Livewire 4** con componentes reactivos para autenticación y administración de usuarios.
- **Tailwind CSS 4 + Flux UI** para interfaz consistente.
- Vistas organizadas por rol en `resources/views/`.
- Componentes reutilizables en `resources/views/components/`.

---

## DESACIERTOS ⚠️

### 1. Módulo Super Administrador — Incompleto
- **Problema:** El módulo `/super-admin` solo implementa la asignación de centros administradores. 
- **Esperado según requerimientos:** El super administrador debería tener acceso a toda la plataforma, gestión de todos los centros de formación, configuración global del sistema, y vista de métricas globales.
- **Impacto:** Alto — el rol de mayor jerarquía tiene funcionalidad mínima en su módulo dedicado.

### 2. Modelos Huérfanos — Sin Usar
- **Archivos:** `app/Models/ProjectGroup.php`, `app/Models/ProjectSeedling.php`, `app/Models/SeedlingMember.php`
- **Problema:** Existen modelos Eloquent para tablas que el sistema consulta mediante `DB::table()` en lugar de usar el modelo. Los modelos existen pero nunca son instanciados.
- **Impacto:** Bajo en funcionalidad, pero genera confusión en el codebase y deuda técnica.

### 3. Archivos de Desarrollo en Raíz — No Eliminados
- **Archivos:** `diagnose_investigador.php`, `fix_asesor_role.php`, `scaffold_mvc.php`, `scaffold_parametric.php`, `seed_catalogs.php`
- **Problema:** Scripts utilitarios usados durante el desarrollo permanecen en la raíz del proyecto. No forman parte de la aplicación y podrían representar un riesgo de seguridad si son accesibles.
- **Impacto:** Seguridad y limpieza del código.

### 4. Configuración por Defecto en .env — Incompatible
- **Problema:** El `.env.example` configura SQLite como base de datos por defecto, pero el sistema usa sintaxis MySQL/MariaDB en sus migraciones (`MODIFY`, `ENUM`). Esto genera un error inmediato al instalar.
- **Impacto:** Alto en instalación — bloquea el proceso de setup sin una solución obvia.

### 5. Ausencia de README de Instalación
- **Problema:** No existe ningún archivo `README.md` en la raíz del proyecto.
- **Impacto:** Cualquier desarrollador nuevo no tiene guía de instalación, lo que hace el onboarding difícil.

### 6. Sin Pruebas de Negocio
- **Problema:** Los tests existentes son solo los generados por el starter kit de Laravel (autenticación básica). No hay pruebas para los módulos de negocio: proyectos, productos, semilleros, grupos.
- **Impacto:** Riesgo de regresiones al hacer cambios.

### 7. Componente Livewire Vacío
- **Archivo:** `app/Livewire/Settings/Appearance.php`
- **Problema:** El componente existe y tiene su vista, pero no implementa ninguna lógica. Es un stub vacío.
- **Impacto:** Bajo — la vista se muestra pero no hace nada funcional.

### 8. Inconsistencia en Acceso a Datos
- **Problema:** El sistema mezcla el uso de modelos Eloquent y consultas `DB::table()` directas para acceder a las mismas entidades. Por ejemplo, `SeedlingMember`, `ProjectGroup` y `ProjectSeedling` se acceden solo via `DB::table()` aunque existen los modelos.
- **Impacto:** Dificulta el mantenimiento y viola el principio de consistencia del ORM.

### 9. Módulo de Investigador — Sin Bandeja Completa
- **Problema:** El diagrama de flujo del investigador muestra una "Bandeja" de semilleros con formalización de proyectos. La ruta `/investigador/bandeja-semilleros` existe, pero el flujo de formalización no está completamente desarrollado según los diagramas (falta el paso de confirmación y notificación).
- **Impacto:** Medio — funcionalidad incompleta en un flujo clave.

### 10. Sin Notificaciones ni Alertas
- **Problema:** Los diagramas de actividades muestran notificaciones al aprobar/rechazar productos y al cambiar estados. El sistema no implementa notificaciones (email, in-app) para ninguno de estos eventos.
- **Impacto:** Medio — los usuarios deben revisar manualmente los estados en lugar de ser notificados.

### 11. Gestión de Asesores Externos — Incompleta
- **Problema:** El permiso `asesores_externos.registrar/vincular/desvincular/listar` está definido y asignado al asesor de semillero, pero no hay un módulo visual completo para gestionar asesores externos dentro de la interfaz del asesor. Las rutas para asesores externos en el módulo asesor están limitadas.
- **Impacto:** Medio — funcionalidad esperada según documentación no es totalmente accesible.

---

## RECOMENDACIONES PRIORITARIAS

1. **[Alta]** Completar el módulo del Super Administrador con dashboard global y gestión de centros.
2. **[Alta]** Cambiar el `.env.example` para usar MySQL por defecto, añadir un `README.md` con instrucciones de instalación.
3. **[Media]** Refactorizar las consultas `DB::table()` para usar los modelos Eloquent correspondientes, o eliminar los modelos huérfanos.
4. **[Media]** Implementar notificaciones por email o in-app para eventos de aprobación/rechazo.
5. **[Media]** Completar el flujo de formalización en la bandeja del investigador.
6. **[Baja]** Agregar pruebas unitarias y de integración para los módulos de negocio principales.
7. **[Baja]** Completar el componente `Appearance.php` de settings o eliminarlo.
