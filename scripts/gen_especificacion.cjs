'use strict';
const path = require('path');
const fs   = require('fs');
const {
  Document, Packer, Paragraph, TextRun, Table, TableRow, TableCell,
  AlignmentType, WidthType, BorderStyle, PageNumber,
  Footer, ShadingType, PageBreak, TableLayoutType,
  convertInchesToTwip, UnderlineType, HeadingLevel
} = require('docx');

const FONT  = 'Calibri';
const BLUE1 = '1F3864';
const BLUE2 = '2E5B8A';
const BLUE3 = '4472C4';
const TBLHDR= 'D6E4F0';
const BLACK = '000000';
const GRAY  = 'F5F5F5';
const PT    = n => n * 2;
const CM    = n => convertInchesToTwip(n / 2.54);

const R = (text, o = {}) => new TextRun({
  text: String(text ?? ''), font: FONT,
  size: PT(o.size ?? 10), bold: o.bold ?? false,
  italics: o.italic ?? false, color: o.color ?? BLACK,
  underline: o.ul ? { type: UnderlineType.SINGLE } : undefined,
});
const P = (children, o = {}) => {
  const runs = typeof children === 'string' ? [R(children, o)]
             : Array.isArray(children) ? children : [children];
  return new Paragraph({
    alignment: o.align ?? AlignmentType.LEFT,
    spacing: { before: o.before ?? 40, after: o.after ?? 40 },
    indent: o.indent ? { left: CM(o.indent) } : undefined,
    children: runs,
  });
};
const BLANK = (n=1) => Array.from({length:n}, () => P('',{before:0,after:0}));
const PB = () => new Paragraph({ children: [new PageBreak()] });
const H1 = t => new Paragraph({
  heading: HeadingLevel.HEADING_1, spacing: { before: PT(14), after: PT(8) },
  children: [R(t, { bold:true, size:15, color:BLUE1 })],
});
const H2 = t => new Paragraph({
  spacing: { before: PT(10), after: PT(6) },
  children: [R(t, { bold:true, size:13, color:BLUE2 })],
});

const BORDER = {
  top:    { style: BorderStyle.SINGLE, size:4, color:'BDC6D6' },
  bottom: { style: BorderStyle.SINGLE, size:4, color:'BDC6D6' },
  left:   { style: BorderStyle.SINGLE, size:4, color:'BDC6D6' },
  right:  { style: BorderStyle.SINGLE, size:4, color:'BDC6D6' },
};
const TC = (text, o = {}) => {
  const isHdr = o.header ?? false;
  const fill  = o.fill ?? (isHdr ? TBLHDR : undefined);
  const p = new Paragraph({
    alignment: o.align ?? AlignmentType.LEFT,
    spacing: { before:40, after:40 },
    children: [ R(text, { bold: o.bold ?? isHdr, size: o.size ?? 10,
                           color: isHdr ? BLUE1 : (o.color ?? BLACK) }) ],
  });
  return new TableCell({
    width: o.w ? { size: o.w, type: WidthType.PERCENTAGE } : undefined,
    borders: BORDER,
    shading: fill ? { type: ShadingType.CLEAR, fill, color:fill } : undefined,
    margins: { top:60, bottom:60, left:100, right:100 },
    columnSpan: o.span,
    children: [ p ],
  });
};
const TR = cells => new TableRow({ children: cells });

// ── Case table builder ────────────────────────────────────────────────────────
function caseTable(c) {
  return new Table({
    layout: TableLayoutType.FIXED,
    width: { size:100, type:WidthType.PERCENTAGE },
    rows: [
      TR([ TC('ID CASO', {header:true, w:18}), TC(c.id, {bold:true, color:BLUE1, w:18}),
           TC('MÓDULO', {header:true, w:18}), TC(c.modulo, {w:46}) ]),
      TR([ TC('Funcionalidad', {header:true, w:18}), TC(c.func, {span:3, w:82}) ]),
      TR([ TC('Descripción', {header:true, w:18}), TC(c.desc, {span:3, w:82}) ]),
      TR([ TC('Precondiciones', {header:true, w:18}), TC(c.pre, {span:3, w:82}) ]),
      TR([ TC('Datos de Entrada', {header:true, w:18}), TC(c.datos, {span:3, w:82}) ]),
      TR([ TC('Pasos', {header:true, w:18}), TC(c.pasos, {span:3, w:82}) ]),
      TR([ TC('Resultado Esperado', {header:true, w:18}), TC(c.esperado, {span:3, w:82}) ]),
      TR([ TC('Resultado Obtenido', {header:true, w:18, fill:GRAY}), TC('', {span:3, w:82, fill:GRAY}) ]),
      TR([ TC('Estado', {header:true, w:18}), TC('⬜ PENDIENTE', {span:3, w:82}) ]),
    ],
  });
}

