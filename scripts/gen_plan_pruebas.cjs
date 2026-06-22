'use strict';
const path = require('path');
const fs   = require('fs');
const {
  Document, Packer, Paragraph, TextRun, Table, TableRow, TableCell,
  AlignmentType, HeadingLevel, WidthType, BorderStyle, PageNumber,
  Footer, Header, ShadingType, PageBreak, TableLayoutType,
  convertInchesToTwip, UnderlineType
} = require('docx');

// ── Constantes de estilo ──────────────────────────────────────────────────────
const FONT   = 'Calibri';
const BLUE1  = '1F3864'; // títulos principales
const BLUE2  = '2E5B8A'; // subtítulos
const BLUE3  = '4472C4'; // acento
const TBLHDR = 'D6E4F0'; // fondo cabecera tabla
const TBLALT = 'EBF4FB'; // fila alternada
const BLACK  = '000000';

const PT = n => n * 2;           // half-points
const CM = n => convertInchesToTwip(n / 2.54);

// ── Helpers de texto ──────────────────────────────────────────────────────────
const R = (text, opts = {}) => new TextRun({
  text: String(text ?? ''),
  font: FONT,
  size: PT(opts.size ?? 11),
  bold:    opts.bold    ?? false,
  italics: opts.italic  ?? false,
  color:   opts.color   ?? BLACK,
  underline: opts.underline ? { type: UnderlineType.SINGLE } : undefined,
});

const P = (children, opts = {}) => {
  const runs = typeof children === 'string' ? [R(children, opts)]
             : Array.isArray(children)      ? children
             : [children];
  return new Paragraph({
    alignment: opts.align ?? AlignmentType.LEFT,
    spacing:   { before: opts.before ?? 60, after: opts.after ?? 80 },
    indent:    opts.indent ? { left: CM(opts.indent) } : undefined,
    children:  runs,
  });
};

const BLANK = (n = 1) => Array.from({ length: n }, () => P('', { before: 0, after: 0 }));

const PB = () => new Paragraph({ children: [new PageBreak()] });

const H1 = text => new Paragraph({
  heading: HeadingLevel.HEADING_1,
  spacing: { before: PT(14), after: PT(8) },
  children: [R(text, { bold: true, size: 16, color: BLUE1 })],
});

const H2 = text => new Paragraph({
  heading: HeadingLevel.HEADING_2,
  spacing: { before: PT(10), after: PT(6) },
  children: [R(text, { bold: true, size: 13, color: BLUE2 })],
});

const H3 = text => new Paragraph({
  spacing: { before: PT(8), after: PT(4) },
  children: [R(text, { bold: true, size: 12, color: BLUE3 })],
});

// ── Helpers de tabla ──────────────────────────────────────────────────────────
const BORDER = {
  top:    { style: BorderStyle.SINGLE, size: 4, color: 'BDC6D6' },
  bottom: { style: BorderStyle.SINGLE, size: 4, color: 'BDC6D6' },
  left:   { style: BorderStyle.SINGLE, size: 4, color: 'BDC6D6' },
  right:  { style: BorderStyle.SINGLE, size: 4, color: 'BDC6D6' },
};

const TC = (text, opts = {}) => {
  const isHdr = opts.header ?? false;
  const fill  = opts.fill ?? (isHdr ? TBLHDR : undefined);
  const p = new Paragraph({
    alignment: opts.align ?? AlignmentType.LEFT,
    spacing: { before: 60, after: 60 },
    children: [ R(text, {
      bold:  opts.bold ?? isHdr,
      size:  opts.size ?? 10,
      color: isHdr ? BLUE1 : (opts.color ?? BLACK),
    }) ],
  });
  return new TableCell({
    width:   opts.width ? { size: opts.width, type: WidthType.PERCENTAGE } : undefined,
    borders: BORDER,
    shading: fill ? { type: ShadingType.CLEAR, fill, color: fill } : undefined,
    margins: { top: 60, bottom: 60, left: 120, right: 120 },
    columnSpan: opts.span,
    children: [ p ],
  });
};

const TR = cells => new TableRow({ children: cells });

