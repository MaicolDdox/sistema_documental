# Casos de Prueba — Sistema Documental SENA GIDESTH
## Manual de Verificación Funcional por Rol

**Versión:** 1.0 — 2026-04-08  
**URL base del sistema:** `http://localhost` (Laragon) o `http://127.0.0.1:8000` (artisan serve)  
**Prerrequisito:** Base de datos migrada y seeders ejecutados (`php artisan migrate && php artisan db:seed`)

---

## CREDENCIALES DE ACCESO (todas las contraseñas: `Password123!`)

| Rol | Email | URL de entrada tras login |
|-----|-------|--------------------------|
| Super Administrador | superadmin@sena.edu.co | /super-admin/dashboard |
| Administrador (Centro Agro) | ydmoreno@sena.edu.co | /admin/dashboard |
| Administrador (Centro Industria) | jovalenciap@sena.edu.co | /admin/dashboard |
| Director Investigación (Agro) | dirgrupo2@sena.edu.co | /director |
| Director Investigación (Industria) | dirgrupo1@sena.edu.co | /director |
| Director Semilleros (Agro) | directorsem@sena.edu.co | /director-semilleros |
| Director Semilleros (Industria) | dirsemillero@sena.edu.co | /director-semilleros |
| Líder Semillero (Agro) | lidersem@sena.edu.co | /lider-semillero |
| Líder Semillero (Industria) | liderIndustrialsem@sena.edu.co | /lider-semillero |
| Asesor Semillero (Agro) | asesorsem@sena.edu.co | /asesor-semillero/dashboard |
| Asesor Semillero (Industria) | asesorIndu@sena.edu.co | /asesor-semillero/dashboard |
| Investigador (Agro) | investigador@sena.edu.co | /investigador |
| Investigador (Industria) | investigadorIndu@sena.edu.co | /investigador |

---

## CONVENCIONES

| Símbolo | Significado |
|---------|-------------|
| ✅ PASA | El resultado esperado coincide con lo observado |
| ❌ FALLA | El resultado no coincide |
| ⬜ PENDIENTE | Caso aún no ejecutado |
| `[DATO]` | Valor que el tester debe ingresar o tomar nota |
| **Prerreq.** | Condición que debe cumplirse antes de ejecutar el caso |

---

## MÓDULO 0: AUTENTICACIÓN (todos los roles)

### CP-AUTH-01: Login exitoso
**Credenciales:** cualquier usuario de la tabla de arriba  
**Pasos:**
1. Ir a `http://localhost/login`
2. Ingresar email: `superadmin@sena.edu.co`
3. Ingresar contraseña: `Password123!`
4. Clic en "Iniciar sesión"

**Resultado esperado:** Redirige a `/super-admin/dashboard`. La barra de navegación muestra el nombre del usuario y su rol.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-AUTH-02: Login fallido con contraseña incorrecta
**Pasos:**
1. Ir a `http://localhost/login`
2. Ingresar email: `superadmin@sena.edu.co`
3. Ingresar contraseña: `contraseña_incorrecta`
4. Clic en "Iniciar sesión"

**Resultado esperado:** Permanece en `/login`. Muestra mensaje de error indicando credenciales inválidas. No redirige al dashboard.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-AUTH-03: Acceso bloqueado por rol incorrecto
**Pasos:**
1. Iniciar sesión como `lidersem@sena.edu.co`
2. Intentar acceder manualmente a `/admin/dashboard`

**Resultado esperado:** Redirige a `/lider-semillero` o muestra error 403 Prohibido. No carga el panel de admin.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-AUTH-04: Logout
**Prerreq.:** Sesión iniciada con cualquier usuario  
**Pasos:**
1. Clic en el menú de usuario (esquina superior derecha)
2. Clic en "Cerrar sesión"

**Resultado esperado:** Redirige a `/login`. Al intentar acceder a `/admin/dashboard` redirige nuevamente a `/login`.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-AUTH-05: Acceso con usuario inactivo
**Prerreq.:** Tener un usuario desactivado (ejecutar luego de CP-ADM-09)  
**Pasos:**
1. Intentar iniciar sesión con el usuario desactivado en CP-ADM-09

**Resultado esperado:** Login rechazado o redirige a página de cuenta inactiva. No accede al sistema.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-AUTH-06: Configuración de perfil
**Prerreq.:** Sesión iniciada  
**Pasos:**
1. Ir a `/settings/profile`
2. Cambiar el nombre del campo "nombre"
3. Clic en "Guardar"

**Resultado esperado:** Mensaje de éxito. El nombre actualizado aparece en la barra de navegación.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-AUTH-07: Cambio de contraseña
**Prerreq.:** Sesión iniciada  
**Pasos:**
1. Ir a `/settings/password`
2. Ingresar contraseña actual: `Password123!`
3. Ingresar nueva contraseña: `NuevoPass456!`
4. Confirmar nueva contraseña: `NuevoPass456!`
5. Clic en "Actualizar contraseña"
6. Cerrar sesión
7. Intentar iniciar sesión con contraseña nueva `NuevoPass456!`

**Resultado esperado:** Login exitoso con la nueva contraseña. La contraseña anterior ya no funciona.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

## MÓDULO 1: SUPER ADMINISTRADOR

**Credenciales de prueba:** `superadmin@sena.edu.co` / `Password123!`

---

### CP-SA-01: Dashboard global
**Pasos:**
1. Iniciar sesión como super admin
2. Ir a `/super-admin/dashboard`
3. Verificar las métricas mostradas

**Resultado esperado:**
- Muestra conteo total de usuarios del sistema (13 usuarios de prueba)
- Muestra conteo de grupos de investigación (2 grupos)
- Muestra conteo de semilleros
- Muestra conteo de centros de formación (2 centros)
- Lista los últimos usuarios creados con nombre y rol
- Lista los últimos grupos de investigación

**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-SA-02: Ver listado de centros administradores
**Pasos:**
1. Ir a `/super-admin/centros-administradores`
2. Verificar la tabla mostrada

**Resultado esperado:** Tabla con los centros de formación y su administrador asignado. Muestra: Centro Agroempresarial (ydmoreno) y Centro Industria (jovalenciap).  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-SA-03: Asignar administrador a centro
**Prerreq.:** Tener un usuario con rol `administrador_sistema` sin centro asignado  
**Pasos:**
1. Ir a `/super-admin/centros-administradores`
2. Seleccionar un centro de formación
3. Seleccionar un administrador disponible
4. Clic en "Asignar"

