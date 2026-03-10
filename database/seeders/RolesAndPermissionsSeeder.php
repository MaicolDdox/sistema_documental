<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

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
            // MÓDULO: USUARIOS
            'usuarios' => [
                'usuarios.listar',
                'usuarios.crear',
                'usuarios.editar',
                'usuarios.activar_desactivar',
                'usuarios.asignar_rol',
                'usuarios.revocar_rol',
                'usuarios.crear_investigador_asociado',
                'usuarios.crear_lider_semillero',
                'usuarios.asignar_credenciales',
            ],
            // MÓDULO: CATÁLOGOS
            'catalogos' => [
                'catalogos.crear',
                'catalogos.leer',
                'catalogos.editar',
                'catalogos.eliminar',
            ],
            // MÓDULO: GRUPOS DE INVESTIGACIÓN
            'grupos' => [
                'grupos.crear',
                'grupos.leer',
                'grupos.editar',
                'grupos.activar_desactivar',
                'grupos.gestionar_miembros',
                'grupos.vincular_investigador',
                'grupos.desvincular_investigador',
                'grupos.cambiar_rol_interno',
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
                // Módulo asesor_semillero
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
                // Módulo asesor_semillero
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
                'productos.cambiar_a_en_revision',
                'productos.ver_estado_revision',
                'productos.ver_observaciones',
                // Módulo asesor_semillero
                'productos.registrar',
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
            // MÓDULO: ASESORES EXTERNOS
            'asesores_externos' => [
                'asesores_externos.registrar',
                'asesores_externos.vincular_semillero',
                'asesores_externos.desvincular_semillero',
                'asesores_externos.listar',
            ],
            // MÓDULO: REPORTES
            'reportes' => [
                'reportes.globales_centro',
                'reportes.usuarios_por_rol',
                'reportes.grupos_con_metricas',
                'reportes.semilleros_con_metricas',
                'reportes.proyectos_por_estado',
                'reportes.productos_por_estado',
                'reportes.exportar_pdf_excel',
                'reportes.productos_por_investigador',
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

        // Definición de Roles y sus permisos
        
        // ROL 1: administrador_sistema
        // Gestión total del centro de formación. Opera sobre todos los módulos dentro de su centro_formacion_id.
        $adminSistemaPermissions = [
            'usuarios.listar', 'usuarios.crear', 'usuarios.editar', 'usuarios.activar_desactivar', 'usuarios.asignar_rol', 'usuarios.revocar_rol', 'usuarios.asignar_credenciales',
            'catalogos.crear', 'catalogos.leer', 'catalogos.editar', 'catalogos.eliminar',
            'grupos.crear', 'grupos.leer', 'grupos.editar', 'grupos.activar_desactivar', 'grupos.gestionar_miembros',
            'semilleros.crear', 'semilleros.listar', 'semilleros.ver_detalle', 'semilleros.editar', 'semilleros.activar_desactivar', 'semilleros.reasignar_lider', 'semilleros.gestionar_miembros', 'semilleros.ver_integrantes', 'semilleros.ver_asesores', 'semilleros.ver_proyectos', 'semilleros.ver_productos', 'semilleros.ver_evidencias',
            'proyectos.listar', 'proyectos.ver_detalle', 'proyectos.activar_desactivar', 'proyectos.ver_ajeno',
            'productos.listar', 'productos.ver_detalle', 'productos.aprobar', 'productos.rechazar', 'productos.cambiar_a_en_revision', 'productos.ver_estado_revision', 'productos.ver_observaciones',
            'evidencias.subir_proyecto', 'evidencias.subir_producto', 'evidencias.listar', 'evidencias.eliminar_cualquiera', 'evidencias.ver_del_grupo', 'evidencias.ver_del_semillero',
            'reportes.globales_centro', 'reportes.usuarios_por_rol', 'reportes.grupos_con_metricas', 'reportes.semilleros_con_metricas', 'reportes.proyectos_por_estado', 'reportes.productos_por_estado', 'reportes.exportar_pdf_excel',
        ];

        $rolAdminSistema = Role::firstOrCreate(['name' => 'administrador_sistema', 'guard_name' => 'web']);
        $rolAdminSistema->syncPermissions($adminSistemaPermissions);

        // ROL 2: investigador_asociado
        // Registra proyectos y productos del grupo. Solo opera sobre sus propios proyectos y productos.
        $invAsocPermissions = [
            'grupos.leer',
            'proyectos.crear', 'proyectos.listar', 'proyectos.ver_detalle', 'proyectos.editar', 'proyectos.activar_desactivar', 'proyectos.gestionar_autores',
            'productos.crear', 'productos.listar', 'productos.ver_detalle', 'productos.editar', 'productos.ver_estado_revision', 'productos.ver_observaciones',
            'evidencias.subir_proyecto', 'evidencias.subir_producto', 'evidencias.listar', 'evidencias.eliminar_propia',
            'catalogos.leer',
        ];

        $rolInvAsoc = Role::firstOrCreate(['name' => 'investigador_asociado', 'guard_name' => 'web']);
        $rolInvAsoc->syncPermissions($invAsocPermissions);

        // ROL 3: director_investigacion
        // Gestiona el grupo, aprueba/rechaza productos.
        // INCLUYE todos los permisos de investigador_asociado más los propios.
        $directorInvExclusives = [
            'grupos.gestionar_miembros', 'grupos.vincular_investigador', 'grupos.desvincular_investigador', 'grupos.cambiar_rol_interno',
            'usuarios.crear_investigador_asociado', 'usuarios.asignar_credenciales', 'usuarios.listar', 'usuarios.editar', 'usuarios.activar_desactivar',
            'proyectos.ver_ajeno',
            'productos.aprobar', 'productos.rechazar', 'productos.cambiar_a_en_revision',
            'evidencias.ver_del_grupo',
            'documentos.subir', 'documentos.listar', 'documentos.eliminar_propio',
            'reportes.productos_por_investigador', 'reportes.productos_por_anio', 'reportes.aprobados_vs_rechazados', 'reportes.exportar_pdf_excel',
        ];
        $dirInvPermissions = array_merge($invAsocPermissions, $directorInvExclusives);

        $rolDirInv = Role::firstOrCreate(['name' => 'director_investigacion', 'guard_name' => 'web']);
        $rolDirInv->syncPermissions($dirInvPermissions);

        // ROL 4: asesor_semillero
        // Actor operativo del semillero. Gestiona proyectos propios, registra aprendices y productos.
        $asesorSemPermissions = [
            'proyectos.crear', 'proyectos.listar', 'proyectos.ver_detalle', 'proyectos.editar', 'proyectos.activar_desactivar', 'proyectos.gestionar_autores',
            'proyectos.listar_semillero', 'proyectos.crear_semillero', 'proyectos.vincular_integrantes',
            'aprendices.registrar', 'aprendices.buscar_por_documento', 'aprendices.vincular_proyecto', 'aprendices.desvincular_proyecto', 'aprendices.listar_autores',
            'aprendices.listar', 'aprendices.editar', 'aprendices.ver_detalle',
            'productos.crear', 'productos.listar', 'productos.ver_detalle', 'productos.editar', 'productos.ver_estado_revision', 'productos.ver_observaciones',
            'productos.registrar',
            'evidencias.subir_proyecto', 'evidencias.subir_producto', 'evidencias.listar', 'evidencias.eliminar_propia',
            'asesores_externos.registrar', 'asesores_externos.vincular_semillero', 'asesores_externos.desvincular_semillero', 'asesores_externos.listar',
            'catalogos.leer',
        ];

        $rolAsesorSem = Role::firstOrCreate(['name' => 'asesor_semillero', 'guard_name' => 'web']);
        $rolAsesorSem->syncPermissions($asesorSemPermissions);

        // ROL 5: lider_semillero
        // Coordina su semillero específico.
        // INCLUYE todos los permisos de asesor_semillero más los propios.
        $liderSemExclusives = [
            'semilleros.ver_detalle', 'semilleros.editar', 'semilleros.ver_integrantes', 'semilleros.ver_asesores', 'semilleros.ver_proyectos', 'semilleros.ver_productos',
            'productos.aprobar', 'productos.rechazar', 'productos.cambiar_a_en_revision',
            'archivos_semillero.subir', 'archivos_semillero.listar', 'archivos_semillero.eliminar',
            'evidencias.ver_del_semillero',
        ];
        $liderSemPermissions = array_merge($asesorSemPermissions, $liderSemExclusives);

        $rolLiderSem = Role::firstOrCreate(['name' => 'lider_semillero', 'guard_name' => 'web']);
        $rolLiderSem->syncPermissions($liderSemPermissions);

        // ROL 6: director_semilleros
        // Coordina todos los semilleros del centro.
        // INCLUYE todos los permisos de asesor_semillero más los propios.
        $dirSemExclusives = [
            'semilleros.crear', 'semilleros.listar', 'semilleros.ver_detalle', 'semilleros.editar', 'semilleros.activar_desactivar', 'semilleros.reasignar_lider', 'semilleros.gestionar_miembros', 'semilleros.ver_integrantes', 'semilleros.ver_asesores', 'semilleros.ver_proyectos', 'semilleros.ver_productos', 'semilleros.ver_evidencias',
            'usuarios.crear_lider_semillero', 'usuarios.asignar_credenciales', 'usuarios.listar', 'usuarios.editar', 'usuarios.activar_desactivar',
            'documentos.subir', 'documentos.listar', 'documentos.eliminar_propio',
            'reportes.semilleros_con_metricas', 'reportes.aprendices_por_semillero', 'reportes.proyectos_por_estado', 'reportes.exportar_pdf_excel',
            'evidencias.ver_del_semillero',
        ];
        $dirSemPermissions = array_merge($asesorSemPermissions, $dirSemExclusives);

        $rolDirSem = Role::firstOrCreate(['name' => 'director_semilleros', 'guard_name' => 'web']);
        $rolDirSem->syncPermissions($dirSemPermissions);

        // Asignar roles a los usuarios de prueba
        // Usuario ydmoreno@sena.edu.co -> administrador_sistema
        $user1 = \App\Models\User::where('numero_documento', 34327134)->first();
        if ($user1 && !$user1->hasRole('administrador_sistema')) {
            $user1->assignRole('administrador_sistema');
            $this->command->info(
                "👤 Rol administrador_sistema asignado a: {$user1->email}"
            );
        }

        // Usuario jovalenciap@sena.edu.co -> administrador_sistema
        $user2 = \App\Models\User::where('numero_documento', 10304952)->first();
        if ($user2 && !$user2->hasRole('administrador_sistema')) {
            $user2->assignRole('administrador_sistema');
            $this->command->info(
                "👤 Rol administrador_sistema asignado a: {$user2->email}"
            );
        }

        // Usuarios con rol director_semilleros
        foreach (['directorsem@sena.edu.co', 'dirsemillero@sena.edu.co'] as $email) {
            $userDirSem = \App\Models\User::where('email', $email)->first();
            if ($userDirSem && !$userDirSem->hasRole('director_semilleros')) {
                $userDirSem->assignRole('director_semilleros');
                $this->command->info(
                    "👤 Rol director_semilleros asignado a: {$userDirSem->email}"
                );
            }
        }

        // Usuario con rol lider_semillero
        $userLider = \App\Models\User::where('email', 'lidersem@sena.edu.co')->first();
        if ($userLider && !$userLider->hasRole('lider_semillero')) {
            $userLider->assignRole('lider_semillero');
            $this->command->info(
                "👤 Rol lider_semillero asignado a: {$userLider->email}"
            );
        }

        // ─── Totales globales ───────────────────────────────────────
        $totalPermisos = \Spatie\Permission\Models\Permission::count();
        $totalRoles    = \Spatie\Permission\Models\Role::count();

        $this->command->newLine();
        $this->command->info('╔══════════════════════════════════════════╗');
        $this->command->info('║   GIDESTH — Roles y Permisos cargados   ║');
        $this->command->info('╚══════════════════════════════════════════╝');
        $this->command->newLine();

        $this->command->info("✅ Total permisos creados : {$totalPermisos}");
        $this->command->info("✅ Total roles creados    : {$totalRoles}");
        $this->command->newLine();

        // ─── Tabla: permisos por módulo ─────────────────────────────
        $this->command->info('📋 Permisos por módulo:');
        $modulos = [
            'usuarios', 'catalogos', 'grupos', 'semilleros', 'proyectos',
            'aprendices', 'productos', 'evidencias', 'documentos',
            'archivos_semillero', 'asesores_externos', 'reportes',
        ];

        $tablaModulos = [];
        foreach ($modulos as $modulo) {
            $count = \Spatie\Permission\Models\Permission::where('name', 'like', $modulo . '.%')->count();
            $tablaModulos[] = [$modulo, $count];
        }
        $this->command->table(['Módulo', 'Permisos'], $tablaModulos);

        // ─── Tabla: permisos por rol ────────────────────────────────
        $this->command->newLine();
        $this->command->info('👥 Permisos asignados por rol:');
        $roles = \Spatie\Permission\Models\Role::with('permissions')->get();
        $tablaRoles = $roles->map(fn($r) => [
            $r->name,
            $r->permissions->count(),
        ])->toArray();
        $this->command->table(['Rol', 'Permisos asignados'], $tablaRoles);

        // ─── Verificación de integridad ─────────────────────────────
        $this->command->newLine();
        $this->command->info('🔍 Verificación de integridad:');

        $rolesEsperados = [
            'administrador_sistema', 'director_investigacion',
            'investigador_asociado', 'director_semilleros',
            'lider_semillero', 'asesor_semillero',
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

        // ─── Verificar roles compuestos ─────────────────────────────
        $this->command->newLine();
        $this->command->info('🔗 Verificación de roles compuestos:');

        $dirInv   = Role::findByName('director_investigacion');
        $invAsoc  = Role::findByName('investigador_asociado');
        $permInvAsoc = $invAsoc->permissions->pluck('name');
        $faltantes = $permInvAsoc->diff($dirInv->permissions->pluck('name'));
        if ($faltantes->isEmpty()) {
            $this->command->line(
                '  ✅ director_investigacion incluye todos los permisos de investigador_asociado'
            );
        } else {
            $this->command->warn(
                '  ⚠️  Faltan permisos de investigador_asociado en director_investigacion: '
                . $faltantes->implode(', ')
            );
        }

        $dirSem    = Role::findByName('director_semilleros');
        $asesorSem = Role::findByName('asesor_semillero');
        $liderSem  = Role::findByName('lider_semillero');
        $permAsesor = $asesorSem->permissions->pluck('name');

        $faltDir = $permAsesor->diff($dirSem->permissions->pluck('name'));
        if ($faltDir->isEmpty()) {
            $this->command->line(
                '  ✅ director_semilleros incluye todos los permisos de asesor_semillero'
            );
        } else {
            $this->command->warn('  ⚠️  Faltan en director_semilleros: '
                . $faltDir->implode(', '));
        }

        $faltLid = $permAsesor->diff($liderSem->permissions->pluck('name'));
        if ($faltLid->isEmpty()) {
            $this->command->line(
                '  ✅ lider_semillero incluye todos los permisos de asesor_semillero'
            );
        } else {
            $this->command->warn('  ⚠️  Faltan en lider_semillero: '
                . $faltLid->implode(', '));
        }

        $this->command->newLine();
        $this->command->info('🚀 Seeder completado exitosamente.');
        $this->command->newLine();
    }
}