const makeTable = (rows, widths) => new Table({
  layout: TableLayoutType.FIXED,
  width:  { size: 100, type: WidthType.PERCENTAGE },
  margins: { top: 0, bottom: 0, left: 0, right: 0 },
  rows,
});

// ── Portada ───────────────────────────────────────────────────────────────────
const portada = [
  ...BLANK(4),
  P([R('SERVICIO NACIONAL DE APRENDIZAJE — SENA', { bold: true, size: 18, color: BLUE1 })], { align: AlignmentType.CENTER, before: 0, after: 20 }),
  P([R('Centro de Formación Agroindustrial — CEFA', { size: 13, color: BLUE2 })], { align: AlignmentType.CENTER, before: 0, after: 60 }),
  new Paragraph({
    alignment: AlignmentType.CENTER,
    spacing: { before: 200, after: 200 },
    border: { bottom: { style: BorderStyle.SINGLE, size: 8, color: BLUE1 } },
    children: [],
  }),
  ...BLANK(1),
  P([R('PLAN DE PRUEBAS DE SOFTWARE', { bold: true, size: 28, color: BLUE1 })], { align: AlignmentType.CENTER, before: 40, after: 20 }),
  P([R('SISTEMA SIGESI', { bold: true, size: 20, color: BLUE2 })], { align: AlignmentType.CENTER, before: 0, after: 10 }),
  P([R('Sistema Documental de Semilleros de Investigación', { size: 14, color: BLUE3, italic: true })], { align: AlignmentType.CENTER, before: 0, after: 120 }),
  ...BLANK(3),
  P([R('Elaborado por:', { bold: true, size: 12, color: BLUE1 })], { align: AlignmentType.CENTER, before: 0, after: 10 }),
  P([R('Juan Esteban Aldana Cortes', { size: 12 })], { align: AlignmentType.CENTER, before: 0, after: 40 }),
  P([R('Año 2026', { size: 11, italic: true, color: '666666' })], { align: AlignmentType.CENTER, before: 0, after: 0 }),
  PB(),
];

// ── Control de versiones ──────────────────────────────────────────────────────
const tablaVersiones = [
  H1('CONTROL DE VERSIONES'),
  makeTable([
    TR([ TC('Versión', { header: true, width: 12 }), TC('Fecha', { header: true, width: 20 }), TC('Autor', { header: true, width: 38 }), TC('Descripción', { header: true, width: 30 }) ]),
    TR([ TC('1.0'), TC('2026-04-08'), TC('Juan Esteban Aldana Cortes'), TC('Versión inicial del Plan de Pruebas') ]),
  ]),
  ...BLANK(1),
  PB(),
];

// ── Sección 1 — Introducción ──────────────────────────────────────────────────
const seccion1 = [
  H1('1. INTRODUCCIÓN'),
  H2('1.1 Propósito'),
  P('Definir la estrategia, alcance, recursos y cronograma de las actividades de prueba para validar que el sistema SIGESI cumple con los requisitos funcionales y no funcionales establecidos. Este plan cubre la validación del sistema documental de semilleros e investigación del SENA, asegurando la correcta gestión de documentos, flujos de aprobación y control de acceso por roles.'),
  H2('1.2 Alcance'),
  P('El plan abarca los 10 módulos del sistema: autenticación, 7 módulos por rol de usuario, el flujo transversal de aprobación de productos (4 etapas) y la seguridad multi-tenant por Training Center. Se excluye la infraestructura de red y los sistemas externos al SIGESI.'),
  H2('1.3 Referencias'),
  P('• Especificación de Requisitos SIGESI v1.0', { indent: 0.5 }),
  P('• Documento de Arquitectura GIDESTH', { indent: 0.5 }),
  P('• Laravel 12 Documentation', { indent: 0.5 }),
  P('• Spatie Permission 6.24 — Documentación oficial', { indent: 0.5 }),
  P('• Estándar IEEE 829 para Documentación de Pruebas de Software', { indent: 0.5 }),
  H2('1.4 Definiciones'),
  makeTable([
    TR([ TC('Término', { header: true, width: 25 }), TC('Definición', { header: true, width: 75 }) ]),
    TR([ TC('Training Center'), TC('Centro de Formación al que pertenece un usuario y que delimita su acceso a datos') ]),
    TR([ TC('Multi-tenancy'), TC('Aislamiento de datos por training_center_id que impide acceso cruzado entre centros') ]),
    TR([ TC('Producto'), TC('Entregable académico/investigativo que sigue el flujo de aprobación de 4 etapas entre roles') ]),
    TR([ TC('Semillero'), TC('Grupo de aprendices e investigadores del SENA que desarrollan proyectos de investigación formativa') ]),
    TR([ TC('Rol'), TC('Perfil de usuario en el sistema (7 roles): super_administrador, administrador_sistema, director_semilleros, lider_semillero, asesor_semillero, director_investigacion, investigador_asociado') ]),
    TR([ TC('Estado'), TC('Valor del ciclo de vida de un producto o semillero: pendiente, aprobado_lider, rechazado_lider, formalizado, en_revision, aprobado_final, rechazado_final') ]),
  ]),
  ...BLANK(1),
  PB(),
];