**Resultado esperado:** Mensaje de éxito. El administrador aparece vinculado al centro en la tabla. Si el centro ya tenía admin, el anterior es reemplazado.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-SA-04: Prevención de asignación duplicada
**Pasos:**
1. Ir a `/super-admin/centros-administradores`
2. Intentar asignar el mismo administrador a un segundo centro distinto

**Resultado esperado:** Mensaje de error indicando que el administrador ya está asignado a otro centro.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

## MÓDULO 2: ADMINISTRADOR DEL SISTEMA

**Credenciales de prueba:** `ydmoreno@sena.edu.co` / `Password123!`  
*(Administrador del Centro de Formación Agroindustrial — código 9116)*

---

### CP-ADM-01: Dashboard del centro
**Pasos:**
1. Iniciar sesión como `ydmoreno@sena.edu.co`
2. Verificar que redirige a `/admin/dashboard`
3. Verificar las métricas

**Resultado esperado:** Muestra métricas filtradas solo para Centro Agroempresarial (9116). No muestra datos del Centro Industria (9527).  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-ADM-02: Listar usuarios del centro
**Pasos:**
1. Ir a `/admin/usuarios`

**Resultado esperado:** Lista solo los usuarios pertenecientes al Centro Agroempresarial. Incluye: directorsem, lidersem, asesorsem, dirgrupo2, investigador, superadmin. NO debe mostrar usuarios del Centro Industria.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-ADM-03: Crear nuevo usuario
**Pasos:**
1. Ir a `/admin/usuarios/create`
2. Llenar el formulario:
   - Tipo documento: Cédula de Ciudadanía
   - Número documento: `99887766`
   - Nombres: `Carlos`
   - Apellidos: `Prueba Nuevo`
   - Email: `cprueba@sena.edu.co`
   - Rol: `investigador_asociado`
   - Centro de formación: Centro Agroempresarial
3. Clic en "Crear usuario"

**Resultado esperado:** Mensaje de éxito. El usuario `cprueba@sena.edu.co` aparece en la lista con rol `investigador_asociado` y estado Activo. *(Nota: si el envío de email no está implementado, el usuario es creado pero no recibe notificación.)*  
**Resultado obtenido:** ___  
**Estado:** ⬜
**Notar:** ¿El usuario recibió un email con sus credenciales? Sí / No / ___

---

### CP-ADM-04: Editar usuario existente
**Prerreq.:** Haber creado el usuario en CP-ADM-03  
**Pasos:**
1. En la lista de usuarios, clic en "Editar" para `cprueba@sena.edu.co`
2. Cambiar Apellidos a `Prueba Editado`
3. Clic en "Actualizar"

**Resultado esperado:** Mensaje de éxito. La lista muestra el apellido actualizado.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-ADM-05: Asignar rol a usuario
**Pasos:**
1. Ir a `/admin/usuarios/asignar-roles`
2. Seleccionar el usuario `cprueba@sena.edu.co`
3. Seleccionar rol adicional `asesor_semillero`
4. Guardar

**Resultado esperado:** El usuario ahora tiene dos roles: `investigador_asociado` y `asesor_semillero`.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-ADM-06: Revocar rol a usuario
**Prerreq.:** CP-ADM-05 ejecutado  
**Pasos:**
1. En la gestión del usuario `cprueba@sena.edu.co`
2. Revocar el rol `asesor_semillero`

**Resultado esperado:** El usuario queda solo con rol `investigador_asociado`.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-ADM-07: Crear grupo de investigación
**Pasos:**
1. Ir a `/admin/research-groups/create` (o `/admin/research-groups` → Crear)
2. Ingresar:
   - Nombre: `Grupo Prueba QA`
   - Código: `9116-QA`
   - Centro: Centro de Formación Agroindustrial
   - Descripción: `Grupo de prueba para QA`
3. Guardar

**Resultado esperado:** Grupo aparece en la lista con estado Activo.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-ADM-08: Gestión de catálogos — crear línea de investigación
**Pasos:**
1. Ir a `/admin/research-lines/create`
2. Ingresar nombre: `Línea de Prueba QA`
3. Guardar

**Resultado esperado:** La nueva línea aparece en el listado `/admin/research-lines`.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-ADM-09: Desactivar usuario
**Pasos:**
1. En la lista `/admin/usuarios`
2. Clic en "Toggle estado" del usuario `cprueba@sena.edu.co`

**Resultado esperado:** El usuario pasa a estado Inactivo. Aparece visualmente diferenciado (tachado, gris, etc.).  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-ADM-10: Reactivar usuario
**Prerreq.:** CP-ADM-09 ejecutado  
**Pasos:**
1. En la lista `/admin/usuarios`
2. Clic en "Toggle estado" nuevamente del usuario `cprueba@sena.edu.co`

**Resultado esperado:** El usuario vuelve a estado Activo.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-ADM-11: Gestión de catálogos — editar y eliminar
**Prerreq.:** CP-ADM-08 ejecutado  
**Pasos:**
1. Ir a `/admin/research-lines`
2. Editar "Línea de Prueba QA" → cambiar nombre a "Línea QA Editada"
3. Guardar
4. Eliminar "Línea QA Editada"

**Resultado esperado:** El nombre se actualiza correctamente. Al eliminar, la línea desaparece del listado.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-ADM-12: Aislamiento de datos entre centros
**Pasos:**
1. Cerrar sesión
2. Iniciar sesión como `jovalenciap@sena.edu.co` (Centro Industria)
3. Ir a `/admin/usuarios`

**Resultado esperado:** Solo ve usuarios del Centro Industria (9527). El usuario `cprueba@sena.edu.co` (creado en Centro Agro) NO aparece.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

## MÓDULO 3: DIRECTOR DE SEMILLEROS

**Credenciales de prueba:** `directorsem@sena.edu.co` / `Password123!`  
*(Centro de Formación Agroindustrial)*

---

### CP-DS-01: Dashboard de semilleros
**Pasos:**
1. Iniciar sesión como `directorsem@sena.edu.co`
2. Verificar `/director-semilleros`

**Resultado esperado:** Muestra conteo de semilleros, líderes, integrantes y asesores del centro. Gráficas de resumen visibles.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-DS-02: Crear semillero
**Pasos:**
1. Ir a `/director-semilleros/semilleros/create`
2. Llenar:
   - Nombre: `Semillero Alpha QA`
   - Código: `SEM-QA-001`
   - Descripción: `Semillero de prueba para QA`
   - Grupo de investigación: GIDESTH Agroindustrial
   - Logo: *(subir imagen PNG de prueba, máx 2MB)*