// ── All 117 cases ─────────────────────────────────────────────────────────────
const CASES = [

// ══════════════════════════════════════════════════════════════════════════════
// MÓDULO 0 — AUTENTICACIÓN (7 casos)
// ══════════════════════════════════════════════════════════════════════════════
{id:'CP-AUTH-01', modulo:'MOD-01 Autenticación', func:'Login exitoso — rol super_administrador',
 desc:'Verificar que el super administrador puede ingresar y es redirigido a su dashboard.',
 pre:'BD inicializada con seeders. Usuario superadmin@sena.edu.co activo.',
 datos:'Email: superadmin@sena.edu.co | Contraseña: Password123!',
 pasos:'1. Ir a http://localhost/login\n2. Ingresar email: superadmin@sena.edu.co\n3. Ingresar contraseña: Password123!\n4. Clic en "Iniciar sesión"',
 esperado:'Redirige a http://localhost/super-admin/dashboard. Se muestra el panel del Super Administrador con estadísticas globales.'},

{id:'CP-AUTH-02', modulo:'MOD-01 Autenticación', func:'Login exitoso — rol administrador_sistema (CEFA)',
 desc:'Verificar que el administrador del sistema CEFA puede ingresar y accede al dashboard admin.',
 pre:'BD con seeders. Usuario ydmoreno@sena.edu.co activo, centro CEFA (9116).',
 datos:'Email: ydmoreno@sena.edu.co | Contraseña: Password123!',
 pasos:'1. Ir a http://localhost/login\n2. Email: ydmoreno@sena.edu.co\n3. Contraseña: Password123!\n4. Clic en "Iniciar sesión"',
 esperado:'Redirige a http://localhost/admin/dashboard y muestra las métricas del Centro de Formación Agroindustrial (CEFA).'},

{id:'CP-AUTH-03', modulo:'MOD-01 Autenticación', func:'Login exitoso — rol director_semilleros',
 desc:'Verificar redirección correcta para el director de semilleros.',
 pre:'Usuario directorsem@sena.edu.co activo, centro CEFA.',
 datos:'Email: directorsem@sena.edu.co | Contraseña: Password123!',
 pasos:'1. Ir a http://localhost/login\n2. Email: directorsem@sena.edu.co\n3. Contraseña: Password123!\n4. Clic en "Iniciar sesión"',
 esperado:'Redirige a http://localhost/director-semilleros/ y muestra el dashboard del Director de Semilleros.'},

{id:'CP-AUTH-04', modulo:'MOD-01 Autenticación', func:'Login exitoso — rol lider_semillero',
 desc:'Verificar que el líder de semillero es redirigido a su módulo.',
 pre:'Usuario lidersem@sena.edu.co activo, centro CEFA.',
 datos:'Email: lidersem@sena.edu.co | Contraseña: Password123!',
 pasos:'1. Ir a http://localhost/login\n2. Email: lidersem@sena.edu.co\n3. Contraseña: Password123!\n4. Clic en "Iniciar sesión"',
 esperado:'Redirige a http://localhost/lider-semillero/ y muestra el dashboard del Líder de Semillero.'},

{id:'CP-AUTH-05', modulo:'MOD-01 Autenticación', func:'Login con credenciales incorrectas',
 desc:'Verificar que las credenciales inválidas no permiten el acceso.',
 pre:'Servidor en ejecución. No se requiere usuario activo.',
 datos:'Email: superadmin@sena.edu.co | Contraseña: ClaveIncorrecta99',
 pasos:'1. Ir a http://localhost/login\n2. Email: superadmin@sena.edu.co\n3. Contraseña: ClaveIncorrecta99\n4. Clic en "Iniciar sesión"',
 esperado:'Permanece en http://localhost/login. Se muestra mensaje de error: "Credenciales incorrectas" o similar. No se crea sesión.'},

{id:'CP-AUTH-06', modulo:'MOD-01 Autenticación', func:'Login con usuario inactivo — debe ser bloqueado',
 desc:'Verificar que un usuario con estado inactivo no puede iniciar sesión.',
 pre:'Existe un usuario con estado=inactivo en la BD (creado manualmente o via toggle). Email de prueba: usuario_inactivo@sena.edu.co.',
 datos:'Email de usuario inactivo | Contraseña: Password123!',
 pasos:'1. Crear usuario con estado inactivo desde el admin (o ejecutar: User::where("email","...")->update(["estado","inactivo"]))\n2. Ir a http://localhost/login\n3. Ingresar email y contraseña del usuario inactivo\n4. Clic en "Iniciar sesión"',
 esperado:'El middleware ensure.active bloquea el acceso. Redirige a /login con mensaje indicando cuenta inactiva.'},

{id:'CP-AUTH-07', modulo:'MOD-01 Autenticación', func:'Logout — cierre de sesión',
 desc:'Verificar que al hacer logout se destruye la sesión y redirige al login.',
 pre:'Usuario autenticado (cualquier rol).',
 datos:'N/A — sesión activa.',
 pasos:'1. Iniciar sesión con ydmoreno@sena.edu.co / Password123!\n2. Verificar acceso a http://localhost/admin/dashboard\n3. Hacer clic en el botón "Cerrar sesión" (menú de usuario)\n4. Confirmar el logout',
 esperado:'Redirige a http://localhost/login. Intentar acceder a http://localhost/admin/dashboard redirige nuevamente a /login (sesión destruida).'},

// ══════════════════════════════════════════════════════════════════════════════
// MÓDULO 1 — SUPER ADMINISTRADOR (4 casos)
// ══════════════════════════════════════════════════════════════════════════════
{id:'CP-SA-01', modulo:'MOD-02 Super Administrador', func:'Dashboard global con estadísticas de todos los centros',
 desc:'El super admin visualiza métricas agregadas de todos los Training Centers.',
 pre:'Autenticado como superadmin@sena.edu.co. Existen centros CEFA (9116) e Industria (9527) en BD.',
 datos:'Sesión activa como super_administrador.',
 pasos:'1. Iniciar sesión como superadmin@sena.edu.co\n2. Navegar a http://localhost/super-admin/dashboard',
 esperado:'Muestra estadísticas de TODOS los centros: total de usuarios, semilleros, grupos de investigación sin filtro por centro. No debe mostrar "Sin datos".'},

{id:'CP-SA-02', modulo:'MOD-02 Super Administrador', func:'Listar administradores del sistema',
 desc:'Verificar que el super admin puede ver todos los administradores registrados.',
 pre:'Autenticado como superadmin@sena.edu.co. Existen ydmoreno@sena.edu.co y jovalenciap@sena.edu.co con rol administrador_sistema.',
 datos:'Sesión activa como super_administrador.',
 pasos:'1. Navegar a http://localhost/super-admin/administradores',
 esperado:'Lista los administradores: Yolanda Moreno (CEFA 9116) y Jorge Valencia (Industria 9527). Se muestra su centro, estado y acciones.'},

{id:'CP-SA-03', modulo:'MOD-02 Super Administrador', func:'Crear nuevo administrador del sistema',
 desc:'El super admin puede registrar un nuevo administrador asignado a un centro.',
 pre:'Autenticado como superadmin@sena.edu.co. Existen los dos centros de formación.',
 datos:'Email: nuevo.admin@sena.edu.co | Contraseña: Password123! | Nombre: Nuevo Admin | Centro: CEFA (9116) | Documento: 11223344',
 pasos:'1. Ir a http://localhost/super-admin/administradores/crear\n2. Completar formulario con los datos de entrada\n3. Clic en "Guardar"',
 esperado:'El administrador es creado con rol administrador_sistema y training_center_id = id del CEFA. Redirige al listado con mensaje de éxito.'},

{id:'CP-SA-04', modulo:'MOD-02 Super Administrador', func:'Activar/desactivar administrador',
 desc:'El super admin puede cambiar el estado activo/inactivo de un administrador.',
 pre:'Autenticado como superadmin@sena.edu.co. Existe ydmoreno@sena.edu.co activo.',
 datos:'ID del usuario ydmoreno@sena.edu.co.',
 pasos:'1. Ir a http://localhost/super-admin/administradores\n2. Localizar a Yolanda Moreno\n3. Clic en el botón de toggle de estado (activo → inactivo)\n4. Confirmar acción',
 esperado:'El estado cambia a inactivo. La tabla se actualiza. Al intentar login con ydmoreno@sena.edu.co, el middleware ensure.active bloquea el acceso.'},

// ══════════════════════════════════════════════════════════════════════════════
// MÓDULO 2 — ADMINISTRADOR DEL SISTEMA (12 casos)
// ══════════════════════════════════════════════════════════════════════════════
{id:'CP-ADM-01', modulo:'MOD-03 Administrador Sistema', func:'Dashboard del administrador (métricas del centro)',
 desc:'El administrador ve estadísticas solo de su Training Center.',
 pre:'Autenticado como ydmoreno@sena.edu.co (CEFA 9116).',
 datos:'Sesión activa como administrador_sistema.',
 pasos:'1. Iniciar sesión con ydmoreno@sena.edu.co / Password123!\n2. Navegar a http://localhost/admin/dashboard',
 esperado:'Muestra métricas exclusivas del Centro de Formación Agroindustrial (CEFA). No se muestran datos del Centro de Industria (9527).'},

{id:'CP-ADM-02', modulo:'MOD-03 Administrador Sistema', func:'Listar usuarios del centro con búsqueda Livewire',
 desc:'Verificar que el listado Livewire muestra solo usuarios del centro propio y filtra por búsqueda.',
 pre:'Autenticado como ydmoreno@sena.edu.co.',
 datos:'Búsqueda: "Carlos"',
 pasos:'1. Ir a http://localhost/admin/usuarios-manage\n2. Verificar que lista solo usuarios del CEFA\n3. Escribir "Carlos" en el campo de búsqueda',
 esperado:'Lista solo usuarios con training_center_id = CEFA. Al buscar "Carlos", filtra y muestra Carlos Rodríguez (directorsem@sena.edu.co). No aparece personal del centro Industria.'},

{id:'CP-ADM-03', modulo:'MOD-03 Administrador Sistema', func:'Crear usuario con rol director_semilleros',
 desc:'El administrador crea un usuario y le asigna el rol director_semilleros.',
 pre:'Autenticado como ydmoreno@sena.edu.co.',
 datos:'Email: nuevo.director@sena.edu.co | Contraseña: Password123! | Nombre: Ana Director | Apellido: Prueba | Documento: 99887766 | Rol: director_semilleros',
 pasos:'1. Ir a http://localhost/admin/usuarios/create\n2. Completar formulario\n3. Seleccionar rol director_semilleros\n4. Clic en "Guardar"',
 esperado:'Usuario creado con training_center_id heredado del admin (CEFA). El nuevo usuario puede iniciar sesión y accede a /director-semilleros/.'},

{id:'CP-ADM-04', modulo:'MOD-03 Administrador Sistema', func:'Editar usuario existente',
 desc:'El administrador puede modificar datos de un usuario de su centro.',
 pre:'Autenticado como ydmoreno@sena.edu.co. Existe directorsem@sena.edu.co.',
 datos:'Cambiar teléfono de Carlos Rodríguez: 3001234567',
 pasos:'1. Ir a http://localhost/admin/usuarios/{id}/edit (ID de directorsem)\n2. Modificar campo teléfono\n3. Clic en "Actualizar"',
 esperado:'Datos actualizados correctamente. Redirige al listado con mensaje de éxito.'},

{id:'CP-ADM-05', modulo:'MOD-03 Administrador Sistema', func:'Cambiar estado activo/inactivo de usuario',
 desc:'Toggle de estado del usuario desde el listado de administración.',
 pre:'Autenticado como ydmoreno@sena.edu.co.',
 datos:'ID del usuario asesorsem@sena.edu.co.',
 pasos:'1. Ir a http://localhost/admin/usuarios\n2. Localizar a Pedro Herrera (asesorsem@sena.edu.co)\n3. Clic en el botón toggle de estado\n4. Confirmar',
 esperado:'Estado cambia de activo a inactivo (o viceversa). El cambio se persiste en la BD (campo estado).'},

{id:'CP-ADM-06', modulo:'MOD-03 Administrador Sistema', func:'Asignar rol a usuario existente',
 desc:'El administrador puede cambiar el rol de un usuario de su centro.',
 pre:'Autenticado como ydmoreno@sena.edu.co. Existe usuario sin rol asignado.',
 datos:'Asignar rol: investigador_asociado al usuario investigador@sena.edu.co',
 pasos:'1. Ir a http://localhost/admin/usuarios/{id}/asignar-rol (POST)\n2. Seleccionar rol investigador_asociado\n3. Confirmar',
 esperado:'Rol asignado correctamente mediante Spatie. El usuario ahora tiene el rol investigador_asociado en la tabla model_has_roles.'},

{id:'CP-ADM-07', modulo:'MOD-03 Administrador Sistema', func:'Listar grupos de investigación del centro',
 desc:'El admin puede ver los grupos de investigación de su centro.',
 pre:'Autenticado como ydmoreno@sena.edu.co. Existe grupo de investigación para CEFA.',
 datos:'Sesión activa.',
 pasos:'1. Ir a http://localhost/admin/research-groups',
 esperado:'Lista los grupos de investigación del CEFA. No muestra grupos del Centro de Industria.'},

{id:'CP-ADM-08', modulo:'MOD-03 Administrador Sistema', func:'Crear grupo de investigación',
 desc:'El administrador crea un nuevo grupo de investigación para su centro.',
 pre:'Autenticado como ydmoreno@sena.edu.co.',
 datos:'Nombre: "Grupo TIC Sostenible" | Descripción: "Investigación en TIC" | Director: dirgrupo2@sena.edu.co',
 pasos:'1. Ir a http://localhost/admin/research-groups/create\n2. Completar formulario\n3. Clic en "Guardar"',
 esperado:'Grupo creado con training_center_id del CEFA. Aparece en el listado.'},

{id:'CP-ADM-09', modulo:'MOD-03 Administrador Sistema', func:'Ver catálogos del sistema',
 desc:'El administrador puede acceder y gestionar catálogos simples.',
 pre:'Autenticado como ydmoreno@sena.edu.co.',
 datos:'Sesión activa.',
 pasos:'1. Ir a http://localhost/admin/catalogos/simples',
 esperado:'Se muestran los catálogos del sistema (tipos de documento, géneros, etc.). Página accesible sin error.'},

{id:'CP-ADM-10', modulo:'MOD-03 Administrador Sistema', func:'Revocar rol de usuario',
 desc:'El administrador puede quitar el rol asignado a un usuario.',
 pre:'Autenticado como ydmoreno@sena.edu.co. Usuario con rol asignado.',
 datos:'ID del usuario con rol + nombre del rol a revocar.',
 pasos:'1. POST a http://localhost/admin/usuarios/{id}/revocar-rol\n2. Enviar nombre del rol a revocar',
 esperado:'El rol es removido del usuario en Spatie. El usuario ya no puede acceder al módulo correspondiente.'},

{id:'CP-ADM-11', modulo:'MOD-03 Administrador Sistema', func:'Admin NO puede ver usuarios de otro centro',
 desc:'Verificar aislamiento: el admin de CEFA no ve usuarios del Centro de Industria.',
 pre:'Autenticado como ydmoreno@sena.edu.co (CEFA 9116). Existe jovalenciap@sena.edu.co en Industria (9527).',
 datos:'Sesión activa como admin de CEFA.',
 pasos:'1. Ir a http://localhost/admin/usuarios-manage\n2. Revisar listado completo\n3. Buscar "jovalenciap" o "Jorge Valencia"',
 esperado:'Jorge Valencia (jovalenciap@sena.edu.co) NO aparece en la lista. TrainingCenterAccess::scopeUserQueryForList filtra correctamente por training_center_id.'},

{id:'CP-ADM-12', modulo:'MOD-03 Administrador Sistema', func:'Asignar roles desde vista dedicada',
 desc:'El administrador usa la vista de asignación de roles para gestionar permisos.',
 pre:'Autenticado como ydmoreno@sena.edu.co.',
 datos:'Sesión activa.',
 pasos:'1. Ir a http://localhost/admin/usuarios/asignar-roles\n2. Seleccionar usuario del centro\n3. Asignar rol',
 esperado:'Vista de asignación cargada. El formulario lista solo roles válidos para el sistema. La asignación se guarda correctamente.'},

// ══════════════════════════════════════════════════════════════════════════════
// MÓDULO 3 — DIRECTOR DE SEMILLEROS (11 casos)
// ══════════════════════════════════════════════════════════════════════════════
{id:'CP-DS-01', modulo:'MOD-04 Director Semilleros', func:'Dashboard del director de semilleros',
 desc:'El director de semilleros ve el resumen de semilleros de su centro.',
 pre:'Autenticado como directorsem@sena.edu.co (CEFA 9116).',
 datos:'Sesión activa.',
 pasos:'1. Iniciar sesión con directorsem@sena.edu.co / Password123!\n2. Navegar a http://localhost/director-semilleros/',
 esperado:'Dashboard carga correctamente mostrando estadísticas de semilleros del CEFA: total semilleros, activos, inactivos.'},

{id:'CP-DS-02', modulo:'MOD-04 Director Semilleros', func:'Listar semilleros del centro',
 desc:'Verificar que el listado muestra solo semilleros del training_center del director.',
 pre:'Autenticado como directorsem@sena.edu.co. Existen semilleros del CEFA y de Industria.',
 datos:'Sesión activa.',
 pasos:'1. Ir a http://localhost/director-semilleros/semilleros',
 esperado:'Lista solo los semilleros con training_center_id = CEFA. No aparecen semilleros del Centro de Industria.'},

{id:'CP-DS-03', modulo:'MOD-04 Director Semilleros', func:'Crear nuevo semillero',
 desc:'El director puede crear un semillero con todos sus atributos.',
 pre:'Autenticado como directorsem@sena.edu.co.',
 datos:'Nombre: "Semillero TIC Verde" | Descripción: "Semillero de innovación tecnológica" | Jornada: mañana | Modalidad: presencial | Estado: activo',
 pasos:'1. Ir a http://localhost/director-semilleros/semilleros/create\n2. Completar el formulario con los datos\n3. Clic en "Guardar"',
 esperado:'Semillero creado con training_center_id del CEFA. Redirige al listado y aparece el nuevo semillero "Semillero TIC Verde".'},

{id:'CP-DS-04', modulo:'MOD-04 Director Semilleros', func:'Editar semillero existente',
 desc:'El director puede modificar los datos de un semillero.',
 pre:'Autenticado como directorsem@sena.edu.co. Existe el semillero creado en CP-DS-03.',
 datos:'Cambiar modalidad a "virtual" y descripción a "Semillero actualizado".',
 pasos:'1. Ir a http://localhost/director-semilleros/semilleros/{id}/edit\n2. Modificar modalidad y descripción\n3. Clic en "Actualizar"',
 esperado:'Cambios guardados. El semillero muestra modalidad: virtual y la nueva descripción.'},

{id:'CP-DS-05', modulo:'MOD-04 Director Semilleros', func:'Cambiar estado del semillero (toggle)',
 desc:'El director puede activar o desactivar un semillero.',
 pre:'Autenticado como directorsem@sena.edu.co. Semillero activo existente.',
 datos:'POST a /director-semilleros/semilleros/{id}/toggle-estado.',
 pasos:'1. Ir a http://localhost/director-semilleros/semilleros\n2. Localizar el semillero\n3. Clic en botón toggle de estado',
 esperado:'El estado cambia a inactivo. La visualización del estado en la tabla se actualiza.'},

{id:'CP-DS-06', modulo:'MOD-04 Director Semilleros', func:'Ver listado de líderes del centro',
 desc:'El director puede ver todos los líderes de semillero de su centro.',
 pre:'Autenticado como directorsem@sena.edu.co. Existe lidersem@sena.edu.co con rol lider_semillero en CEFA.',
 datos:'Sesión activa.',
 pasos:'1. Ir a http://localhost/director-semilleros/lideres',
 esperado:'Lista a Luis Fernando Bermúdez (lidersem@sena.edu.co) como líder del CEFA. No aparecen líderes del Centro de Industria.'},

{id:'CP-DS-07', modulo:'MOD-04 Director Semilleros', func:'Vincular líder a semillero (reasignación)',
 desc:'El director asigna un líder existente a un semillero.',
 pre:'Autenticado como directorsem@sena.edu.co. Existe semillero sin líder y lidersem@sena.edu.co.',
 datos:'Semillero ID: {id_semillero} | Líder: lidersem@sena.edu.co (ID: {id_lider})',
 pasos:'1. Ir a http://localhost/director-semilleros/vinculaciones\n2. Seleccionar semillero\n3. Asignar líder lidersem@sena.edu.co\n4. PUT a /director-semilleros/vinculaciones/{semillero}',
 esperado:'Líder asignado al semillero. Luis Bermúdez puede ahora ver el semillero en su módulo /lider-semillero/.'},

{id:'CP-DS-08', modulo:'MOD-04 Director Semilleros', func:'Subir documento institucional al semillero',
 desc:'El director puede cargar un documento PDF al semillero.',
 pre:'Autenticado como directorsem@sena.edu.co. Semillero existente.',
 datos:'Archivo: acta_constitutiva.pdf (menos de 2MB) | Nombre: "Acta Constitutiva 2026"',
 pasos:'1. Ir a http://localhost/director-semilleros/documentos/create\n2. Seleccionar semillero\n3. Seleccionar archivo PDF\n4. Clic en "Subir"',
 esperado:'Documento almacenado en storage. Aparece en http://localhost/director-semilleros/documentos con nombre y fecha de subida.'},

{id:'CP-DS-09', modulo:'MOD-04 Director Semilleros', func:'Ver documentos institucionales de semillero',
 desc:'El director puede listar los documentos subidos a los semilleros.',
 pre:'Autenticado como directorsem@sena.edu.co. Existe documento del CP-DS-08.',
 datos:'Sesión activa.',
 pasos:'1. Ir a http://localhost/director-semilleros/documentos',
 esperado:'Lista los documentos subidos con nombre, fecha y semillero asociado. Solo documentos del CEFA.'},

{id:'CP-DS-10', modulo:'MOD-04 Director Semilleros', func:'Generar reporte de semilleros (exportar)',
 desc:'El director puede generar un reporte exportado de todos sus semilleros.',
 pre:'Autenticado como directorsem@sena.edu.co. Semilleros con datos.',
 datos:'POST a /director-semilleros/reportes/exportar con parámetros de filtro.',
 pasos:'1. Ir a http://localhost/director-semilleros/reportes\n2. Seleccionar rango de fechas o filtros\n3. Clic en "Exportar"',
 esperado:'Se descarga archivo (PDF o Excel) con datos de semilleros del CEFA. El archivo no está vacío y los datos son correctos.'},

{id:'CP-DS-11', modulo:'MOD-04 Director Semilleros', func:'Director NO puede acceder a semilleros de otro centro',
 desc:'Verificar que directorsem (CEFA) no ve semilleros de dirsemillero (Industria).',
 pre:'Autenticado como directorsem@sena.edu.co (CEFA). Existen semilleros del Centro de Industria.',
 datos:'Sesión activa como director del CEFA.',
 pasos:'1. Ir a http://localhost/director-semilleros/semilleros\n2. Revisar listado completo',
 esperado:'Solo aparecen semilleros con training_center_id = CEFA (9116). Semilleros de Industria (9527) no son visibles.'},

// ══════════════════════════════════════════════════════════════════════════════
// MÓDULO 4 — LÍDER DE SEMILLERO (17 casos)
// ══════════════════════════════════════════════════════════════════════════════
{id:'CP-LS-01', modulo:'MOD-05 Líder Semillero', func:'Dashboard del líder de semillero',
 desc:'El líder ve el resumen de su semillero al ingresar.',
 pre:'Autenticado como lidersem@sena.edu.co. Semillero asignado existe.',
 datos:'Sesión activa.',
 pasos:'1. Iniciar sesión con lidersem@sena.edu.co / Password123!\n2. Navegar a http://localhost/lider-semillero/',
 esperado:'Dashboard carga con datos del semillero asignado: nombre, jornada, modalidad, cantidad de integrantes.'},

{id:'CP-LS-02', modulo:'MOD-05 Líder Semillero', func:'Ver información del semillero',
 desc:'El líder puede ver los datos de su semillero.',
 pre:'Autenticado como lidersem@sena.edu.co.',
 datos:'Sesión activa.',
 pasos:'1. Ir a http://localhost/lider-semillero/info-semillero',
 esperado:'Se muestran los datos del semillero: nombre, descripción, jornada, modalidad, estado. Solo el semillero propio.'},

{id:'CP-LS-03', modulo:'MOD-05 Líder Semillero', func:'Ver integrantes (aprendices) del semillero',
 desc:'El líder puede ver las tarjetas de integrantes con estado de proyecto.',
 pre:'Autenticado como lidersem@sena.edu.co. Semillero con aprendices vinculados.',
 datos:'Sesión activa.',
 pasos:'1. Ir a http://localhost/lider-semillero/integrantes',
 esperado:'Lista de aprendices del semillero con indicador visual de si tienen proyecto asignado o no (Con/Sin proyecto).'},

{id:'CP-LS-04', modulo:'MOD-05 Líder Semillero', func:'Ver asesores del semillero',
 desc:'El líder puede ver todos los asesores asignados a su semillero.',
 pre:'Autenticado como lidersem@sena.edu.co. Existe asesorsem@sena.edu.co vinculado.',
 datos:'Sesión activa.',
 pasos:'1. Ir a http://localhost/lider-semillero/asesores',
 esperado:'Lista a Pedro José Herrera (asesorsem@sena.edu.co) como asesor activo del semillero.'},

{id:'CP-LS-05', modulo:'MOD-05 Líder Semillero', func:'Añadir asesor al semillero',
 desc:'El líder puede vincular un nuevo asesor a su semillero.',
 pre:'Autenticado como lidersem@sena.edu.co. Existe un asesor no vinculado al semillero.',
 datos:'Email del asesor a vincular | Estado: activo',
 pasos:'1. Ir a http://localhost/lider-semillero/asesores\n2. Clic en "Agregar asesor"\n3. POST a http://localhost/lider-semillero/asesores con datos del asesor',
 esperado:'Asesor vinculado al semillero. Aparece en la tabla de asesores con estado activo.'},

{id:'CP-LS-06', modulo:'MOD-05 Líder Semillero', func:'Activar/desactivar asesor del semillero',
 desc:'El líder puede cambiar el estado activo/inactivo de un asesor.',
 pre:'Autenticado como lidersem@sena.edu.co. Asesor vinculado y activo.',
 datos:'PATCH a /lider-semillero/asesores/{vinculo}/toggle.',
 pasos:'1. Ir a http://localhost/lider-semillero/asesores\n2. Localizar asesor activo\n3. Clic en botón toggle\n4. Confirmar',
 esperado:'El estado del vínculo cambia a inactivo. El asesor inactivo no aparece en listados activos.'},

{id:'CP-LS-07', modulo:'MOD-05 Líder Semillero', func:'Eliminar asesor del semillero',
 desc:'El líder puede desvincular un asesor de su semillero.',
 pre:'Autenticado como lidersem@sena.edu.co. Asesor vinculado existente.',
 datos:'DELETE a /lider-semillero/asesores/{vinculo}.',
 pasos:'1. Ir a http://localhost/lider-semillero/asesores\n2. Clic en eliminar en el asesor a remover\n3. Confirmar eliminación',
 esperado:'El vínculo es eliminado. El asesor ya no aparece en la lista del semillero.'},

{id:'CP-LS-08', modulo:'MOD-05 Líder Semillero', func:'Ver proyectos vinculados al semillero',
 desc:'El líder puede ver todos los proyectos de su semillero (solo consulta).',
 pre:'Autenticado como lidersem@sena.edu.co. Existen proyectos en el semillero.',
 datos:'Sesión activa.',
 pasos:'1. Ir a http://localhost/lider-semillero/proyectos',
 esperado:'Lista los proyectos del semillero con nombre, estado y fecha. Sin botón de edición (solo visualización).'},

{id:'CP-LS-09', modulo:'MOD-05 Líder Semillero', func:'Listar productos del semillero',
 desc:'El líder ve todos los productos del semillero con su estado actual.',
 pre:'Autenticado como lidersem@sena.edu.co. Existen productos creados por el asesor.',
 datos:'Sesión activa.',
 pasos:'1. Ir a http://localhost/lider-semillero/productos',
 esperado:'Lista productos con columnas: nombre, tipo, estado, autor, fecha. Productos en estado "pendiente" visibles para revisión.'},

{id:'CP-LS-10', modulo:'MOD-05 Líder Semillero', func:'Aprobar producto (pendiente → aprobado)',
 desc:'El líder aprueba un producto en estado pendiente — Etapa 2 del flujo.',
 pre:'Autenticado como lidersem@sena.edu.co. Existe producto con estado=pendiente creado por asesorsem@sena.edu.co.',
 datos:'PATCH a /lider-semillero/productos/{producto}/aprobar.',
 pasos:'1. Ir a http://localhost/lider-semillero/productos\n2. Localizar producto en estado "pendiente"\n3. Clic en "Aprobar"\n4. Confirmar',
 esperado:'El estado del producto cambia a "aprobado" (o aprobado_lider). El producto desaparece de la cola de pendientes. Queda disponible para el investigador asociado.'},

{id:'CP-LS-11', modulo:'MOD-05 Líder Semillero', func:'Rechazar producto (pendiente → rechazado)',
 desc:'El líder rechaza un producto en estado pendiente.',
 pre:'Autenticado como lidersem@sena.edu.co. Existe producto con estado=pendiente.',
 datos:'PATCH a /lider-semillero/productos/{producto}/rechazar | Motivo de rechazo (si aplica).',
 pasos:'1. Ir a http://localhost/lider-semillero/productos\n2. Localizar producto pendiente\n3. Clic en "Rechazar"\n4. Confirmar',
 esperado:'El estado cambia a "rechazado". El asesor puede ver el estado rechazado al consultar sus productos.'},

{id:'CP-LS-12', modulo:'MOD-05 Líder Semillero', func:'Ver detalle de un producto',
 desc:'El líder puede ver el detalle completo de un producto.',
 pre:'Autenticado como lidersem@sena.edu.co. Producto existente.',
 datos:'GET a /lider-semillero/productos/{groupProduct}.',
 pasos:'1. Ir a http://localhost/lider-semillero/productos\n2. Clic en un producto para ver su detalle',
 esperado:'Se muestra vista de detalle con: nombre, descripción, tipo, autor, estado, evidencias adjuntas.'},

{id:'CP-LS-13', modulo:'MOD-05 Líder Semillero', func:'Asignar investigador a producto aprobado',
 desc:'El líder puede vincular un investigador asociado al producto aprobado para formalización.',
 pre:'Autenticado como lidersem@sena.edu.co. Producto aprobado. Existe investigador@sena.edu.co en el grupo.',
 datos:'POST a /lider-semillero/productos/{producto}/asignar-investigador | investigador_id: {id de investigador@sena.edu.co}.',
 pasos:'1. Ir a http://localhost/lider-semillero/productos\n2. Localizar producto aprobado\n3. Clic en "Asignar investigador"\n4. Seleccionar Felipe Mora (investigador@sena.edu.co)\n5. Confirmar',
 esperado:'Investigador asignado. El producto aparece en la bandeja del investigador en /investigador/productos/bandeja.'},

{id:'CP-LS-14', modulo:'MOD-05 Líder Semillero', func:'Subir archivo al semillero',
 desc:'El líder puede cargar archivos (PDF, imágenes) al repositorio del semillero.',
 pre:'Autenticado como lidersem@sena.edu.co.',
 datos:'Archivo: reglamento_interno.pdf (menor a 2MB) | Nombre: "Reglamento Interno 2026".',
 pasos:'1. Ir a http://localhost/lider-semillero/archivos\n2. Clic en "Subir archivo"\n3. POST a /lider-semillero/archivos con el archivo\n4. Confirmar subida',
 esperado:'Archivo guardado en storage/app. Aparece en la tabla de archivos con nombre, fecha y opción de descarga.'},

{id:'CP-LS-15', modulo:'MOD-05 Líder Semillero', func:'Descargar archivo del semillero',
 desc:'El líder puede descargar un archivo previamente subido.',
 pre:'Autenticado como lidersem@sena.edu.co. Existe archivo subido en CP-LS-14.',
 datos:'GET a /lider-semillero/archivos/{archivo}/descargar.',
 pasos:'1. Ir a http://localhost/lider-semillero/archivos\n2. Clic en "Descargar" del archivo "Reglamento Interno 2026"',
 esperado:'El navegador descarga el archivo reglamento_interno.pdf con el contenido correcto.'},

{id:'CP-LS-16', modulo:'MOD-05 Líder Semillero', func:'Subir documento interno (actas, informes)',
 desc:'El líder puede subir documentación interna del semillero.',
 pre:'Autenticado como lidersem@sena.edu.co.',
 datos:'Archivo: acta_reunion_01.pdf | Nombre: "Acta de Reunión Nro. 1".',
 pasos:'1. Ir a http://localhost/lider-semillero/doc-interna\n2. POST a /lider-semillero/doc-interna con el documento\n3. Confirmar',
 esperado:'Documento interno guardado. Aparece en la lista con nombre, tipo y fecha.'},

{id:'CP-LS-17', modulo:'MOD-05 Líder Semillero', func:'Eliminar documento interno',
 desc:'El líder puede eliminar un documento interno del semillero.',
 pre:'Autenticado como lidersem@sena.edu.co. Documento interno existente del CP-LS-16.',
 datos:'DELETE a /lider-semillero/doc-interna/{documento}.',
 pasos:'1. Ir a http://localhost/lider-semillero/doc-interna\n2. Clic en "Eliminar" del documento\n3. Confirmar eliminación',
 esperado:'Documento eliminado de la BD y del storage. Ya no aparece en el listado.'},

// ══════════════════════════════════════════════════════════════════════════════
// MÓDULO 5 — ASESOR DE SEMILLERO (20 casos)
// ══════════════════════════════════════════════════════════════════════════════
{id:'CP-AS-01', modulo:'MOD-06 Asesor Semillero', func:'Dashboard del asesor de semillero',
 desc:'El asesor ve el resumen de actividades al ingresar al sistema.',
 pre:'Autenticado como asesorsem@sena.edu.co (CEFA).',
 datos:'Sesión activa.',
 pasos:'1. Iniciar sesión con asesorsem@sena.edu.co / Password123!\n2. Navegar a http://localhost/asesor-semillero/dashboard',
 esperado:'Dashboard carga mostrando resumen: semilleros asignados, proyectos activos, productos pendientes.'},

{id:'CP-AS-02', modulo:'MOD-06 Asesor Semillero', func:'Ver semilleros asignados al asesor',
 desc:'El asesor puede ver los semilleros donde está asignado.',
 pre:'Autenticado como asesorsem@sena.edu.co. Vinculado al semillero del CEFA.',
 datos:'Sesión activa.',
 pasos:'1. Ir a http://localhost/asesor-semillero/mis-semilleros',
 esperado:'Lista el/los semilleros donde Pedro Herrera (asesorsem) está como asesor activo. Nombre, jornada, modalidad y estado visibles.'},

{id:'CP-AS-03', modulo:'MOD-06 Asesor Semillero', func:'Seleccionar semillero activo de trabajo',
 desc:'El asesor puede definir cuál semillero está activo para operar sobre él.',
 pre:'Autenticado como asesorsem@sena.edu.co. Vinculado a más de un semillero.',
 datos:'POST a /asesor-semillero/semillero-activo | seedling_id: {id del semillero}.',
 pasos:'1. Ir a http://localhost/asesor-semillero/mis-semilleros\n2. Clic en "Trabajar en este semillero"\n3. Confirmar selección',
 esperado:'El semillero seleccionado queda como activo en la sesión. Las rutas de proyectos y productos filtran datos de ese semillero.'},

{id:'CP-AS-04', modulo:'MOD-06 Asesor Semillero', func:'Ver aprendices del semillero activo',
 desc:'El asesor puede ver la lista de aprendices del semillero activo.',
 pre:'Autenticado como asesorsem@sena.edu.co. Semillero activo seleccionado con aprendices.',
 datos:'Sesión activa.',
 pasos:'1. Ir a http://localhost/asesor-semillero/aprendices',
 esperado:'Lista los aprendices del semillero activo con nombre, documento, género y estado.'},

{id:'CP-AS-05', modulo:'MOD-06 Asesor Semillero', func:'Registrar nuevo aprendiz',
 desc:'El asesor puede añadir un nuevo aprendiz al semillero.',
 pre:'Autenticado como asesorsem@sena.edu.co. Semillero activo.',
 datos:'Nombre: "Camilo" | Apellido: "Torres" | Documento: CC 1099887766 | Género: masculino | Programa: "ADSI"',
 pasos:'1. Ir a http://localhost/asesor-semillero/aprendices/create\n2. Completar formulario\n3. POST a /asesor-semillero/aprendices\n4. Guardar',
 esperado:'Aprendiz registrado en la BD. Aparece en el listado de aprendices del semillero.'},

{id:'CP-AS-06', modulo:'MOD-06 Asesor Semillero', func:'Editar datos de aprendiz',
 desc:'El asesor puede actualizar la información de un aprendiz.',
 pre:'Autenticado como asesorsem@sena.edu.co. Aprendiz registrado en CP-AS-05.',
 datos:'Actualizar programa de Camilo Torres a "Análisis de Sistemas".',
 pasos:'1. Ir a http://localhost/asesor-semillero/aprendices/{id}/edit\n2. Modificar campo programa\n3. PUT a /asesor-semillero/aprendices/{id}\n4. Guardar',
 esperado:'Datos actualizados correctamente en la BD. Redirige al listado.'},

{id:'CP-AS-07', modulo:'MOD-06 Asesor Semillero', func:'Desactivar aprendiz del semillero',
 desc:'El asesor puede cambiar el estado de un aprendiz a inactivo.',
 pre:'Autenticado como asesorsem@sena.edu.co. Aprendiz activo existente.',
 datos:'PATCH a /asesor-semillero/aprendices/{id}/desactivar.',
 pasos:'1. Ir a http://localhost/asesor-semillero/aprendices\n2. Clic en desactivar del aprendiz\n3. Confirmar',
 esperado:'El aprendiz cambia a estado inactivo. Puede reactivarse posteriormente.'},

{id:'CP-AS-08', modulo:'MOD-06 Asesor Semillero', func:'Listar proyectos del semillero activo',
 desc:'El asesor puede ver todos los proyectos del semillero activo.',
 pre:'Autenticado como asesorsem@sena.edu.co. Semillero con proyectos.',
 datos:'Sesión activa.',
 pasos:'1. Ir a http://localhost/asesor-semillero/proyectos',
 esperado:'Lista los proyectos del semillero activo con: nombre, estado, fecha inicio, cantidad de integrantes.'},

{id:'CP-AS-09', modulo:'MOD-06 Asesor Semillero', func:'Crear nuevo proyecto de semillero',
 desc:'El asesor puede crear un proyecto vinculado al semillero activo.',
 pre:'Autenticado como asesorsem@sena.edu.co. Semillero activo.',
 datos:'Nombre: "Sistema de Riego Inteligente" | Descripción: "Automatización de riego" | Fecha inicio: 2026-03-01 | Estado: activo',
 pasos:'1. Ir a http://localhost/asesor-semillero/proyectos/create\n2. Completar formulario\n3. POST a /asesor-semillero/proyectos\n4. Guardar',
 esperado:'Proyecto creado con seedling_id del semillero activo y tipo_origen=semillero. Aparece en el listado.'},

{id:'CP-AS-10', modulo:'MOD-06 Asesor Semillero', func:'Editar proyecto existente',
 desc:'El asesor puede modificar los datos de un proyecto.',
 pre:'Autenticado como asesorsem@sena.edu.co. Proyecto creado en CP-AS-09.',
 datos:'Actualizar descripción del proyecto "Sistema de Riego Inteligente".',
 pasos:'1. Ir a http://localhost/asesor-semillero/proyectos/{id}/edit\n2. Modificar descripción\n3. PUT a /asesor-semillero/proyectos/{id}\n4. Guardar',
 esperado:'Descripción actualizada. Redirige al listado con mensaje de éxito.'},

{id:'CP-AS-11', modulo:'MOD-06 Asesor Semillero', func:'Ver detalle de proyecto',
 desc:'El asesor puede ver los detalles completos de un proyecto.',
 pre:'Autenticado como asesorsem@sena.edu.co.',
 datos:'GET a /asesor-semillero/proyectos/{id}.',
 pasos:'1. Ir a http://localhost/asesor-semillero/proyectos\n2. Clic en el nombre del proyecto',
 esperado:'Vista detalle con: nombre, descripción, integrantes, estado, evidencias y productos asociados.'},

{id:'CP-AS-12', modulo:'MOD-06 Asesor Semillero', func:'Vincular integrante (aprendiz) a proyecto',
 desc:'El asesor vincula un aprendiz del semillero a un proyecto específico.',
 pre:'Autenticado como asesorsem@sena.edu.co. Proyecto y aprendices existentes.',
 datos:'POST a /asesor-semillero/proyectos/{id}/integrantes | user_id: {id_aprendiz}.',
 pasos:'1. Ir a http://localhost/asesor-semillero/proyectos/{id}/integrantes\n2. Seleccionar aprendiz\n3. Confirmar vinculación',
 esperado:'Aprendiz vinculado al proyecto. Aparece en el listado de integrantes del proyecto.'},

{id:'CP-AS-13', modulo:'MOD-06 Asesor Semillero', func:'Listar productos del semillero activo',
 desc:'El asesor puede ver todos los productos de los proyectos del semillero.',
 pre:'Autenticado como asesorsem@sena.edu.co.',
 datos:'Sesión activa.',
 pasos:'1. Ir a http://localhost/asesor-semillero/productos',
 esperado:'Lista productos con nombre, tipo, estado (pendiente/aprobado/rechazado) y proyecto asociado.'},

{id:'CP-AS-14', modulo:'MOD-06 Asesor Semillero', func:'Crear nuevo producto — Etapa 1 del flujo',
 desc:'El asesor crea un producto que inicia el flujo de aprobación.',
 pre:'Autenticado como asesorsem@sena.edu.co. Proyecto activo existente.',
 datos:'Nombre: "Artículo IEEE 2026" | Tipo: articulo | Proyecto: "Sistema de Riego" | Descripción: "Artículo de investigación" | Archivo: articulo_borrador.pdf',
 pasos:'1. Ir a http://localhost/asesor-semillero/productos/create\n2. Completar formulario\n3. Seleccionar proyecto "Sistema de Riego"\n4. Adjuntar PDF\n5. POST a /asesor-semillero/productos',
 esperado:'Producto creado con estado=pendiente. Aparece en la cola de revisión del líder en /lider-semillero/productos.'},

{id:'CP-AS-15', modulo:'MOD-06 Asesor Semillero', func:'Editar producto propio (estado pendiente)',
 desc:'El asesor puede editar un producto mientras esté en estado pendiente.',
 pre:'Autenticado como asesorsem@sena.edu.co. Producto en estado pendiente.',
 datos:'Actualizar título del producto a "Artículo IEEE 2026 - Versión 2".',
 pasos:'1. Ir a http://localhost/asesor-semillero/productos/{id}/edit\n2. Modificar nombre\n3. PUT a /asesor-semillero/productos/{id}',
 esperado:'Producto actualizado. El estado permanece en pendiente.'},

{id:'CP-AS-16', modulo:'MOD-06 Asesor Semillero', func:'Ver detalle del producto',
 desc:'El asesor puede ver el estado actual y los detalles de un producto.',
 pre:'Autenticado como asesorsem@sena.edu.co.',
 datos:'GET a /asesor-semillero/productos/{id}.',
 pasos:'1. Ir a http://localhost/asesor-semillero/productos\n2. Clic en el nombre del producto',
 esperado:'Vista detalle con: nombre, tipo, estado actual, descripción, archivo adjunto y evidencias.'},

{id:'CP-AS-17', modulo:'MOD-06 Asesor Semillero', func:'Subir evidencia a proyecto',
 desc:'El asesor puede adjuntar evidencias (fotos, actas) a un proyecto.',
 pre:'Autenticado como asesorsem@sena.edu.co. Proyecto activo.',
 datos:'Archivo: evidencia_reunion.jpg | Proyecto ID: {id_proyecto}.',
 pasos:'1. Ir a http://localhost/asesor-semillero/proyectos/{proyecto_id}/evidencias\n2. POST a /asesor-semillero/proyectos/{id}/evidencias\n3. Adjuntar imagen JPG\n4. Guardar',
 esperado:'Evidencia guardada en storage. Aparece en la lista de evidencias del proyecto.'},

{id:'CP-AS-18', modulo:'MOD-06 Asesor Semillero', func:'Descargar evidencia de proyecto',
 desc:'El asesor puede descargar una evidencia adjunta a un proyecto.',
 pre:'Autenticado como asesorsem@sena.edu.co. Evidencia subida en CP-AS-17.',
 datos:'GET a /asesor-semillero/proyectos/{proyecto_id}/evidencias/{evidencia_id}/descargar.',
 pasos:'1. Ir a http://localhost/asesor-semillero/proyectos/{id}/evidencias\n2. Clic en "Descargar" de la evidencia',
 esperado:'El navegador descarga el archivo evidencia_reunion.jpg con el contenido original.'},

{id:'CP-AS-19', modulo:'MOD-06 Asesor Semillero', func:'Exportar reporte del semillero',
 desc:'El asesor puede exportar el reporte del semillero activo.',
 pre:'Autenticado como asesorsem@sena.edu.co. Semillero con datos.',
 datos:'GET a /asesor-semillero/exportar/semilleros.',
 pasos:'1. Ir a http://localhost/asesor-semillero/exportar/semilleros',
 esperado:'Se descarga reporte con datos del semillero activo: proyectos, aprendices, productos por estado.'},

{id:'CP-AS-20', modulo:'MOD-06 Asesor Semillero', func:'Exportar reporte de proyectos',
 desc:'El asesor exporta el reporte de proyectos del semillero.',
 pre:'Autenticado como asesorsem@sena.edu.co.',
 datos:'GET a /asesor-semillero/exportar/proyectos.',
 pasos:'1. Ir a http://localhost/asesor-semillero/exportar/proyectos',
 esperado:'Se descarga reporte con todos los proyectos del semillero activo.'},

// ══════════════════════════════════════════════════════════════════════════════
// MÓDULO 6 — DIRECTOR DE INVESTIGACIÓN (18 casos)
// ══════════════════════════════════════════════════════════════════════════════
{id:'CP-DI-01', modulo:'MOD-07 Director Investigación', func:'Dashboard del director de investigación',
 desc:'El director ve el resumen de su grupo de investigación.',
 pre:'Autenticado como dirgrupo2@sena.edu.co (CEFA).',
 datos:'Sesión activa.',
 pasos:'1. Iniciar sesión con dirgrupo2@sena.edu.co / Password123!\n2. Navegar a http://localhost/director/',
 esperado:'Dashboard del Director de Investigación con estadísticas del grupo: investigadores, proyectos activos, productos pendientes de revisión.'},

{id:'CP-DI-02', modulo:'MOD-07 Director Investigación', func:'Listar investigadores del grupo',
 desc:'El director puede ver los investigadores asociados a su grupo.',
 pre:'Autenticado como dirgrupo2@sena.edu.co. Existe investigador@sena.edu.co en el grupo del CEFA.',
 datos:'Sesión activa.',
 pasos:'1. Ir a http://localhost/director/investigadores',
 esperado:'Lista a Felipe Augusto Mora Salinas (investigador@sena.edu.co) con rol InvestigadorAsociado en el grupo. Solo investigadores del CEFA.'},

{id:'CP-DI-03', modulo:'MOD-07 Director Investigación', func:'Crear/vincular nuevo investigador al grupo',
 desc:'El director puede añadir un investigador a su grupo.',
 pre:'Autenticado como dirgrupo2@sena.edu.co.',
 datos:'Email del nuevo investigador | Rol en grupo: InvestigadorAsociado',
 pasos:'1. Ir a http://localhost/director/investigadores/create\n2. Completar formulario\n3. POST a /director/investigadores\n4. Guardar',
 esperado:'Investigador vinculado al grupo. Aparece en http://localhost/director/investigadores.'},

{id:'CP-DI-04', modulo:'MOD-07 Director Investigación', func:'Editar investigador del grupo',
 desc:'El director puede modificar datos del investigador.',
 pre:'Autenticado como dirgrupo2@sena.edu.co. Investigador existente.',
 datos:'Cambiar rol de investigador@sena.edu.co a "joven_investigador".',
 pasos:'1. Ir a http://localhost/director/investigadores/{investigador}/edit\n2. Cambiar rol\n3. PUT /director/investigadores/{investigador}',
 esperado:'Rol actualizado en la BD correctamente.'},

{id:'CP-DI-05', modulo:'MOD-07 Director Investigación', func:'Cambiar rol del investigador en el grupo',
 desc:'El director puede cambiar el rol de un investigador en el grupo de investigación.',
 pre:'Autenticado como dirgrupo2@sena.edu.co.',
 datos:'PATCH a /director/investigadores/{investigador}/rol | nuevo_rol: coinvestigador.',
 pasos:'1. Ir a http://localhost/director/investigadores\n2. Seleccionar investigador\n3. Cambiar rol a coinvestigador\n4. Confirmar',
 esperado:'El rol en research_group_users se actualiza a coinvestigador.'},

{id:'CP-DI-06', modulo:'MOD-07 Director Investigación', func:'Desvincular investigador del grupo',
 desc:'El director puede remover un investigador de su grupo.',
 pre:'Autenticado como dirgrupo2@sena.edu.co. Investigador vinculado.',
 datos:'DELETE a /director/investigadores/{investigador}/desvincular.',
 pasos:'1. Ir a http://localhost/director/investigadores\n2. Clic en "Desvincular" del investigador\n3. Confirmar',
 esperado:'El registro en research_group_users es eliminado. El investigador ya no aparece en el grupo.'},

{id:'CP-DI-07', modulo:'MOD-07 Director Investigación', func:'Ver bandeja de productos para revisión final',
 desc:'El director ve los productos formalizados por el investigador pendientes de aprobación final.',
 pre:'Autenticado como dirgrupo2@sena.edu.co. Existen productos en estado formalizado o en_revision.',
 datos:'Sesión activa.',
 pasos:'1. Ir a http://localhost/director/productos',
 esperado:'Lista los productos del grupo con estado formalizado/en_revision. Aparecen nombre, tipo, investigador responsable, estado.'},

{id:'CP-DI-08', modulo:'MOD-07 Director Investigación', func:'Ver detalle de producto a revisar',
 desc:'El director puede ver todos los detalles de un producto antes de aprobarlo.',
 pre:'Autenticado como dirgrupo2@sena.edu.co. Producto en revisión.',
 datos:'GET a /director/productos/{producto}.',
 pasos:'1. Ir a http://localhost/director/productos\n2. Clic en el nombre del producto',
 esperado:'Vista detalle con: título, tipo, investigador, descripción, archivo adjunto, evidencias, historial de estados.'},

{id:'CP-DI-09', modulo:'MOD-07 Director Investigación', func:'Aprobar producto — Etapa 4 final',
 desc:'El director aprueba definitivamente un producto formalizado.',
 pre:'Autenticado como dirgrupo2@sena.edu.co. Producto con estado formalizado.',
 datos:'PATCH a /director/productos/{producto}/aprobar.',
 pasos:'1. Ir a http://localhost/director/productos\n2. Abrir detalle del producto\n3. Clic en "Aprobar definitivamente"\n4. Confirmar',
 esperado:'El estado del producto cambia a aprobado_final. Notificación de éxito. El producto aparece como aprobado en todos los módulos.'},

{id:'CP-DI-10', modulo:'MOD-07 Director Investigación', func:'Rechazar producto definitivamente',
 desc:'El director rechaza un producto formalizado.',
 pre:'Autenticado como dirgrupo2@sena.edu.co. Producto en estado formalizado.',
 datos:'PATCH a /director/productos/{producto}/rechazar.',
 pasos:'1. Ir a http://localhost/director/productos\n2. Abrir producto\n3. Clic en "Rechazar"\n4. Confirmar',
 esperado:'El estado cambia a rechazado_final. El investigador ve el estado en su módulo.'},

{id:'CP-DI-11', modulo:'MOD-07 Director Investigación', func:'Enviar producto a revisión (estado en_revision)',
 desc:'El director puede marcar un producto como "en revisión" antes de aprobarlo.',
 pre:'Autenticado como dirgrupo2@sena.edu.co. Producto formalizado.',
 datos:'PATCH a /director/productos/{producto}/en-revision.',
 pasos:'1. Ir a http://localhost/director/productos/{producto}\n2. Clic en "Enviar a revisión"',
 esperado:'Estado cambia a en_revision. Permite al director revisar con más detalle antes de la aprobación final.'},

{id:'CP-DI-12', modulo:'MOD-07 Director Investigación', func:'Descargar evidencia adjunta a producto',
 desc:'El director puede descargar evidencias del producto para su revisión.',
 pre:'Autenticado como dirgrupo2@sena.edu.co. Producto con evidencias adjuntas.',
 datos:'GET a /director/productos/{producto}/evidencias/{evidencia}/download.',
 pasos:'1. Ir a http://localhost/director/productos/{producto}\n2. En la sección de evidencias, clic en "Descargar"',
 esperado:'El archivo de evidencia se descarga correctamente.'},

{id:'CP-DI-13', modulo:'MOD-07 Director Investigación', func:'Subir documento institucional al grupo',
 desc:'El director puede subir documentos del grupo de investigación.',
 pre:'Autenticado como dirgrupo2@sena.edu.co.',
 datos:'Archivo: plan_investigacion_2026.pdf | Nombre: "Plan de Investigación 2026".',
 pasos:'1. Ir a http://localhost/director/documentos\n2. POST a /director/documentos\n3. Adjuntar PDF y guardar',
 esperado:'Documento guardado. Aparece en la lista de documentos del grupo.'},

{id:'CP-DI-14', modulo:'MOD-07 Director Investigación', func:'Listar documentos del grupo',
 desc:'El director puede ver todos los documentos del grupo de investigación.',
 pre:'Autenticado como dirgrupo2@sena.edu.co. Documento subido en CP-DI-13.',
 datos:'GET a /director/documentos.',
 pasos:'1. Ir a http://localhost/director/documentos',
 esperado:'Lista los documentos del grupo: nombre, tipo, fecha de subida y opciones de descarga/eliminar.'},

{id:'CP-DI-15', modulo:'MOD-07 Director Investigación', func:'Descargar documento del grupo',
 desc:'El director puede descargar un documento del grupo.',
 pre:'Autenticado como dirgrupo2@sena.edu.co. Documento en CP-DI-13.',
 datos:'GET a /director/documentos/{documento}/download.',
 pasos:'1. Ir a http://localhost/director/documentos\n2. Clic en "Descargar" del plan de investigación',
 esperado:'El archivo plan_investigacion_2026.pdf se descarga correctamente.'},

{id:'CP-DI-16', modulo:'MOD-07 Director Investigación', func:'Eliminar documento del grupo',
 desc:'El director puede eliminar documentos del grupo.',
 pre:'Autenticado como dirgrupo2@sena.edu.co. Documento existente.',
 datos:'DELETE a /director/documentos/{documento}.',
 pasos:'1. Ir a http://localhost/director/documentos\n2. Clic en "Eliminar"\n3. Confirmar',
 esperado:'Documento eliminado de BD y storage. Ya no aparece en el listado.'},

{id:'CP-DI-17', modulo:'MOD-07 Director Investigación', func:'Exportar reporte del grupo en PDF',
 desc:'El director puede exportar un reporte en PDF del grupo de investigación.',
 pre:'Autenticado como dirgrupo2@sena.edu.co. Grupo con datos.',
 datos:'GET a /director/reportes/exportar-pdf.',
 pasos:'1. Ir a http://localhost/director/reportes\n2. Clic en "Exportar PDF"',
 esperado:'Se descarga reporte PDF con: investigadores, proyectos, productos por estado del grupo.'},

{id:'CP-DI-18', modulo:'MOD-07 Director Investigación', func:'Exportar reporte del grupo en CSV',
 desc:'El director puede exportar un reporte en CSV del grupo.',
 pre:'Autenticado como dirgrupo2@sena.edu.co.',
 datos:'GET a /director/reportes/exportar-csv.',
 pasos:'1. Ir a http://localhost/director/reportes\n2. Clic en "Exportar CSV"',
 esperado:'Se descarga archivo .csv con los datos del grupo en formato tabular.'},

// ══════════════════════════════════════════════════════════════════════════════
// MÓDULO 7 — INVESTIGADOR ASOCIADO (20 casos)
// ══════════════════════════════════════════════════════════════════════════════
{id:'CP-INV-01', modulo:'MOD-08 Investigador Asociado', func:'Dashboard del investigador asociado',
 desc:'El investigador ve el resumen de su actividad en el grupo.',
 pre:'Autenticado como investigador@sena.edu.co (CEFA).',
 datos:'Sesión activa.',
 pasos:'1. Iniciar sesión con investigador@sena.edu.co / Password123!\n2. Navegar a http://localhost/investigador/',
 esperado:'Dashboard carga con: proyectos del grupo, productos en proceso, bandeja de formalizaciones pendientes.'},

{id:'CP-INV-02', modulo:'MOD-08 Investigador Asociado', func:'Listar proyectos del grupo de investigación',
 desc:'El investigador puede ver los proyectos del grupo.',
 pre:'Autenticado como investigador@sena.edu.co.',
 datos:'Sesión activa.',
 pasos:'1. Ir a http://localhost/investigador/proyectos',
 esperado:'Lista los proyectos del grupo con nombre, estado, fechas. Solo proyectos del grupo del CEFA.'},

{id:'CP-INV-03', modulo:'MOD-08 Investigador Asociado', func:'Crear proyecto de investigación',
 desc:'El investigador puede crear un nuevo proyecto vinculado al grupo.',
 pre:'Autenticado como investigador@sena.edu.co.',
 datos:'Nombre: "IA para Agricultura" | Descripción: "Aplicaciones de IA en cultivos" | Fecha inicio: 2026-04-01',
 pasos:'1. Ir a http://localhost/investigador/proyectos/create\n2. Completar formulario\n3. POST a /investigador/proyectos\n4. Guardar',
 esperado:'Proyecto creado con research_group_id del grupo del CEFA y tipo_origen=grupo_investigacion.'},

{id:'CP-INV-04', modulo:'MOD-08 Investigador Asociado', func:'Editar proyecto de investigación',
 desc:'El investigador puede actualizar los datos de un proyecto propio.',
 pre:'Autenticado como investigador@sena.edu.co. Proyecto "IA para Agricultura" creado.',
 datos:'Actualizar descripción a "Aplicaciones de IA en cultivos de la región".',
 pasos:'1. Ir a http://localhost/investigador/proyectos/{proyecto}/edit\n2. Modificar descripción\n3. PUT a /investigador/proyectos/{proyecto}',
 esperado:'Descripción actualizada. Redirige al detalle del proyecto.'},

{id:'CP-INV-05', modulo:'MOD-08 Investigador Asociado', func:'Ver detalle de proyecto',
 desc:'El investigador puede ver la vista completa de un proyecto.',
 pre:'Autenticado como investigador@sena.edu.co.',
 datos:'GET a /investigador/proyectos/{proyecto}.',
 pasos:'1. Ir a http://localhost/investigador/proyectos\n2. Clic en el nombre del proyecto',
 esperado:'Vista detalle con: nombre, descripción, miembros, evidencias, productos asociados, estado.'},

{id:'CP-INV-06', modulo:'MOD-08 Investigador Asociado', func:'Subir evidencia a proyecto',
 desc:'El investigador puede adjuntar evidencias a un proyecto.',
 pre:'Autenticado como investigador@sena.edu.co. Proyecto existente.',
 datos:'Archivo: foto_campo.jpg | Proyecto ID: {id_proyecto}.',
 pasos:'1. Ir a http://localhost/investigador/proyectos/{proyecto}\n2. POST a /investigador/proyectos/{proyecto}/evidencias\n3. Adjuntar imagen\n4. Guardar',
 esperado:'Evidencia guardada. Aparece en el detalle del proyecto.'},

{id:'CP-INV-07', modulo:'MOD-08 Investigador Asociado', func:'Descargar evidencia de proyecto',
 desc:'El investigador puede descargar evidencias adjuntas.',
 pre:'Autenticado como investigador@sena.edu.co. Evidencia foto_campo.jpg subida.',
 datos:'GET a /investigador/proyectos/evidencias/{evidencia}/download.',
 pasos:'1. Ir a http://localhost/investigador/proyectos/{proyecto}\n2. Clic en "Descargar" en la evidencia',
 esperado:'El archivo foto_campo.jpg se descarga correctamente.'},

{id:'CP-INV-08', modulo:'MOD-08 Investigador Asociado', func:'Finalizar proyecto de investigación',
 desc:'El investigador puede marcar un proyecto como finalizado.',
 pre:'Autenticado como investigador@sena.edu.co. Proyecto activo con al menos un producto.',
 datos:'PATCH a /investigador/proyectos/{proyecto}/finalizar.',
 pasos:'1. Ir a http://localhost/investigador/proyectos/{proyecto}\n2. Clic en "Finalizar proyecto"\n3. Confirmar',
 esperado:'Estado del proyecto cambia a finalizado. El proyecto aparece como concluido en el listado.'},

{id:'CP-INV-09', modulo:'MOD-08 Investigador Asociado', func:'Ver bandeja de semilleros — productos para formalizar',
 desc:'El investigador ve los productos aprobados por el líder pendientes de formalización.',
 pre:'Autenticado como investigador@sena.edu.co. Producto aprobado por líder y asignado al investigador.',
 datos:'Sesión activa.',
 pasos:'1. Ir a http://localhost/investigador/productos/bandeja',
 esperado:'Lista los productos aprobados_lider asignados al investigador, listos para formalizar.'},

{id:'CP-INV-10', modulo:'MOD-08 Investigador Asociado', func:'Formalizar producto aprobado por líder — Etapa 3',
 desc:'El investigador formaliza un producto aprobado por el líder, completando la documentación.',
 pre:'Autenticado como investigador@sena.edu.co. Producto en estado aprobado_lider asignado.',
 datos:'GET a /investigador/productos/formalizar/{producto} | Adjuntar documentación final.',
 pasos:'1. Ir a http://localhost/investigador/productos/bandeja\n2. Seleccionar el producto aprobado\n3. Ir a http://localhost/investigador/productos/formalizar/{id}\n4. Completar formulario de formalización\n5. Adjuntar documento final\n6. Guardar',
 esperado:'Estado del producto cambia a formalizado. El producto aparece en la bandeja del director en /director/productos para aprobación final.'},

{id:'CP-INV-11', modulo:'MOD-08 Investigador Asociado', func:'Listar productos del grupo de investigación',
 desc:'El investigador puede ver todos los productos del grupo.',
 pre:'Autenticado como investigador@sena.edu.co.',
 datos:'Sesión activa.',
 pasos:'1. Ir a http://localhost/investigador/productos',
 esperado:'Lista los productos del grupo con nombre, tipo, estado actual y autor.'},

{id:'CP-INV-12', modulo:'MOD-08 Investigador Asociado', func:'Crear producto del grupo directamente',
 desc:'El investigador puede crear un producto nuevo directamente en el grupo.',
 pre:'Autenticado como investigador@sena.edu.co. Proyecto de grupo existente.',
 datos:'Nombre: "Ponencia SENA 2026" | Tipo: ponencia | Proyecto: "IA para Agricultura" | Descripción: "Presentación en congreso".',
 pasos:'1. Ir a http://localhost/investigador/productos/create\n2. Completar formulario\n3. POST a /investigador/productos\n4. Guardar',
 esperado:'Producto creado en estado inicial. Aparece en /investigador/productos.'},

{id:'CP-INV-13', modulo:'MOD-08 Investigador Asociado', func:'Ver detalle de producto de grupo',
 desc:'El investigador puede ver el detalle completo de un producto.',
 pre:'Autenticado como investigador@sena.edu.co.',
 datos:'GET a /investigador/productos/{producto}.',
 pasos:'1. Ir a http://localhost/investigador/productos\n2. Clic en el nombre del producto',
 esperado:'Vista detalle con título, tipo, estado, investigador responsable, evidencias adjuntas.'},

{id:'CP-INV-14', modulo:'MOD-08 Investigador Asociado', func:'Editar producto de grupo',
 desc:'El investigador puede modificar los datos de un producto.',
 pre:'Autenticado como investigador@sena.edu.co. Producto existente.',
 datos:'Actualizar descripción de "Ponencia SENA 2026".',
 pasos:'1. Ir a http://localhost/investigador/productos/{producto}/edit\n2. Modificar descripción\n3. PUT a /investigador/productos/{producto}',
 esperado:'Cambios guardados correctamente.'},

{id:'CP-INV-15', modulo:'MOD-08 Investigador Asociado', func:'Subir evidencia a producto',
 desc:'El investigador adjunta evidencias a un producto del grupo.',
 pre:'Autenticado como investigador@sena.edu.co. Producto existente.',
 datos:'Archivo: abstract_ponencia.pdf | Producto ID: {id_producto}.',
 pasos:'1. Ir a http://localhost/investigador/productos/{producto}\n2. POST a /investigador/productos/{producto}/evidencias\n3. Adjuntar PDF\n4. Guardar',
 esperado:'Evidencia guardada y asociada al producto. Aparece en la vista de detalle.'},

{id:'CP-INV-16', modulo:'MOD-08 Investigador Asociado', func:'Descargar evidencia de producto',
 desc:'El investigador puede descargar evidencias adjuntas a productos.',
 pre:'Autenticado como investigador@sena.edu.co. Evidencia abstract_ponencia.pdf subida.',
 datos:'GET a /investigador/evidencias/producto/{evidencia}/download.',
 pasos:'1. Ir a http://localhost/investigador/productos/{producto}\n2. Clic en "Descargar" de la evidencia',
 esperado:'El archivo abstract_ponencia.pdf se descarga correctamente.'},

{id:'CP-INV-17', modulo:'MOD-08 Investigador Asociado', func:'Ver estados y seguimiento de productos',
 desc:'El investigador puede monitorear el estado de todos sus productos.',
 pre:'Autenticado como investigador@sena.edu.co.',
 datos:'Sesión activa.',
 pasos:'1. Ir a http://localhost/investigador/estados',
 esperado:'Vista de seguimiento con los productos organizados por estado: pendiente, formalizado, aprobado_final, rechazado.'},

{id:'CP-INV-18', modulo:'MOD-08 Investigador Asociado', func:'Exportar reporte en PDF',
 desc:'El investigador puede exportar su reporte de actividad en PDF.',
 pre:'Autenticado como investigador@sena.edu.co.',
 datos:'GET a /investigador/reportes/exportar-pdf.',
 pasos:'1. Ir a http://localhost/investigador/reportes\n2. Clic en "Exportar PDF"',
 esperado:'Se descarga reporte PDF con proyectos y productos del investigador.'},

{id:'CP-INV-19', modulo:'MOD-08 Investigador Asociado', func:'Exportar reporte en CSV',
 desc:'El investigador puede exportar datos en formato CSV.',
 pre:'Autenticado como investigador@sena.edu.co.',
 datos:'GET a /investigador/reportes/exportar-csv.',
 pasos:'1. Ir a http://localhost/investigador/reportes\n2. Clic en "Exportar CSV"',
 esperado:'Se descarga archivo .csv con los datos del investigador en formato tabular.'},

{id:'CP-INV-20', modulo:'MOD-08 Investigador Asociado', func:'Eliminar evidencia de proyecto',
 desc:'El investigador puede eliminar una evidencia propia de un proyecto.',
 pre:'Autenticado como investigador@sena.edu.co. Evidencia foto_campo.jpg existente.',
 datos:'DELETE a /investigador/evidencias/proyecto/{evidencia}.',
 pasos:'1. Ir a http://localhost/investigador/proyectos/{proyecto}\n2. Localizar la evidencia foto_campo.jpg\n3. Clic en "Eliminar"\n4. Confirmar',
 esperado:'Evidencia eliminada de la BD y del storage. Ya no aparece en el proyecto.'},

// ══════════════════════════════════════════════════════════════════════════════
// MÓDULO 8 — FLUJOS END-TO-END (3 casos)
// ══════════════════════════════════════════════════════════════════════════════
{id:'CP-E2E-01', modulo:'MOD-09 Flujos E2E', func:'Flujo completo de aprobación de producto (4 etapas)',
 desc:'Verificar el ciclo completo desde creación por el asesor hasta aprobación final por el director.',
 pre:'BD con todos los usuarios del seeder activos y vinculados: asesorsem, lidersem, investigador, dirgrupo2 en el centro CEFA.',
 datos:'Producto: "Artículo E2E Test" | Tipo: articulo | Proyecto del semillero activo.',
 pasos:'ETAPA 1 — Asesor crea:\n1. Login asesorsem@sena.edu.co / Password123!\n2. POST /asesor-semillero/productos → estado=pendiente\n\nETAPA 2 — Líder aprueba:\n3. Login lidersem@sena.edu.co / Password123!\n4. GET /lider-semillero/productos → producto pendiente visible\n5. PATCH /lider-semillero/productos/{id}/aprobar → estado=aprobado\n\nETAPA 3 — Investigador formaliza:\n6. Login investigador@sena.edu.co / Password123!\n7. GET /investigador/productos/bandeja → producto aprobado visible\n8. GET /investigador/productos/formalizar/{id} → completar form\n9. Estado → formalizado\n\nETAPA 4 — Director aprueba final:\n10. Login dirgrupo2@sena.edu.co / Password123!\n11. GET /director/productos → producto formalizado visible\n12. PATCH /director/productos/{id}/aprobar → estado=aprobado_final',
 esperado:'El producto transita correctamente por los 4 estados: pendiente → aprobado → formalizado → aprobado_final. Cada rol solo ve el producto en la etapa que le corresponde.'},

{id:'CP-E2E-02', modulo:'MOD-09 Flujos E2E', func:'Flujo de rechazo por el líder de semillero',
 desc:'Verificar que el rechazo por el líder detiene el flujo correctamente.',
 pre:'Producto en estado pendiente creado por asesorsem@sena.edu.co.',
 datos:'Mismo producto del CP-E2E-01 (o uno nuevo).',
 pasos:'ETAPA 1 — Asesor crea:\n1. Login asesorsem@sena.edu.co / Password123!\n2. POST /asesor-semillero/productos → estado=pendiente\n\nETAPA 2 — Líder rechaza:\n3. Login lidersem@sena.edu.co / Password123!\n4. GET /lider-semillero/productos → pendiente visible\n5. PATCH /lider-semillero/productos/{id}/rechazar → estado=rechazado\n\nVerificar:\n6. Login investigador@sena.edu.co\n7. GET /investigador/productos/bandeja → producto rechazado NO aparece\n8. GET /director/productos → producto rechazado NO aparece',
 esperado:'Producto en estado rechazado. No aparece en la bandeja del investigador ni en la cola de revisión del director. El asesor puede ver el estado rechazado.'},

{id:'CP-E2E-03', modulo:'MOD-09 Flujos E2E', func:'Ciclo completo: crear semillero → proyectos → productos → aprobar',
 desc:'Verificar el flujo completo de creación de semillero hasta aprobación de producto.',
 pre:'BD con seeders. directorsem@sena.edu.co, lidersem@sena.edu.co, asesorsem@sena.edu.co activos.',
 datos:'Semillero nuevo de prueba E2E.',
 pasos:'1. Login directorsem@sena.edu.co → crear semillero en /director-semilleros/semilleros/create\n2. Vincular lidersem@sena.edu.co al semillero en /director-semilleros/vinculaciones\n3. Login asesorsem@sena.edu.co → seleccionar semillero activo\n4. Crear proyecto en /asesor-semillero/proyectos/create\n5. Crear producto en /asesor-semillero/productos/create (estado=pendiente)\n6. Login lidersem@sena.edu.co → aprobar producto en /lider-semillero/productos/{id}/aprobar\n7. Verificar estado final=aprobado',
 esperado:'Todos los pasos transitan sin error. Cada actor ve los datos correctos de su módulo. El producto queda en estado aprobado al final del ciclo.'},

// ══════════════════════════════════════════════════════════════════════════════
// MÓDULO 9 — SEGURIDAD Y CONTROL DE ACCESO (5 casos)
// ══════════════════════════════════════════════════════════════════════════════
{id:'CP-SEG-01', modulo:'MOD-10 Multi-tenancy y Seguridad', func:'Admin CEFA no puede ver usuarios del Centro de Industria',
 desc:'Verificar el aislamiento por training_center_id en el listado de usuarios.',
 pre:'BD con seeders. ydmoreno@sena.edu.co (CEFA) y jovalenciap@sena.edu.co (Industria).',
 datos:'Sesión activa como ydmoreno@sena.edu.co.',
 pasos:'1. Login ydmoreno@sena.edu.co / Password123!\n2. Ir a http://localhost/admin/usuarios-manage\n3. Revisar TODOS los usuarios del listado\n4. Buscar "jovalenciap" o "Jorge Valencia" o "9527"',
 esperado:'Jorge Valencia Parra (jovalenciap@sena.edu.co) NO aparece en el listado. TrainingCenterAccess::scopeUserQueryForList filtra por training_center_id=CEFA. Resultado: solo usuarios del CEFA visibles.'},

{id:'CP-SEG-02', modulo:'MOD-10 Multi-tenancy y Seguridad', func:'Director de semilleros no puede acceder a rutas del Director de Investigación',
 desc:'Verificar que el middleware de roles bloquea el acceso cruzado entre prefijos de ruta.',
 pre:'Autenticado como directorsem@sena.edu.co (rol: director_semilleros).',
 datos:'URL de intento: http://localhost/director/ (módulo del director_investigacion).',
 pasos:'1. Login directorsem@sena.edu.co / Password123!\n2. Intentar acceder directamente a http://localhost/director/\n3. Intentar acceder a http://localhost/director/productos',
 esperado:'HTTP 403 Forbidden o redirección a /dashboard o /login. El middleware role:director_investigacion bloquea el acceso al rol director_semilleros.'},

{id:'CP-SEG-03', modulo:'MOD-10 Multi-tenancy y Seguridad', func:'Acceso sin autenticación redirige a /login',
 desc:'Verificar que las rutas protegidas requieren autenticación.',
 pre:'Sin sesión activa (navegación anónima).',
 datos:'URLs protegidas: /admin/dashboard, /director/, /lider-semillero/, /investigador/',
 pasos:'1. Cerrar sesión (o navegar en modo incógnito)\n2. Intentar acceder a http://localhost/admin/dashboard\n3. Intentar acceder a http://localhost/director/\n4. Intentar acceder a http://localhost/lider-semillero/',
 esperado:'Todas las URLs redirigen a http://localhost/login. El middleware auth bloquea el acceso y no muestra datos del sistema.'},

{id:'CP-SEG-04', modulo:'MOD-10 Multi-tenancy y Seguridad', func:'Líder de CEFA no puede aprobar productos de semillero de Industria',
 desc:'Verificar que el aislamiento por training_center impide acciones sobre datos de otro centro.',
 pre:'Autenticado como lidersem@sena.edu.co (CEFA). Existe producto de semillero del Centro de Industria.',
 datos:'ID de producto perteneciente al Centro de Industria (9527).',
 pasos:'1. Login lidersem@sena.edu.co / Password123!\n2. Intentar PATCH a http://localhost/lider-semillero/productos/{id_producto_industria}/aprobar',
 esperado:'HTTP 403 Forbidden o redirección. El producto de Industria no aparece en el listado del líder del CEFA y no puede ser aprobado.'},

{id:'CP-SEG-05', modulo:'MOD-10 Multi-tenancy y Seguridad', func:'Asesor no puede acceder a rutas del Director de Semilleros',
 desc:'Verificar que el asesor de semillero no tiene acceso al módulo del director de semilleros.',
 pre:'Autenticado como asesorsem@sena.edu.co (rol: asesor_semillero).',
 datos:'URLs de intento: http://localhost/director-semilleros/semilleros, http://localhost/director-semilleros/lideres.',
 pasos:'1. Login asesorsem@sena.edu.co / Password123!\n2. Intentar acceder a http://localhost/director-semilleros/semilleros\n3. Intentar acceder a http://localhost/director-semilleros/lideres',
 esperado:'HTTP 403 Forbidden o redirección. El middleware role:director_semilleros bloquea el acceso al asesor. No puede ver ni modificar los semilleros del centro.'},
];