// ── Sección 2 — Elementos a probar ───────────────────────────────────────────
const seccion2 = [
  H1('2. ELEMENTOS A PROBAR'),
  P('Los siguientes módulos del sistema SIGESI están sujetos a pruebas en este plan:'),
  ...BLANK(1),
  makeTable([
    TR([ TC('ID', { header: true, width: 12 }), TC('Módulo', { header: true, width: 30 }), TC('Descripción', { header: true, width: 58 }) ]),
    TR([ TC('MOD-01'), TC('Autenticación y Gestión de Sesión'), TC('Login por rol, logout, recuperación de contraseña, redirección post-login por rol, bloqueo de usuarios inactivos. Basado en Laravel Fortify.') ]),
    TR([ TC('MOD-02'), TC('Módulo Super Administrador'), TC('Dashboard global, gestión de Centros de Formación (CRUD), gestión global de usuarios de todos los centros, catálogos de catálogos institucionales.') ]),
    TR([ TC('MOD-03'), TC('Módulo Administrador del Sistema'), TC('Dashboard del centro propio, CRUD usuarios del centro, asignación de roles con training_center_id, gestión de grupos de investigación del centro.') ]),
    TR([ TC('MOD-04'), TC('Módulo Director de Semilleros'), TC('Dashboard semilleros del centro, CRUD semilleros (nombre, jornada, modalidad, estado), asignación de líder, documentos institucionales, reportes PDF/Excel.') ]),
    TR([ TC('MOD-05'), TC('Módulo Líder de Semillero'), TC('Dashboard semillero propio, lista de asesores, revisión y aprobación/rechazo de productos (Etapa 2 del flujo), documentos internos y archivos del semillero.') ]),
    TR([ TC('MOD-06'), TC('Módulo Asesor de Semillero'), TC('Dashboard, semilleros asignados, CRUD proyectos, creación de productos (Etapa 1), subida de evidencias, gestión de aprendices, exportación de reportes.') ]),
    TR([ TC('MOD-07'), TC('Módulo Director de Investigación'), TC('Dashboard grupo de investigación, gestión de investigadores del grupo, revisión y aprobación/rechazo definitivo de productos (Etapa 4), documentos del grupo.') ]),
    TR([ TC('MOD-08'), TC('Módulo Investigador Asociado'), TC('Dashboard, proyectos del grupo, formalización de productos aprobados por líder (Etapa 3), subida de evidencias, exportación de reportes.') ]),
    TR([ TC('MOD-09'), TC('Flujo de Aprobación de Productos'), TC('Ciclo completo de 4 etapas: Asesor crea → Líder aprueba/rechaza → Investigador formaliza → Director de Investigación aprueba/rechaza definitivo.') ]),
    TR([ TC('MOD-10'), TC('Multi-tenancy y Control de Acceso'), TC('Aislamiento de datos por training_center_id, verificación de middleware de roles, denegación de acceso cruzado entre centros, super_admin con acceso global.') ]),
  ]),
  ...BLANK(1),
  PB(),
];