3. Guardar

**Resultado esperado:** Semillero aparece en `/director-semilleros/semilleros` con estado Activo. Logo visible.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-DS-03: Editar semillero
**Prerreq.:** CP-DS-02 ejecutado  
**Pasos:**
1. En la lista, clic en "Editar" para `Semillero Alpha QA`
2. Cambiar descripción a `Descripción editada QA`
3. Guardar

**Resultado esperado:** Descripción actualizada en el detalle del semillero.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-DS-04: Desactivar/activar semillero
**Prerreq.:** CP-DS-02 ejecutado  
**Pasos:**
1. En la lista, clic en "Toggle estado" de `Semillero Alpha QA`

**Resultado esperado:** Estado cambia a Inactivo. Al volver a clicar, regresa a Activo.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-DS-05: Crear líder de semillero
**Pasos:**
1. Ir a `/director-semilleros/lideres/create`
2. Llenar:
   - Nombres: `Andrés`
   - Apellidos: `Líder QA`
   - Documento: `12345678`
   - Email: `alider.qa@sena.edu.co`
   - Teléfono: `3001234567`
3. Guardar

**Resultado esperado:** Líder creado con rol `lider_semillero`. Aparece en `/director-semilleros/lideres`.  
**Resultado obtenido:** ___  
**Estado:** ⬜
**Notar:** ¿Se mostró la contraseña generada? Sí / No / ___

---

### CP-DS-06: Reasignar líder a semillero
**Prerreq.:** CP-DS-02 y CP-DS-05 ejecutados  
**Pasos:**
1. En la lista de semilleros, buscar `Semillero Alpha QA`
2. Clic en "Reasignar líder"
3. Seleccionar `Andrés Líder QA`
4. Confirmar

**Resultado esperado:** El semillero ahora muestra a `Andrés Líder QA` como líder.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-DS-07: Gestión de vinculaciones semillero-líder
**Pasos:**
1. Ir a `/director-semilleros/vinculaciones`

**Resultado esperado:** Tabla mostrando todos los semilleros con su líder asignado. Permite cambiar la asignación desde la misma tabla.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-DS-08: Subir documento institucional
**Pasos:**
1. Ir a `/director-semilleros/documentos/create`
2. Ingresar nombre: `Reglamento Semilleros QA`
3. Subir un archivo PDF (menor a 10MB)
4. Guardar

**Resultado esperado:** Documento aparece en `/director-semilleros/documentos` con nombre y fecha de carga.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-DS-09: Eliminar documento
**Prerreq.:** CP-DS-08 ejecutado  
**Pasos:**
1. En la lista de documentos, clic en "Eliminar" del documento `Reglamento Semilleros QA`

**Resultado esperado:** Documento desaparece de la lista. El archivo ya no es accesible.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-DS-10: Exportar reporte de semilleros
**Pasos:**
1. Ir a `/director-semilleros/reportes`
2. Clic en "Exportar"

**Resultado esperado:** Se descarga un archivo (PDF o Excel) con el reporte de semilleros del centro.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-DS-11: Desactivar líder
**Prerreq.:** CP-DS-05 ejecutado  
**Pasos:**
1. En `/director-semilleros/lideres`, clic en "Toggle estado" de `Andrés Líder QA`

**Resultado esperado:** El líder aparece como Inactivo. No puede iniciar sesión.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

## MÓDULO 4: LÍDER DE SEMILLERO

**Credenciales de prueba:** `lidersem@sena.edu.co` / `Password123!`  
**Prerreq.:** El líder debe tener un semillero asignado (creado por el director en CP-DS-02/06)

---

### CP-LS-01: Dashboard del semillero
**Pasos:**
1. Iniciar sesión como `lidersem@sena.edu.co`
2. Verificar `/lider-semillero`

**Resultado esperado:** Muestra nombre del semillero, conteo de integrantes, productos pendientes de revisión, proyectos activos y miembros sin proyecto.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-LS-02: Ver información del semillero
**Pasos:**
1. Ir a `/lider-semillero/info-semillero`

**Resultado esperado:** Vista de solo lectura con nombre, código, descripción, logo y grupo de investigación. No tiene formulario de edición (no puede editar estos datos).  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-LS-03: Ver integrantes del semillero
**Pasos:**
1. Ir a `/lider-semillero/integrantes`

**Resultado esperado:** Lista de integrantes con indicador de si tienen proyecto activo asignado. Cada integrante muestra nombre y estado de participación en proyectos.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-LS-04: Registrar asesor externo (sin cuenta del sistema)
**Pasos:**
1. Ir a `/lider-semillero/asesores`
2. Clic en "Agregar asesor"
3. Llenar:
   - Nombre completo: `Juan Externo QA`
   - Email: `jexterno@universidad.edu.co`
   - Teléfono: `3119876543`
   - Institución: `Universidad QA`
   - Crear cuenta en sistema: **No**
4. Guardar

**Resultado esperado:** Asesor externo aparece en la lista. No tiene cuenta de acceso al sistema.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-LS-05: Registrar asesor con cuenta del sistema
**Pasos:**
1. En `/lider-semillero/asesores`, clic en "Agregar asesor"
2. Llenar:
   - Nombre completo: `María Asesora QA`
   - Email: `masesora.qa@sena.edu.co`
   - Documento: `56781234`
   - Crear cuenta en sistema: **Sí**
3. Guardar

**Resultado esperado:** Asesor creado con rol `asesor_semillero`. Puede iniciar sesión en el sistema. Aparece en la lista del líder.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-LS-06: Desactivar/activar asesor
**Prerreq.:** CP-LS-04 ejecutado  
**Pasos:**
1. En la lista de asesores, clic en "Toggle" de `Juan Externo QA`

**Resultado esperado:** El asesor pasa a Inactivo (desvinculado del semillero temporalmente). Al clicar de nuevo, vuelve a Activo.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-LS-07: Ver proyectos del semillero (solo lectura)
**Pasos:**
1. Ir a `/lider-semillero/proyectos`

