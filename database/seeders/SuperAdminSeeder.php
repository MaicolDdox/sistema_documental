<?php

namespace Database\Seeders;

use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    /**
     * Usuario bootstrap con rol super_administrador (todos los permisos web).
     *
     * Variables opcionales en .env:
     * - SUPER_ADMIN_EMAIL
     * - SUPER_ADMIN_PASSWORD
     * - SUPER_ADMIN_DOCUMENT (solo dígitos, único en users.numero_documento)
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $rolSuper = Role::firstOrCreate(['name' => 'super_administrador', 'guard_name' => 'web']);
        $permisosWeb = Permission::where('guard_name', 'web')->pluck('name')->all();
        if ($permisosWeb !== []) {
            $rolSuper->syncPermissions($permisosWeb);
        }

        $centro = TrainingCenter::query()->orderBy('id')->first();
        if (! $centro) {
            $this->command->warn('SuperAdminSeeder: no hay centros de formación. Ejecuta TrainingCenterSeeder antes.');

            return;
        }

        $email = env('SUPER_ADMIN_EMAIL', 'superadmin@sena.edu.co');
        $password = env('SUPER_ADMIN_PASSWORD', 'Password123!');
        $documento = (int) env('SUPER_ADMIN_DOCUMENT', '900000001');

        $docTaken = User::query()
            ->where('numero_documento', $documento)
            ->where('email', '!=', $email)
            ->exists();

        if ($docTaken) {
            $this->command->error("SuperAdminSeeder: el documento {$documento} ya pertenece a otro usuario. Cambia SUPER_ADMIN_DOCUMENT en .env.");

            return;
        }

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'training_center_id' => $centro->id,
                'tipo_documento' => 'cedula ciudadana',
                'numero_documento' => $documento,
                'password' => Hash::make($password),
                'estado' => 'activo',
            ]
        );

        if (! $user->wasRecentlyCreated) {
            $user->forceFill([
                'numero_documento' => $documento,
                'training_center_id' => $centro->id,
                'tipo_documento' => 'cedula ciudadana',
                'password' => Hash::make($password),
                'estado' => 'activo',
            ])->save();
        }

        $user->syncRoles(['super_administrador']);

        $this->command->info("✅ Super administrador: {$user->email} (documento {$user->numero_documento}) → rol super_administrador");
        $this->command->info('   Contraseña: variable SUPER_ADMIN_PASSWORD en .env, o por defecto Password123!');
    }
}