// ── Sección 3 — Características a probar ─────────────────────────────────────
const seccion3 = [
  H1('3. CARACTERÍSTICAS A PROBAR'),
  H2('3.1 Características Funcionales'),
  makeTable([
    TR([ TC('ID', { header: true, width: 12 }), TC('Característica', { header: true, width: 30 }), TC('Módulo(s) asociado(s)', { header: true, width: 25 }), TC('Prioridad', { header: true, width: 15 }), TC('Criterio básico', { header: true, width: 18 }) ]),
    TR([ TC('FT-01'), TC('Autenticación por rol con redirección correcta'), TC('MOD-01'), TC('Alta'), TC('Cada rol redirige a su dashboard') ]),
    TR([ TC('FT-02'), TC('CRUD de Centros de Formación'), TC('MOD-02'), TC('Alta'), TC('Crear, editar, listar, eliminar centros') ]),
    TR([ TC('FT-03'), TC('CRUD de Usuarios con asignación de roles y training_center'), TC('MOD-02, MOD-03'), TC('Alta'), TC('training_center_id correcto en roles bound') ]),
    TR([ TC('FT-04'), TC('CRUD de Semilleros con asignación de líder'), TC('MOD-04'), TC('Alta'), TC('Semillero creado con jornada, modalidad, estado') ]),
    TR([ TC('FT-05'), TC('CRUD de Grupos de Investigación'), TC('MOD-03, MOD-07'), TC('Alta'), TC('Grupo vinculado a training_center correcto') ]),
    TR([ TC('FT-06'), TC('CRUD de Proyectos (semillero y grupo)'), TC('MOD-06, MOD-08'), TC('Media'), TC('tipo_origen asignado correctamente') ]),
    TR([ TC('FT-07'), TC('Ciclo completo de aprobación de productos (4 etapas)'), TC('MOD-09'), TC('Crítica'), TC('Estado avanza correctamente en cada etapa') ]),
    TR([ TC('FT-08'), TC('Subida y gestión de evidencias y documentos'), TC('MOD-04...08'), TC('Media'), TC('Archivo guardado y descargable') ]),
    TR([ TC('FT-09'), TC('Generación de reportes PDF y Excel'), TC('MOD-04, MOD-06, MOD-08'), TC('Media'), TC('Archivo descargado sin error, datos correctos') ]),
    TR([ TC('FT-10'), TC('Gestión de catálogos institucionales'), TC('MOD-02'), TC('Baja'), TC('EntityPosition, ExternalAdvisor, InvestigationType') ]),
  ]),
  ...BLANK(1),
  H2('3.2 Características No Funcionales'),
  makeTable([
    TR([ TC('ID', { header: true, width: 12 }), TC('Característica', { header: true, width: 35 }), TC('Descripción', { header: true, width: 38 }), TC('Prioridad', { header: true, width: 15 }) ]),
    TR([ TC('NFT-01'), TC('Aislamiento multi-tenant por training_center'), TC('Ningún usuario puede acceder a datos de otro Training Center'), TC('Crítica') ]),
    TR([ TC('NFT-02'), TC('Control de acceso por rol (Spatie Permission)'), TC('Ningún usuario puede acceder a rutas de un rol distinto al propio'), TC('Crítica') ]),
    TR([ TC('NFT-03'), TC('Consistencia de la interfaz Livewire + Flux'), TC('Componentes reactivos responden sin recarga de página, mensajes de error visibles'), TC('Media') ]),
  ]),
  ...BLANK(1),
  H2('3.3 Características excluidas'),
  P('• Rendimiento bajo carga masiva de usuarios concurrentes', { indent: 0.5 }),
  P('• Compatibilidad con navegadores obsoletos (Internet Explorer)', { indent: 0.5 }),
  P('• Integración con sistemas externos al SIGESI', { indent: 0.5 }),
  P('• Pruebas de penetración (pentest) a nivel de infraestructura', { indent: 0.5 }),
  ...BLANK(1),
  PB(),
];