**Resultado esperado:** Lista de proyectos vinculados al semillero. Solo visualización — no hay botones de crear/editar (eso lo hace el asesor).  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-LS-08: Registrar producto del semillero
**Prerreq.:** Debe existir al menos un proyecto en el semillero (creado por el asesor en CP-AS-06)  
**Pasos:**
1. Ir a `/lider-semillero/productos`
2. Clic en "Registrar producto"
3. Llenar:
   - Proyecto: *(seleccionar el proyecto creado por el asesor)*
   - Título: `Producto QA del Semillero`
   - Archivo: *(subir PDF de prueba)*
4. Guardar

**Resultado esperado:** Producto aparece en lista con estado de revisión `Pendiente`.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-LS-09: Ver detalle de producto
**Prerreq.:** CP-LS-08 ejecutado  
**Pasos:**
1. En la lista de productos, clic en el producto `Producto QA del Semillero`

**Resultado esperado:** Vista de detalle mostrando: título, proyecto, estado de revisión, archivo descargable, autores y observaciones (si las hay).  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-LS-10: Aprobar producto
**Prerreq.:** CP-LS-08 ejecutado  
**Pasos:**
1. En el detalle del producto, clic en "Aprobar"
2. Confirmar

**Resultado esperado:** Estado del producto cambia a `Aprobado`. Aparece opción de "Asignar a investigador".  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-LS-11: Rechazar producto con observación
**Prerreq.:** Debe existir otro producto en estado Pendiente  
**Pasos:**
1. En el detalle de un producto pendiente, clic en "Rechazar"
2. Ingresar observación: `El documento no cumple con el formato requerido.`
3. Confirmar

**Resultado esperado:** Estado cambia a `Rechazado`. La observación es visible en el detalle del producto.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-LS-12: Asignar producto aprobado a investigador
**Prerreq.:** CP-LS-10 ejecutado  
**Pasos:**
1. En el detalle del producto aprobado, clic en "Asignar a investigador"
2. Seleccionar investigador: `investigador@sena.edu.co`
3. Confirmar

**Resultado esperado:** El producto queda asignado al investigador. Aparece en la bandeja del investigador (verificable en CP-INV-13).  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-LS-13: Subir archivo del semillero
**Pasos:**
1. Ir a `/lider-semillero/archivos`
2. Clic en "Subir archivo"
3. Seleccionar archivo PDF
4. Nombre: `Ponencia Nacional QA`
5. Guardar

**Resultado esperado:** Archivo aparece en la lista con opciones de Ver, Descargar y Eliminar.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-LS-14: Descargar archivo del semillero
**Prerreq.:** CP-LS-13 ejecutado  
**Pasos:**
1. En la lista de archivos, clic en "Descargar" de `Ponencia Nacional QA`

**Resultado esperado:** El archivo se descarga correctamente al equipo del tester.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-LS-15: Eliminar archivo del semillero
**Prerreq.:** CP-LS-13 ejecutado  
**Pasos:**
1. En la lista, clic en "Eliminar" del archivo `Ponencia Nacional QA`

**Resultado esperado:** Archivo desaparece de la lista. No es accesible desde su URL anterior.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-LS-16: Subir documento de documentación interna
**Pasos:**
1. Ir a `/lider-semillero/doc-interna`
2. Subir un PDF
3. Nombre: `Acta de Reunión QA 001`
4. Guardar

**Resultado esperado:** Documento aparece en la lista de documentación interna.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-LS-17: Descargar documento interno
**Prerreq.:** CP-LS-16 ejecutado  
**Pasos:**
1. Clic en "Descargar" de `Acta de Reunión QA 001`

**Resultado esperado:** Archivo se descarga correctamente.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

## MÓDULO 5: ASESOR DE SEMILLERO

**Credenciales de prueba:** `asesorsem@sena.edu.co` / `Password123!`  
**Prerreq.:** El asesor debe estar vinculado a al menos un semillero

---

### CP-AS-01: Dashboard del asesor
**Pasos:**
1. Iniciar sesión como `asesorsem@sena.edu.co`
2. Verificar `/asesor-semillero/dashboard`

**Resultado esperado:** Muestra totales de semilleros, proyectos, aprendices y productos. Estado de revisión de productos (pendiente/aprobado/rechazado).  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-AS-02: Ver mis semilleros
**Pasos:**
1. Ir a `/asesor-semillero/mis-semilleros`

**Resultado esperado:** Lista de semilleros donde el asesor está vinculado. Cada semillero tiene opción de "Seleccionar como activo".  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-AS-03: Establecer semillero activo
**Prerreq.:** CP-AS-02 — debe haber al menos un semillero  
**Pasos:**
1. En la lista, clic en "Seleccionar como activo" sobre el primer semillero disponible
2. Anotar el nombre del semillero: `[NOMBRE_SEMILLERO_ACTIVO]`

**Resultado esperado:** El semillero queda marcado como activo. Los módulos de aprendices, proyectos y productos ahora muestran datos de ese semillero.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-AS-04: Registrar aprendiz
**Prerreq.:** CP-AS-03 ejecutado  
**Pasos:**
1. Ir a `/asesor-semillero/aprendices/create`
2. Llenar:
   - Nombres: `Laura`
   - Apellidos: `Aprendiz QA`
   - Tipo documento: Cédula de Ciudadanía
   - Número documento: `10203040`
   - Email: `laprendiz.qa@sena.edu.co`
   - Programa de formación: Análisis y Desarrollo de Software
   - Tipo de vinculación: Aprendiz SENA
   - Jornada: Diurna
3. Guardar

**Resultado esperado:** Aprendiz registrado y aparece en `/asesor-semillero/aprendices`. Estado inicial: disponible para vincular a proyectos.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-AS-05: Ver detalle y editar aprendiz
**Prerreq.:** CP-AS-04 ejecutado  
**Pasos:**
1. En la lista, clic en `Laura Aprendiz QA`
2. Verificar detalle
3. Clic en "Editar"
4. Cambiar teléfono a `3201234567`
5. Guardar

**Resultado esperado:** Teléfono actualizado en el detalle del aprendiz.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-AS-06: Crear proyecto en el semillero
**Prerreq.:** CP-AS-03 ejecutado  
**Pasos:**
1. Ir a `/asesor-semillero/proyectos/create`
2. Llenar:
   - Nombre: `Proyecto QA Alpha`
   - Descripción: `Proyecto de prueba para QA`
   - Semillero: `[NOMBRE_SEMILLERO_ACTIVO]`
   - Línea de investigación: Producción Agropecuaria sostenible
   - Línea tecnológica: TIC's e Inteligencia Artificial...
   - Área temática: TIC
   - Modalidad: En curso
   - Tipo de investigación: Aplicada
   - Fecha inicio: 2026-01-01
   - Fecha fin: 2026-12-31