// ── Verificación: conteo de casos ─────────────────────────────────────────────
const total = CASES.length;
console.log(`Total casos definidos: ${total}`);

// ── Portada ───────────────────────────────────────────────────────────────────
const portada = [
  ...BLANK(4),
  P([R('SERVICIO NACIONAL DE APRENDIZAJE — SENA', {bold:true,size:16,color:BLUE1})], {align:AlignmentType.CENTER}),
  P([R('Centro de Formación Agroindustrial — CEFA', {size:12,color:BLUE2})], {align:AlignmentType.CENTER}),
  ...BLANK(2),
  P([R('ESPECIFICACIÓN DE CASOS DE PRUEBA', {bold:true,size:26,color:BLUE1})], {align:AlignmentType.CENTER}),
  P([R('SISTEMA SIGESI', {bold:true,size:18,color:BLUE2})], {align:AlignmentType.CENTER}),
  P([R('Sistema Documental de Semilleros de Investigación', {size:13,italic:true,color:BLUE3})], {align:AlignmentType.CENTER}),
  ...BLANK(3),
  P([R(`Total de casos de prueba: ${total}`, {bold:true,size:12})], {align:AlignmentType.CENTER}),
  P([R('Versión: 1.0  |  Fecha: 2026-04-08', {size:11,italic:true,color:'666666'})], {align:AlignmentType.CENTER}),
  ...BLANK(2),
  P([R('Elaborado por:', {bold:true,size:12,color:BLUE1})], {align:AlignmentType.CENTER}),
  P([R('Juan Esteban Aldana Cortes', {size:12})], {align:AlignmentType.CENTER}),
  PB(),
];