// ── Sección 4 — Estrategia de pruebas ─────────────────────────────────────────
const seccion4 = [
  H1('4. ESTRATEGIA DE PRUEBAS'),
  H2('4.1 Tipos de prueba'),
  H3('4.1.1 Pruebas Funcionales'),
  P('Nivel: Sistema e Integración. Técnica: Caja negra basada en casos de uso por rol. Herramienta: PHPUnit 11.5.3 + navegador web. Cobertura objetivo: 100% de flujos críticos (FT-01 a FT-10).'),
  H3('4.1.2 Pruebas de Integración'),
  P('Verificación del flujo completo de aprobación de productos entre los 4 roles participantes. Verificación de la propagación correcta del training_center_id entre módulos. Herramienta: PHPUnit con RefreshDatabase (SQLite en memoria).'),
  H3('4.1.3 Pruebas de Seguridad'),
  P('• Intentos de acceso cruzado entre Training Centers (debe ser denegado con HTTP 403/redirect)', { indent: 0.5 }),
  P('• Acceso a rutas de otros roles sin autenticación del rol requerido', { indent: 0.5 }),
  P('• Verificación de middleware role: aplicado correctamente en cada prefijo de ruta', { indent: 0.5 }),
  P('• Técnica: Pruebas de autorización manual + PHPUnit actingAs()', { indent: 0.5 }),
  H3('4.1.4 Pruebas de Regresión'),
  P('Ejecutar la suite PHPUnit completa (composer test) tras cada modificación en TrainingCenterAccess o en el flujo de estados de productos. Verificar que cambios en módulos individuales no afectan otros módulos del mismo training_center.'),
  ...BLANK(1),
  H2('4.2 Ambiente de pruebas'),
  makeTable([
    TR([ TC('Componente', { header: true, width: 30 }), TC('Valor', { header: true, width: 70 }) ]),
    TR([ TC('URL base'), TC('http://localhost (Laragon)') ]),
    TR([ TC('Base de datos'), TC('MySQL — sistema_documental @ 127.0.0.1:3306') ]),
    TR([ TC('PHP'), TC('8.2.28') ]),
    TR([ TC('Laravel'), TC('12.51.0') ]),
    TR([ TC('Livewire'), TC('4.0') ]),
    TR([ TC('Datos de prueba'), TC('php artisan db:seed (DatabaseSeeder — 7 usuarios + 1 Training Center CEFA)') ]),
    TR([ TC('Tests automatizados'), TC('SQLite en memoria (phpunit.xml) — comando: composer test') ]),
    TR([ TC('Contraseña universal'), TC('password (todos los usuarios del seeder)') ]),
  ]),
  ...BLANK(1),
  H2('4.3 Criterios de entrada'),
  P('• Base de datos migrada y sembrada: php artisan migrate --seed', { indent: 0.5 }),
  P('• Servidor Laragon en ejecución (Apache + MySQL)', { indent: 0.5 }),
  P('• Los 7 usuarios de prueba del DatabaseSeeder están disponibles en la BD', { indent: 0.5 }),
  P('• Build de assets ejecutado: npm run build', { indent: 0.5 }),
  ...BLANK(1),
  H2('4.4 Criterios de salida'),
  P('• 100% de casos de prueba de alta y crítica prioridad ejecutados', { indent: 0.5 }),
  P('• 0 defectos críticos abiertos al finalizar el ciclo de pruebas', { indent: 0.5 }),
  P('• Defectos de baja prioridad documentados, analizados y aceptados por el responsable', { indent: 0.5 }),
  P('• Suite PHPUnit ejecutada sin fallos (composer test = verde)', { indent: 0.5 }),
  PB(),
];