3. Guardar

**Resultado esperado:** Proyecto creado y aparece en `/asesor-semillero/proyectos`. El asesor aparece como autor automáticamente.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-AS-07: Vincular integrante al proyecto
**Prerreq.:** CP-AS-04 y CP-AS-06 ejecutados  
**Pasos:**
1. En la lista de proyectos, clic en "Integrantes" de `Proyecto QA Alpha`
2. Buscar `Laura Aprendiz QA`
3. Clic en "Vincular"

**Resultado esperado:** `Laura Aprendiz QA` aparece en la lista de autores/integrantes del proyecto.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-AS-08: Desvincular integrante del proyecto
**Prerreq.:** CP-AS-07 ejecutado  
**Pasos:**
1. En la lista de integrantes del proyecto, clic en "Desvincular" para `Laura Aprendiz QA`

**Resultado esperado:** El aprendiz ya no aparece en la lista de autores del proyecto. Puede volver a vincularse.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-AS-09: Registrar producto vinculado a proyecto
**Prerreq.:** CP-AS-06 ejecutado  
**Pasos:**
1. Ir a `/asesor-semillero/productos/create`
2. Llenar:
   - Semillero: `[NOMBRE_SEMILLERO_ACTIVO]`
   - Proyecto: `Proyecto QA Alpha`
   - Nombre del producto: `Artículo QA sobre TIC`
   - Archivo: *(subir PDF menor a 20MB)*
   - Autores: *(seleccionar al asesor mismo)*
3. Guardar

**Resultado esperado:** Producto registrado y aparece en `/asesor-semillero/productos` con estado `Pendiente`.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-AS-10: Editar producto
**Prerreq.:** CP-AS-09 ejecutado  
**Pasos:**
1. En la lista, clic en "Editar" de `Artículo QA sobre TIC`
2. Cambiar nombre a `Artículo QA sobre TIC - v2`
3. Guardar

**Resultado esperado:** El nombre actualizado aparece en la lista.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-AS-11: Descargar archivo del producto
**Prerreq.:** CP-AS-09 ejecutado  
**Pasos:**
1. En la lista de productos, clic en "Descargar" del producto

**Resultado esperado:** El archivo PDF se descarga al equipo del tester.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-AS-12: Subir evidencia de proyecto
**Prerreq.:** CP-AS-06 ejecutado  
**Pasos:**
1. En la lista de proyectos, clic en "Evidencias" de `Proyecto QA Alpha`
2. Clic en "Subir evidencia"
3. Nombre: `Certificado de participación QA`
4. Archivo: *(PDF de prueba)*
5. Guardar

**Resultado esperado:** Evidencia aparece en la lista de evidencias del proyecto.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-AS-13: Subir evidencia de producto
**Prerreq.:** CP-AS-09 ejecutado  
**Pasos:**
1. En la lista de productos, clic en "Evidencias" del producto
2. Subir evidencia: `Constancia de publicación QA`

**Resultado esperado:** Evidencia aparece en la lista de evidencias del producto.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-AS-14: Eliminar evidencia propia
**Prerreq.:** CP-AS-12 o CP-AS-13 ejecutado  
**Pasos:**
1. En la lista de evidencias, clic en "Eliminar" sobre la evidencia creada

**Resultado esperado:** Evidencia eliminada de la lista. Archivo no accesible.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-AS-15: Desactivar aprendiz
**Prerreq.:** CP-AS-04 ejecutado  
**Pasos:**
1. En `/asesor-semillero/aprendices`, clic en "Desactivar" de `Laura Aprendiz QA`

**Resultado esperado:** El aprendiz pasa a estado Inactivo. No puede ser vinculado a nuevos proyectos.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-AS-16: Exportar reporte de aprendices
**Pasos:**
1. Ir a `/asesor-semillero/exportar/aprendices`

**Resultado esperado:** Se descarga un archivo (PDF o Excel) con el listado de aprendices del semillero activo.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-AS-17: Exportar reporte de proyectos
**Pasos:**
1. Ir a `/asesor-semillero/exportar/proyectos`

**Resultado esperado:** Se descarga un archivo con el listado de proyectos.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-AS-18: Exportar reporte de productos
**Pasos:**
1. Ir a `/asesor-semillero/exportar/productos`

**Resultado esperado:** Se descarga un archivo con el listado de productos del asesor.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-AS-19: API proyectos por semillero (carga dinámica en formulario)
**Pasos:**
1. Ir a `/asesor-semillero/productos/create`
2. Seleccionar un semillero en el desplegable
3. Verificar que el campo "Proyecto" se actualiza automáticamente con los proyectos de ese semillero

**Resultado esperado:** El campo proyecto muestra solo los proyectos del semillero seleccionado, sin recargar la página (AJAX).  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-AS-20: Desactivar proyecto
**Prerreq.:** CP-AS-06 ejecutado  
**Pasos:**
1. En `/asesor-semillero/proyectos`, clic en "Desactivar" de `Proyecto QA Alpha`

**Resultado esperado:** El proyecto pasa a estado Inactivo. Se puede reactivar.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

## MÓDULO 6: DIRECTOR DE INVESTIGACIÓN

**Credenciales de prueba:** `dirgrupo2@sena.edu.co` / `Password123!`  
*(Director del grupo GIDESTH Agroindustrial)*

---

### CP-DI-01: Dashboard del grupo
**Pasos:**
1. Iniciar sesión como `dirgrupo2@sena.edu.co`
2. Verificar `/director`

**Resultado esperado:** Muestra nombre del grupo GIDESTH Agroindustrial, conteo de investigadores, productos por estado, y gráficas de producción.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-DI-02: Crear investigador asociado
**Pasos:**
1. Ir a `/director/investigadores/create`
2. Llenar:
   - Nombres: `Pedro`
   - Apellidos: `Investigador QA`
   - Documento: `77665544`
   - Email: `pinvestigador.qa@sena.edu.co`
   - Rol en grupo: `InvestigadorAsociado`
3. Guardar

**Resultado esperado:** Investigador creado con rol `investigador_asociado`, vinculado al grupo GIDESTH Agroindustrial. Aparece en `/director/investigadores`.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-DI-03: Cambiar rol interno del investigador en el grupo
**Prerreq.:** CP-DI-02 ejecutado  
**Pasos:**
1. En la lista de investigadores, clic en "Cambiar rol" de `Pedro Investigador QA`
2. Cambiar a `InvestigadorLider`
3. Confirmar