// ── Introducción ──────────────────────────────────────────────────────────────
const intro = [
  H1('INTRODUCCIÓN'),
  H2('Objetivo del documento'),
  P('Este documento contiene la especificación detallada de los ' + total + ' casos de prueba para el sistema SIGESI. Cada caso incluye precondiciones exactas, datos de entrada reales (emails y URLs del sistema), pasos numerados y resultado esperado.'),
  H2('Convenciones'),
  P('• Contraseña universal del seeder: Password123!', {indent:0.5}),
  P('• URL base: http://localhost (Laragon)', {indent:0.5}),
  P('• Centro CEFA: training_center código 9116', {indent:0.5}),
  P('• Centro Industria: training_center código 9527', {indent:0.5}),
  P('• Campo "Resultado Obtenido": se diligencia durante la ejecución', {indent:0.5}),
  P('• Estado inicial de todos los casos: ⬜ PENDIENTE', {indent:0.5}),
  ...BLANK(1),
  H2('Usuarios de prueba'),
  new Table({
    layout: TableLayoutType.FIXED,
    width:{size:100,type:WidthType.PERCENTAGE},
    rows:[
      TR([TC('Email',{header:true,w:38}),TC('Contraseña',{header:true,w:17}),TC('Rol',{header:true,w:30}),TC('Centro',{header:true,w:15})]),
      TR([TC('superadmin@sena.edu.co'),TC('Password123!'),TC('super_administrador'),TC('CEFA 9116')]),
      TR([TC('ydmoreno@sena.edu.co'),TC('Password123!'),TC('administrador_sistema'),TC('CEFA 9116')]),
      TR([TC('jovalenciap@sena.edu.co'),TC('Password123!'),TC('administrador_sistema'),TC('Ind. 9527')]),
      TR([TC('directorsem@sena.edu.co'),TC('Password123!'),TC('director_semilleros'),TC('CEFA 9116')]),
      TR([TC('dirsemillero@sena.edu.co'),TC('Password123!'),TC('director_semilleros'),TC('Ind. 9527')]),
      TR([TC('lidersem@sena.edu.co'),TC('Password123!'),TC('lider_semillero'),TC('CEFA 9116')]),
      TR([TC('liderIndustrialsem@sena.edu.co'),TC('Password123!'),TC('lider_semillero'),TC('Ind. 9527')]),
      TR([TC('asesorsem@sena.edu.co'),TC('Password123!'),TC('asesor_semillero'),TC('CEFA 9116')]),
      TR([TC('asesorIndu@sena.edu.co'),TC('Password123!'),TC('asesor_semillero'),TC('Ind. 9527')]),
      TR([TC('dirgrupo1@sena.edu.co'),TC('Password123!'),TC('director_investigacion'),TC('Ind. 9527')]),
      TR([TC('dirgrupo2@sena.edu.co'),TC('Password123!'),TC('director_investigacion'),TC('CEFA 9116')]),
      TR([TC('investigador@sena.edu.co'),TC('Password123!'),TC('investigador_asociado'),TC('CEFA 9116')]),
      TR([TC('investigadorIndu@sena.edu.co'),TC('Password123!'),TC('investigador_asociado'),TC('Ind. 9527')]),
    ],
  }),
  PB(),
];