// ── Sección 5 — Criterios de aprobación ──────────────────────────────────────
const seccion5 = [
  H1('5. CRITERIOS DE APROBACIÓN'),
  makeTable([
    TR([ TC('Tipo', { header: true, width: 20 }), TC('Criterio', { header: true, width: 55 }), TC('Resultado esperado', { header: true, width: 25 }) ]),
    TR([ TC('Funcional'), TC('Todos los flujos de los 10 módulos ejecutados sin error bloqueante'), TC('APROBADO') ]),
    TR([ TC('Seguridad'), TC('Ningún usuario puede acceder a datos de otro training_center'), TC('APROBADO') ]),
    TR([ TC('Seguridad'), TC('Ningún usuario puede acceder a rutas de otro rol'), TC('APROBADO') ]),
    TR([ TC('Flujo crítico'), TC('El ciclo completo de aprobación de 4 etapas funciona con estados correctos'), TC('APROBADO') ]),
    TR([ TC('Reportes'), TC('Los reportes PDF y Excel se generan sin error y con datos correctos del centro'), TC('APROBADO') ]),
    TR([ TC('Regresión'), TC('La suite PHPUnit (composer test) pasa sin fallos tras el refactoring'), TC('VERDE') ]),
  ]),
  ...BLANK(1),
  H2('5.1 Criterios de suspensión'),
  P('Las pruebas se suspenden si se presenta alguna de las siguientes condiciones:'),
  P('• Defecto crítico que impide la autenticación de cualquier rol del sistema', { indent: 0.5 }),
  P('• Corrupción de datos de training_center_id que afecte el aislamiento multi-tenant', { indent: 0.5 }),
  P('• Fallo en el flujo de aprobación que bloquee completamente a un rol participante', { indent: 0.5 }),
  P('• Ambiente de pruebas no disponible (base de datos, servidor)', { indent: 0.5 }),
  PB(),
];

// ── Sección 6 — Resumen de ejecución ─────────────────────────────────────────
const seccion6 = [
  H1('6. RESUMEN DE EJECUCIÓN'),
  P([R('Nota: ', { bold: true }), R('Esta tabla se diligencia durante la ejecución de las pruebas. Los valores de Ejecutados, Pasaron, Fallaron y Bloqueados se registran al finalizar cada módulo.')], { before: 0, after: 120 }),
  makeTable([
    TR([
      TC('Módulo', { header: true, width: 34 }),
      TC('Total Casos', { header: true, width: 13, align: AlignmentType.CENTER }),
      TC('Ejecutados', { header: true, width: 13, align: AlignmentType.CENTER }),
      TC('Pasaron',    { header: true, width: 13, align: AlignmentType.CENTER }),
      TC('Fallaron',   { header: true, width: 13, align: AlignmentType.CENTER }),
      TC('Bloqueados', { header: true, width: 14, align: AlignmentType.CENTER }),
    ]),
    TR([ TC('MOD-01  Autenticación'), TC('12', { align: AlignmentType.CENTER }), TC('', { align: AlignmentType.CENTER }), TC('', { align: AlignmentType.CENTER }), TC('', { align: AlignmentType.CENTER }), TC('', { align: AlignmentType.CENTER }) ]),
    TR([ TC('MOD-02  Super Administrador'), TC('10', { align: AlignmentType.CENTER }), TC(''), TC(''), TC(''), TC('') ]),
    TR([ TC('MOD-03  Administrador del Sistema'), TC('14', { align: AlignmentType.CENTER }), TC(''), TC(''), TC(''), TC('') ]),
    TR([ TC('MOD-04  Director de Semilleros'), TC('13', { align: AlignmentType.CENTER }), TC(''), TC(''), TC(''), TC('') ]),
    TR([ TC('MOD-05  Líder de Semillero'), TC('11', { align: AlignmentType.CENTER }), TC(''), TC(''), TC(''), TC('') ]),
    TR([ TC('MOD-06  Asesor de Semillero'), TC('15', { align: AlignmentType.CENTER }), TC(''), TC(''), TC(''), TC('') ]),
    TR([ TC('MOD-07  Director de Investigación'), TC('11', { align: AlignmentType.CENTER }), TC(''), TC(''), TC(''), TC('') ]),
    TR([ TC('MOD-08  Investigador Asociado'), TC('10', { align: AlignmentType.CENTER }), TC(''), TC(''), TC(''), TC('') ]),
    TR([ TC('MOD-09  Flujo de Aprobación'), TC('14', { align: AlignmentType.CENTER }), TC(''), TC(''), TC(''), TC('') ]),
    TR([ TC('MOD-10  Multi-tenancy y Seguridad'), TC('7',  { align: AlignmentType.CENTER }), TC(''), TC(''), TC(''), TC('') ]),
    TR([
      TC('TOTAL', { bold: true, fill: TBLHDR }),
      TC('117', { bold: true, align: AlignmentType.CENTER, fill: TBLHDR }),
      TC('', { fill: TBLHDR }),
      TC('', { fill: TBLHDR }),
      TC('', { fill: TBLHDR }),
      TC('', { fill: TBLHDR }),
    ]),
  ]),
  PB(),
];