**Resultado esperado:** El rol interno del investigador en el grupo cambia a `InvestigadorLider`. El rol del sistema `investigador_asociado` permanece igual.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-DI-04: Desactivar investigador
**Prerreq.:** CP-DI-02 ejecutado  
**Pasos:**
1. En la lista, clic en "Toggle estado" de `Pedro Investigador QA`

**Resultado esperado:** Investigador pasa a estado Inactivo. No puede iniciar sesión.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-DI-05: Reactivar investigador
**Prerreq.:** CP-DI-04 ejecutado  
**Pasos:**
1. Toggle estado nuevamente

**Resultado esperado:** Investigador vuelve a estado Activo.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-DI-06: Restablecer contraseña de investigador
**Prerreq.:** CP-DI-02 ejecutado  
**Pasos:**
1. En la lista, clic en "Resetear contraseña" de `Pedro Investigador QA`
2. Anotar la nueva contraseña generada: `[NUEVA_CONTRASEÑA]`

**Resultado esperado:** Contraseña restablecida. Se muestra o envía la nueva contraseña. El investigador puede iniciar sesión con ella.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-DI-07: Desvincular investigador del grupo
**Prerreq.:** CP-DI-02 ejecutado (usar otro investigador para no perder el creado)  
**Pasos:**
1. En la lista, clic en "Desvincular" del investigador
2. Confirmar

**Resultado esperado:** El investigador ya no aparece en la lista del grupo. Su cuenta de usuario permanece activa pero sin asignación al grupo.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-DI-08: Ver listado de productos para revisión
**Prerreq.:** Debe existir al menos un `GroupProduct` creado por el investigador (CP-INV-09)  
**Pasos:**
1. Ir a `/director/productos`

**Resultado esperado:** Lista de productos del grupo con su estado de revisión. Incluye: título, autor, año y estado (Pendiente/EnRevision/Aprobado/Rechazado).  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-DI-09: Ver detalle de producto
**Prerreq.:** CP-DI-08 ejecutado  
**Pasos:**
1. Clic en un producto de la lista

**Resultado esperado:** Detalle del producto con: metadatos completos, tipología Minciencias, área de conocimiento, evidencia descargable, historial de revisiones.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-DI-10: Marcar producto "En Revisión"
**Prerreq.:** CP-DI-08 — producto en estado Pendiente  
**Pasos:**
1. En el detalle del producto, clic en "Marcar en revisión"

**Resultado esperado:** Estado cambia a `EnRevision`. Se registra en el historial de revisiones con el director como revisor y la fecha.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-DI-11: Aprobar producto
**Prerreq.:** CP-DI-10 ejecutado (producto en EnRevision)  
**Pasos:**
1. En el detalle del producto en revisión, clic en "Aprobar"
2. Confirmar

**Resultado esperado:** Estado cambia a `Aprobado`. Historial actualizado. El investigador puede ver el estado aprobado en su módulo.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-DI-12: Rechazar producto con observación
**Prerreq.:** Debe haber otro producto en estado Pendiente o EnRevision  
**Pasos:**
1. En el detalle del producto, clic en "Rechazar"
2. Ingresar observación: `Falta incluir la bibliografía en formato APA.`
3. Confirmar

**Resultado esperado:** Estado cambia a `Rechazado`. La observación queda visible en el historial y en el módulo del investigador.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-DI-13: Subir documento del grupo
**Pasos:**
1. Ir a `/director/documentos`
2. Clic en "Subir documento"
3. Nombre: `Plan Operativo Grupo QA 2026`
4. Archivo: *(PDF)*
5. Guardar

**Resultado esperado:** Documento aparece en la lista del grupo.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-DI-14: Eliminar documento del grupo
**Prerreq.:** CP-DI-13 ejecutado  
**Pasos:**
1. Clic en "Eliminar" del documento `Plan Operativo Grupo QA 2026`

**Resultado esperado:** Documento eliminado de la lista.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-DI-15: Crear macroproyecto
**Pasos:**
1. Ir a `/director/macroproyectos/create`
2. Llenar:
   - Código: `MP-QA-2026`
   - Nombre: `Macroproyecto QA 2026`
3. Guardar

**Resultado esperado:** Macroproyecto aparece en `/director/macroproyectos` con estado Activo.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-DI-16: Desactivar/activar macroproyecto
**Prerreq.:** CP-DI-15 ejecutado  
**Pasos:**
1. Clic en "Desactivar" del macroproyecto `Macroproyecto QA 2026`

**Resultado esperado:** Estado cambia a Inactivo. Clic en "Activar" lo regresa a Activo.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-DI-17: Exportar reporte del grupo en CSV
**Pasos:**
1. Ir a `/director/reportes`
2. Clic en "Exportar CSV"

**Resultado esperado:** Se descarga un archivo CSV con los datos del grupo de investigación.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-DI-18: Exportar reporte del grupo en PDF
**Pasos:**
1. Ir a `/director/reportes`
2. Clic en "Exportar PDF"

**Resultado esperado:** Se descarga un archivo PDF con el reporte formal del grupo.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

## MÓDULO 7: INVESTIGADOR ASOCIADO

**Credenciales de prueba:** `investigador@sena.edu.co` / `Password123!`  
*(Vinculado al grupo GIDESTH Agroindustrial)*

---

### CP-INV-01: Dashboard del investigador
**Pasos:**
1. Iniciar sesión como `investigador@sena.edu.co`
2. Verificar `/investigador`

**Resultado esperado:** Muestra mis productos por estado, mis proyectos, producción por año y tendencia de aprobaciones. Muestra contador de bandeja de semilleros.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-INV-02: Crear proyecto del grupo
**Pasos:**
1. Ir a `/investigador/proyectos/create`
2. Llenar:
   - Nombre: `Proyecto Investigador QA`
   - Descripción: `Proyecto de prueba creado por el investigador`
   - Línea de investigación: TIC aplicado al desarrollo sostenible
   - Línea tecnológica: TIC's e Inteligencia Artificial...
   - Área temática: TIC
   - Modalidad: En curso
   - Tipo de investigación: Innovación
   - Fecha inicio: 2026-02-01
   - Fecha fin: 2026-11-30
3. Guardar

**Resultado esperado:** Proyecto creado. El investigador aparece automáticamente como autor. Aparece en `/investigador/proyectos`.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-INV-03: Ver detalle del proyecto
**Prerreq.:** CP-INV-02 ejecutado  
**Pasos:**
1. Clic en `Proyecto Investigador QA`

