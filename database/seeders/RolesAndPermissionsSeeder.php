<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Limpiar caché de permisos al inicio
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Lista de permisos agrupados por módulo
        $permissionsByModule = [
            // MÓDULO: USUARIOS — matriz de creación exclusiva (un solo rol crea cada rol)
            'usuarios' => [
                'usuarios.listar',
                'usuarios.editar',
                'usuarios.activar_desactivar',
                'usuarios.asignar_credenciales',
                'usuarios.crear_director_semilleros',
                'usuarios.crear_co_investigador',
                'usuarios.crear_lider_semillero',
                'usuarios.crear_lider_proyecto',
            ],
            // MÓDULO: CATÁLOGOS
            'catalogos' => [
                'catalogos.crear',
                'catalogos.leer',
                'catalogos.editar',
                'catalogos.eliminar',
            ],
            // MÓDULO: SEMILLEROS
            'semilleros' => [
                'semilleros.crear',
                'semilleros.listar',
                'semilleros.ver_detalle',
                'semilleros.editar',
                'semilleros.activar_desactivar',
                'semilleros.reasignar_lider',
                'semilleros.ver_integrantes',
                'semilleros.ver_asesores',
                'semilleros.ver_proyectos',
                'semilleros.ver_productos',
                'semilleros.ver_evidencias',
                'semilleros.gestionar_miembros',
            ],
            // MÓDULO: PROYECTOS
            'proyectos' => [
                'proyectos.crear',
                'proyectos.listar',
                'proyectos.ver_detalle',
                'proyectos.editar',
                'proyectos.activar_desactivar',
                'proyectos.ver_ajeno',
                'proyectos.gestionar_autores',
                'proyectos.listar_semillero',
                'proyectos.crear_semillero',
                'proyectos.vincular_integrantes',
            ],
            // MÓDULO: APRENDICES
            'aprendices' => [
                'aprendices.registrar',
                'aprendices.buscar_por_documento',
                'aprendices.vincular_proyecto',
                'aprendices.desvincular_proyecto',
                'aprendices.listar_autores',
                'aprendices.listar',
                'aprendices.editar',
                'aprendices.ver_detalle',
            ],
            // MÓDULO: PRODUCTOS
            'productos' => [
                'productos.crear',
                'productos.listar',
                'productos.ver_detalle',
                'productos.editar',
                'productos.aprobar',
                'productos.rechazar',
                'productos.aprobar_final',
                'productos.rechazar_final',
                'productos.cambiar_a_en_revision',
                'productos.ver_estado_revision',
                'productos.ver_observaciones',
                'productos.registrar',
            ],
            // MÓDULO: PRODUCTOS MINCIENCIAS (BUG-20260813-029) — personales del
            // co-investigador, aprobados por el administrador_sistema de su centro.
            'minciencias' => [
                'minciencias.listar',
                'minciencias.ver_detalle',
                'minciencias.aprobar',
                'minciencias.rechazar',
            ],
            // MÓDULO: EVIDENCIAS
            'evidencias' => [
                'evidencias.subir_proyecto',
                'evidencias.subir_producto',
                'evidencias.listar',
                'evidencias.eliminar_propia',
                'evidencias.eliminar_cualquiera',
                'evidencias.ver_del_grupo',
                'evidencias.ver_del_semillero',
            ],
            // MÓDULO: DOCUMENTOS INSTITUCIONALES
            'documentos' => [
                'documentos.subir',
                'documentos.listar',
                'documentos.eliminar_propio',
            ],
            // MÓDULO: ARCHIVOS SEMILLERO
            'archivos_semillero' => [
                'archivos_semillero.subir',
                'archivos_semillero.listar',
                'archivos_semillero.eliminar',
            ],
            // MÓDULO: REPORTES
            'reportes' => [
                'reportes.globales_centro',
                'reportes.usuarios_por_rol',
                'reportes.semilleros_con_metricas',
                'reportes.proyectos_por_estado',
                'reportes.productos_por_estado',
                'reportes.exportar_pdf_excel',
                'reportes.productos_por_lider_proyecto',
                'reportes.productos_por_anio',
                'reportes.aprobados_vs_rechazados',
                'reportes.aprendices_por_semillero',
            ],
        ];

        // Crear permisos de forma idempotente
        foreach ($permissionsByModule as $module => $permissions) {
            foreach ($permissions as $permissionName) {
                Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
            }
        }

        // ROL 0: super_administrador — permisos exclusivos de gestión global de la instancia.
        // Acceso: dashboard global, centros de formación (CRUD), vinculación centro↔admin,
        // y creación de CUALQUIER usuario/rol (protegido por middleware de rol, no por permisos Spatie).
        // No gestiona semilleros ni catálogos de centro (eso es de administrador_sistema / director_semilleros).
        $rolSuperAdmin = Role::firstOrCreate(['name' => 'super_administrador', 'guard_name' => 'web']);
        $rolSuperAdmin->syncPermissions([]);

        // Definición de Roles y sus permisos

        // ROL 1: administrador_sistema
        // Gestión del centro de formación. Solo puede crear cuentas de Director de Semilleros
        // y Co-investigador (matriz de creación exclusiva). Ya no crea semilleros directamente
        // (queda exclusivo de director_semilleros) ni gestiona grupos de investigación (el
        // concepto desaparece del sistema).
        $adminSistemaPermissions = [
            'usuarios.listar', 'usuarios.editar', 'usuarios.activar_desactivar', 'usuarios.asignar_credenciales',
            'usuarios.crear_director_semilleros', 'usuarios.crear_co_investigador',
            'catalogos.crear', 'catalogos.leer', 'catalogos.editar', 'catalogos.eliminar',
            'semilleros.listar', 'semilleros.ver_detalle', 'semilleros.ver_integrantes', 'semilleros.ver_asesores',
            'semilleros.ver_proyectos', 'semilleros.ver_productos', 'semilleros.ver_evidencias',
            'proyectos.listar', 'proyectos.ver_detalle', 'proyectos.ver_ajeno',
            'productos.listar', 'productos.ver_detalle', 'productos.ver_estado_revision', 'productos.ver_observaciones',
            'minciencias.listar', 'minciencias.ver_detalle', 'minciencias.aprobar', 'minciencias.rechazar',
            'evidencias.listar', 'evidencias.ver_del_semillero',
            'reportes.globales_centro', 'reportes.usuarios_por_rol', 'reportes.semilleros_con_metricas',
            'reportes.proyectos_por_estado', 'reportes.productos_por_estado', 'reportes.exportar_pdf_excel',
        ];

        $rolAdminSistema = Role::firstOrCreate(['name' => 'administrador_sistema', 'guard_name' => 'web']);
        $rolAdminSistema->syncPermissions($adminSistemaPermissions);

        // ROL 2: director_semilleros
        // "Administrador" operativo de TODOS los semilleros del centro. Único que crea/edita
        // semilleros y crea Líderes de Semillero. Da la aprobación FINAL (segunda etapa) de
        // productos. Ve todo: semilleros, líderes de proyecto, proyectos y su estado.
        $dirSemPermissions = [
            'usuarios.listar', 'usuarios.editar', 'usuarios.activar_desactivar', 'usuarios.asignar_credenciales',
            'usuarios.crear_lider_semillero',
            'semilleros.crear', 'semilleros.listar', 'semilleros.ver_detalle', 'semilleros.editar',
            'semilleros.activar_desactivar', 'semilleros.reasignar_lider', 'semilleros.gestionar_miembros',
            'semilleros.ver_integrantes', 'semilleros.ver_asesores', 'semilleros.ver_proyectos',
            'semilleros.ver_productos', 'semilleros.ver_evidencias',
            'proyectos.listar', 'proyectos.ver_detalle', 'proyectos.editar', 'proyectos.activar_desactivar', 'proyectos.ver_ajeno',
            'productos.listar', 'productos.ver_detalle', 'productos.aprobar_final', 'productos.rechazar_final',
            'productos.ver_estado_revision', 'productos.ver_observaciones',
            'evidencias.listar', 'evidencias.ver_del_semillero',
            'documentos.subir', 'documentos.listar', 'documentos.eliminar_propio',
            'archivos_semillero.subir', 'archivos_semillero.listar', 'archivos_semillero.eliminar',
            'reportes.semilleros_con_metricas', 'reportes.aprendices_por_semillero', 'reportes.proyectos_por_estado',
            'reportes.productos_por_lider_proyecto', 'reportes.aprobados_vs_rechazados', 'reportes.exportar_pdf_excel',
        ];

        $rolDirSem = Role::firstOrCreate(['name' => 'director_semilleros', 'guard_name' => 'web']);
        $rolDirSem->syncPermissions($dirSemPermissions);

        // ROL 3: lider_semillero
        // A cargo de TODOS los proyectos de su semillero. Crea proyectos y crea Líderes de
        // Proyecto (matriz de creación exclusiva). Da la primera aprobación de productos.
        $liderSemPermissions = [
            'usuarios.crear_lider_proyecto',
            'proyectos.crear', 'proyectos.listar', 'proyectos.ver_detalle', 'proyectos.editar',
            'proyectos.activar_desactivar', 'proyectos.crear_semillero', 'proyectos.listar_semillero',
            'productos.listar', 'productos.ver_detalle', 'productos.aprobar', 'productos.rechazar',
            'productos.ver_estado_revision', 'productos.ver_observaciones',
            'evidencias.listar', 'evidencias.ver_del_semillero',
            'reportes.semilleros_con_metricas', 'reportes.aprendices_por_semillero', 'reportes.exportar_pdf_excel',
        ];

        $rolLiderSem = Role::firstOrCreate(['name' => 'lider_semillero', 'guard_name' => 'web']);
        $rolLiderSem->syncPermissions($liderSemPermissions);

        // ROL 4: lider_proyecto
        // Rol nuevo del rediseño. Sus permisos funcionales (subir evidencias, registrar
        // aprendices como datos, vincular co-investigadores) se definen en la fase del
        // rediseño que construye el modelo de datos de proyectos — por ahora solo existe
        // como rol asignable, sin permisos propios.
        $rolLiderProyecto = Role::firstOrCreate(['name' => 'lider_proyecto', 'guard_name' => 'web']);
        $rolLiderProyecto->syncPermissions([]);

        // ROL 5: co_investigador
        // Rol nuevo del rediseño, fuera de la jerarquía de semilleros. Sin training_center_id
        // (ver TrainingCenterAccess). Sus permisos funcionales se definen junto con
        // lider_proyecto en una fase posterior — por ahora solo existe como rol asignable.
        $rolCoInvestigador = Role::firstOrCreate(['name' => 'co_investigador', 'guard_name' => 'web']);
        $rolCoInvestigador->syncPermissions([]);

        // ─── Totales globales ───────────────────────────────────────
        $totalPermisos = \Spatie\Permission\Models\Permission::count();
        $totalRoles = \Spatie\Permission\Models\Role::count();

        $this->command->newLine();
        $this->command->info('╔══════════════════════════════════════════╗');
        $this->command->info('║   GIDESTH — Roles y Permisos cargados    ║');
        $this->command->info('╚══════════════════════════════════════════╝');
        $this->command->newLine();

        $this->command->info("✅ Total permisos creados : {$totalPermisos}");
        $this->command->info("✅ Total roles creados    : {$totalRoles}");
        $this->command->newLine();

        // ─── Tabla: permisos por módulo ─────────────────────────────
        $this->command->info('📋 Permisos por módulo:');
        $modulos = array_keys($permissionsByModule);

        $tablaModulos = [];
        foreach ($modulos as $modulo) {
            $count = \Spatie\Permission\Models\Permission::where('name', 'like', $modulo.'.%')->count();
            $tablaModulos[] = [$modulo, $count];
        }
        $this->command->table(['Módulo', 'Permisos'], $tablaModulos);

        // ─── Tabla: permisos por rol ────────────────────────────────
        $this->command->newLine();
        $this->command->info('👥 Permisos asignados por rol:');
        $roles = \Spatie\Permission\Models\Role::with('permissions')->get();
        $tablaRoles = $roles->map(fn ($r) => [
            $r->name,
            $r->permissions->count(),
        ])->toArray();
        $this->command->table(['Rol', 'Permisos asignados'], $tablaRoles);

        // ─── Verificación de integridad ─────────────────────────────
        $this->command->newLine();
        $this->command->info('🔍 Verificación de integridad:');

        $rolesEsperados = [
            'super_administrador', 'administrador_sistema', 'director_semilleros',
            'lider_semillero', 'lider_proyecto', 'co_investigador',
        ];

        foreach ($rolesEsperados as $nombreRol) {
            $rol = \Spatie\Permission\Models\Role::where('name', $nombreRol)->first();
            if ($rol) {
                $this->command->line(
                    "  ✅ {$nombreRol} — {$rol->permissions->count()} permisos"
                );
            } else {
                $this->command->error("  ❌ ROL FALTANTE: {$nombreRol}");
            }
        }

        $this->command->newLine();
        $this->command->info('🚀 Seeder completado exitosamente.');
        $this->command->newLine();
    }
}