// ── Sección 7 — Aprobaciones ──────────────────────────────────────────────────
const seccion7 = [
  H1('7. APROBACIONES'),
  P('El presente Plan de Pruebas ha sido elaborado, revisado y aprobado por los responsables indicados a continuación:'),
  ...BLANK(1),
  makeTable([
    TR([ TC('Rol', { header: true, width: 20 }), TC('Nombre', { header: true, width: 35 }), TC('Firma', { header: true, width: 25 }), TC('Fecha', { header: true, width: 20 }) ]),
    TR([ TC('Elaboró'), TC('Juan Esteban Aldana Cortes'), TC('________________'), TC('2026-04-08') ]),
    TR([ TC('Revisó'), TC(''), TC('________________'), TC('') ]),
    TR([ TC('Aprobó'), TC(''), TC('________________'), TC('') ]),
  ]),
  ...BLANK(2),
  P([R('SIGESI — Sistema Documental de Semilleros de Investigación', { italic: true, color: '888888', size: 9 })], { align: AlignmentType.CENTER }),
  P([R('SENA — Centro de Formación Agroindustrial (CEFA) — 2026', { italic: true, color: '888888', size: 9 })], { align: AlignmentType.CENTER }),
];

// ── Footer con numeración ─────────────────────────────────────────────────────
const footer = new Footer({
  children: [
    new Paragraph({
      alignment: AlignmentType.CENTER,
      border: { top: { style: BorderStyle.SINGLE, size: 4, color: 'BDC6D6' } },
      spacing: { before: 60 },
      children: [
        R('SIGESI — Plan de Pruebas v1.0  |  Pág. ', { size: 9, color: '888888' }),
        new TextRun({ children: [PageNumber.CURRENT], font: FONT, size: PT(9), color: '888888' }),
        R(' de ', { size: 9, color: '888888' }),
        new TextRun({ children: [PageNumber.TOTAL_PAGES], font: FONT, size: PT(9), color: '888888' }),
      ],
    }),
  ],
});

// ── Ensamblar documento ───────────────────────────────────────────────────────
const doc = new Document({
  numbering: { config: [] },
  styles: {
    default: {
      document: {
        run: { font: FONT, size: PT(11), color: BLACK },
        paragraph: { spacing: { line: 276 } },
      },
    },
  },
  sections: [
    {
      properties: {
        page: {
          margin: { top: CM(2.54), bottom: CM(2.54), left: CM(2.54), right: CM(2.54) },
        },
      },
      footers: { default: footer },
      children: [
        ...portada,
        ...tablaVersiones,
        ...seccion1,
        ...seccion2,
        ...seccion3,
        ...seccion4,
        ...seccion5,
        ...seccion6,
        ...seccion7,
      ],
    },
  ],
});

// ── Guardar ───────────────────────────────────────────────────────────────────
const outDir  = path.resolve(__dirname, '..', 'docs', 'pruebas');
const outFile = path.join(outDir, 'Plan_Pruebas_SIGESI.docx');
fs.mkdirSync(outDir, { recursive: true });

Packer.toBuffer(doc).then(buf => {
  fs.writeFileSync(outFile, buf);
  const kb = (buf.length / 1024).toFixed(1);
  console.log(`✓ Documento generado: ${outFile} (${kb} KB)`);
}).catch(err => {
  console.error('ERROR:', err.message);
  process.exit(1);
});