**Resultado esperado:** Vista de detalle con autores, evidencias y estado del proyecto.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-INV-04: Editar proyecto
**Prerreq.:** CP-INV-02 ejecutado  
**Pasos:**
1. Clic en "Editar" del proyecto
2. Cambiar descripción
3. Guardar

**Resultado esperado:** Descripción actualizada en el detalle.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-INV-05: Ver autores del proyecto (API JSON)
**Prerreq.:** CP-INV-02 ejecutado  
**Pasos:**
1. Ir a `/investigador/proyectos/{id}/autores` (reemplazar `{id}` con el ID del proyecto)

**Resultado esperado:** Respuesta JSON con la lista de autores del proyecto.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-INV-06: Subir evidencia al proyecto
**Prerreq.:** CP-INV-02 ejecutado  
**Pasos:**
1. En el detalle del proyecto, ir a evidencias
2. Subir un PDF: `Carta de aval QA`
3. Guardar

**Resultado esperado:** Evidencia aparece en la lista de evidencias del proyecto con opción de descarga.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-INV-07: Descargar evidencia del proyecto
**Prerreq.:** CP-INV-06 ejecutado  
**Pasos:**
1. Clic en "Descargar" de `Carta de aval QA`

**Resultado esperado:** Archivo descargado al equipo.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-INV-08: Eliminar evidencia del proyecto
**Prerreq.:** CP-INV-06 ejecutado  
**Pasos:**
1. Clic en "Eliminar" de la evidencia

**Resultado esperado:** Evidencia eliminada de la lista.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-INV-09: Registrar producto del grupo (GroupProduct)
**Prerreq.:** CP-INV-02 ejecutado  
**Pasos:**
1. Ir a `/investigador/productos/create`
2. Llenar:
   - Proyecto: `Proyecto Investigador QA`
   - Título: `Software de Gestión QA`
   - Descripción: `Producto de software desarrollado como resultado del proyecto`
   - Año de publicación: `2026`
   - Tipología Minciencias: Desarrollo Tecnológico e Innovación
   - Subcategoría: Software
   - Gran área de conocimiento: Ingeniería y Tecnología
   - Área de conocimiento: Ingeniería mecánica
   - Tiene repositorio: Sí
   - URL repositorio: `https://github.com/ejemplo/qa`
   - Evidencia: *(subir PDF)*
   - Autoriza datos: Sí
3. Guardar

**Resultado esperado:** Producto registrado con estado `Pendiente` de revisión. Aparece en `/investigador/productos`.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-INV-10: Ver detalle del producto
**Prerreq.:** CP-INV-09 ejecutado  
**Pasos:**
1. Clic en `Software de Gestión QA`

**Resultado esperado:** Detalle con todos los metadatos, tipología Minciencias, evidencia descargable e historial de revisión (vacío inicialmente).  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-INV-11: Editar producto
**Prerreq.:** CP-INV-09 ejecutado, producto en estado Pendiente  
**Pasos:**
1. Clic en "Editar" del producto
2. Cambiar descripción
3. Guardar

**Resultado esperado:** Descripción actualizada. Solo posible si el estado es Pendiente (no Aprobado).  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-INV-12: Ver estados de mis productos
**Pasos:**
1. Ir a `/investigador/estados`

**Resultado esperado:** Vista mostrando todos los productos del investigador con su estado actual de revisión y las observaciones del director (si las hay).  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-INV-13: Bandeja de semilleros — ver productos asignados
**Prerreq.:** CP-LS-12 ejecutado (líder asignó producto al investigador)  
**Pasos:**
1. Ir a `/investigador/productos/bandeja`

**Resultado esperado:** El producto `Producto QA del Semillero` (creado en CP-LS-08) aparece en la bandeja con estado `Asignado`.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-INV-14: Formalizar producto del semillero
**Prerreq.:** CP-INV-13 ejecutado  
**Pasos:**
1. En la bandeja, clic en "Formalizar" del producto del semillero
2. Agregar metadatos:
   - Tipología Minciencias: Generación de Nuevo Conocimiento
   - Subcategoría: Artículos de investigación
   - Gran área: Ciencias Naturales
   - Área: Ciencias de la computación
   - Año publicación: 2026
3. Guardar

**Resultado esperado:** El producto pasa a ser un `GroupProduct` en el módulo del director para revisión. Desaparece de la bandeja.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-INV-15: Subir evidencia al producto
**Prerreq.:** CP-INV-09 ejecutado  
**Pasos:**
1. En el detalle del producto, ir a evidencias
2. Subir evidencia: `Certificado de publicación QA`

**Resultado esperado:** Evidencia registrada y visible en el detalle del producto.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-INV-16: Eliminar evidencia del producto
**Prerreq.:** CP-INV-15 ejecutado  
**Pasos:**
1. Eliminar la evidencia recién subida

**Resultado esperado:** Evidencia eliminada.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-INV-17: Finalizar proyecto
**Prerreq.:** CP-INV-02 ejecutado  
**Pasos:**
1. En el detalle del proyecto, clic en "Finalizar proyecto"
2. Confirmar

**Resultado esperado:** El proyecto cambia a estado `Finalizado`. No se puede editar una vez finalizado.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-INV-18: Exportar reporte de productos en CSV
**Pasos:**
1. Ir a `/investigador/reportes`
2. Clic en "Exportar CSV"

**Resultado esperado:** Se descarga archivo CSV con los productos del investigador.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-INV-19: Exportar reporte de productos en PDF
**Pasos:**
1. Ir a `/investigador/reportes`
2. Clic en "Exportar PDF"

**Resultado esperado:** Se descarga archivo PDF con el reporte del investigador.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-INV-20: Filtrar reporte por estado
**Pasos:**
1. Ir a `/investigador/reportes/estado`

**Resultado esperado:** Vista de productos agrupados por estado de revisión (Pendiente, EnRevision, Aprobado, Rechazado).  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

## MÓDULO 8: PRUEBAS DE FLUJO COMPLETO (E2E)

Estas pruebas verifican el flujo completo de negocio de extremo a extremo, involucrando múltiples roles.

---

### CP-E2E-01: Flujo completo de producto — del semillero al grupo aprobado

**Descripción:** Verificar el flujo completo: asesor crea → líder aprueba → investigador formaliza → director aprueba.