// ── Index de casos ────────────────────────────────────────────────────────────
const indexRows = [
  TR([TC('ID',{header:true,w:15}),TC('Módulo',{header:true,w:30}),TC('Funcionalidad',{header:true,w:55})]),
  ...CASES.map(c => TR([TC(c.id),TC(c.modulo),TC(c.func)])),
];
const indexSection = [
  H1('ÍNDICE DE CASOS DE PRUEBA'),
  new Table({layout:TableLayoutType.FIXED, width:{size:100,type:WidthType.PERCENTAGE}, rows:indexRows}),
  PB(),
];

// ── Agrupar casos por módulo ──────────────────────────────────────────────────
const modules = {};
for (const c of CASES) {
  const mod = c.modulo;
  if (!modules[mod]) modules[mod] = [];
  modules[mod].push(c);
}

const caseSections = [];
for (const [mod, cases] of Object.entries(modules)) {
  caseSections.push(H1(mod));
  for (const c of cases) {
    caseSections.push(H2(c.id + ' — ' + c.func));
    caseSections.push(caseTable(c));
    caseSections.push(...BLANK(1));
  }
  caseSections.push(PB());
}

// ── Footer ────────────────────────────────────────────────────────────────────
const footer = new Footer({
  children:[new Paragraph({
    alignment:AlignmentType.CENTER,
    border:{top:{style:BorderStyle.SINGLE,size:4,color:'BDC6D6'}},
    spacing:{before:60},
    children:[
      R('SIGESI — Especificación Casos de Prueba v1.0  |  Pág. ',{size:9,color:'888888'}),
      new TextRun({children:[PageNumber.CURRENT],font:FONT,size:PT(9),color:'888888'}),
      R(' de ',{size:9,color:'888888'}),
      new TextRun({children:[PageNumber.TOTAL_PAGES],font:FONT,size:PT(9),color:'888888'}),
    ],
  })],
});

// ── Documento ─────────────────────────────────────────────────────────────────
const doc = new Document({
  numbering:{config:[]},
  styles:{
    default:{
      document:{
        run:{font:FONT,size:PT(10),color:BLACK},
        paragraph:{spacing:{line:276}},
      },
    },
  },
  sections:[{
    properties:{
      page:{margin:{top:CM(2.54),bottom:CM(2.54),left:CM(2.54),right:CM(2.54)}},
    },
    footers:{default:footer},
    children:[
      ...portada,
      ...intro,
      ...indexSection,
      ...caseSections,
    ],
  }],
});

// ── Guardar ───────────────────────────────────────────────────────────────────
const outDir  = path.resolve(__dirname,'..','docs','pruebas');
const outFile = path.join(outDir,'Especificacion_Casos_Prueba_SIGESI.docx');
fs.mkdirSync(outDir,{recursive:true});

Packer.toBuffer(doc).then(buf => {
  fs.writeFileSync(outFile, buf);
  console.log(`✓ Documento generado: ${outFile} (${(buf.length/1024).toFixed(1)} KB)`);
}).catch(err => {
  console.error('ERROR:', err.message);
  process.exit(1);
});