| Paso | Rol | Acción | URL |
|------|-----|--------|-----|
| 1 | Asesor (`asesorsem`) | Crear proyecto en el semillero | `/asesor-semillero/proyectos/create` |
| 2 | Asesor (`asesorsem`) | Registrar producto vinculado al proyecto | `/asesor-semillero/productos/create` |
| 3 | Líder (`lidersem`) | Ver producto en la lista | `/lider-semillero/productos` |
| 4 | Líder (`lidersem`) | Aprobar el producto | `/lider-semillero/productos/{id}` → Aprobar |
| 5 | Líder (`lidersem`) | Asignar al investigador | → Asignar a investigador (`investigador@sena.edu.co`) |
| 6 | Investigador (`investigador`) | Ver producto en bandeja | `/investigador/productos/bandeja` |
| 7 | Investigador (`investigador`) | Formalizar producto con metadatos Minciencias | `/investigador/productos/formalizar/{id}` |
| 8 | Director (`dirgrupo2`) | Ver producto en lista de revisión | `/director/productos` |
| 9 | Director (`dirgrupo2`) | Marcar en revisión | → En revisión |
| 10 | Director (`dirgrupo2`) | Aprobar el producto | → Aprobar |
| 11 | Investigador (`investigador`) | Verificar estado "Aprobado" en sus productos | `/investigador/estados` |

**Resultado esperado en cada paso:**

| Paso | Resultado esperado |
|------|--------------------|
| 1 | Proyecto creado en estado "En curso" |
| 2 | Producto registrado en estado "Pendiente" |
| 3 | Producto visible en la lista del líder |
| 4 | Estado cambia a "Aprobado" por el líder |
| 5 | Producto asignado al investigador |
| 6 | Producto aparece en la bandeja del investigador |
| 7 | Producto formalizado como GroupProduct en estado "Pendiente" |
| 8 | GroupProduct visible para el director |
| 9 | Estado cambia a "EnRevision" |
| 10 | Estado cambia a "Aprobado" |
| 11 | Investigador ve su producto como "Aprobado" |

**Estado general:** ⬜

---

### CP-E2E-02: Flujo de rechazo y corrección de producto

| Paso | Rol | Acción |
|------|-----|--------|
| 1 | Asesor | Crear y subir producto |
| 2 | Líder | Rechazar producto con observación: *"Falta firma del tutor"* |
| 3 | Asesor | Verificar que el estado del producto es "Rechazado" |
| 4 | Asesor | Editar el producto (corregir) y subir nuevo archivo |
| 5 | Asesor | El estado vuelve a "Pendiente" automáticamente |
| 6 | Líder | Aprobar el producto corregido |

**Estado general:** ⬜

---

### CP-E2E-03: Flujo completo de creación y gestión de semillero

| Paso | Rol | Acción |
|------|-----|--------|
| 1 | Director Semilleros | Crear semillero nuevo |
| 2 | Director Semilleros | Crear líder nuevo |
| 3 | Director Semilleros | Asignar líder al semillero |
| 4 | Líder (nuevo) | Iniciar sesión y verificar el semillero asignado |
| 5 | Líder | Registrar asesor externo |
| 6 | Asesor | Iniciar sesión (si tiene cuenta) y ver sus semilleros |
| 7 | Asesor | Establecer semillero activo |
| 8 | Asesor | Registrar aprendiz |
| 9 | Asesor | Crear proyecto |
| 10 | Asesor | Vincular aprendiz al proyecto |

**Estado general:** ⬜

---

## MÓDULO 9: PRUEBAS DE SEGURIDAD

---

### CP-SEG-01: No acceder a datos de otro centro
**Pasos:**
1. Iniciar sesión como `ydmoreno@sena.edu.co` (Centro Agro)
2. Intentar acceder a `/admin/usuarios` — solo usuarios de Agro visibles
3. Cerrar sesión
4. Iniciar sesión como `jovalenciap@sena.edu.co` (Centro Industria)
5. Verificar que solo ve usuarios de Centro Industria

**Resultado esperado:** En ningún caso un administrador puede ver usuarios del otro centro.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-SEG-02: No acceder a módulo de otro rol
**Pasos:**
1. Iniciar sesión como `investigador@sena.edu.co`
2. Intentar acceder a `/admin/dashboard`
3. Intentar acceder a `/director`
4. Intentar acceder a `/director-semilleros`

**Resultado esperado:** Todas las URLs redirigen a `/investigador` o retornan 403. Ninguna carga el panel de otro rol.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-SEG-03: Subida de archivo ejecutable bloqueada
**Prerreq.:** Tener acceso a cualquier formulario de carga de archivo  
**Pasos:**
1. Ir a un formulario de carga (ej: `/asesor-semillero/productos/create`)
2. Intentar subir un archivo `.php`, `.exe` o `.js`

**Resultado esperado:** Error de validación: "El archivo debe ser de tipo: pdf, doc, docx...". El archivo NO se sube.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-SEG-04: Prevención de acceso sin autenticación
**Pasos:**
1. Sin iniciar sesión, intentar acceder directamente a `/admin/dashboard`
2. Intentar acceder a `/director/productos`
3. Intentar acceder a `/investigador/proyectos`

**Resultado esperado:** Todas las URLs redirigen a `/login`.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

### CP-SEG-05: Usuario inactivo bloqueado
**Prerreq.:** CP-ADM-09 ejecutado (usuario desactivado)  
**Pasos:**
1. Intentar iniciar sesión con el usuario desactivado

**Resultado esperado:** Acceso bloqueado con mensaje de cuenta inactiva.  
**Resultado obtenido:** ___  
**Estado:** ⬜

---

## RESUMEN DE EJECUCIÓN

| Módulo | Total casos | Pasaron | Fallaron | Pendientes |
|--------|------------|---------|----------|------------|
| 0 — Autenticación | 7 | | | |
| 1 — Super Admin | 4 | | | |
| 2 — Administrador | 12 | | | |
| 3 — Director Semilleros | 11 | | | |
| 4 — Líder Semillero | 17 | | | |
| 5 — Asesor Semillero | 20 | | | |
| 6 — Director Investigación | 18 | | | |
| 7 — Investigador Asociado | 20 | | | |
| 8 — Flujos E2E | 3 | | | |
| 9 — Seguridad | 5 | | | |
| **TOTAL** | **117** | | | |

---

**Ejecutado por:** ___________________  
**Fecha de ejecución:** ___________________  
**Versión del sistema probada:** ___________________  
**Observaciones generales:** ___________________
