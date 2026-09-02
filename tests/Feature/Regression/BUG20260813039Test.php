<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Models\City;
use App\Models\Department;
use App\Models\TrainingCenter;
use App\Models\User;
use App\Support\TrainingCenterAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Regresión: BUG-20260813-039
 * scopeUserQueryForList()/scopeUserQueryForMetrics() tenían 'co_investigador'
 * escrito a mano como "el" rol global. Fix: se deriva en vivo de la tabla
 * roles (cualquier rol fuera de CENTRO_BOUND_ROLE_NAMES y distinto de
 * super_administrador). Este test crea un rol global QUE NO EXISTÍA cuando
 * se escribió el código, para probar que se detecta solo, sin tocar
 * TrainingCenterAccess.
 */
class BUG20260813039Test extends TestCase
{
    use RefreshDatabase;

    private function crearAdminConCentro(): array
    {
        $depto = Department::firstOrCreate(['nombre' => 'Depto Test BUG-039']);
        $ciudad = City::firstOrCreate(['nombre' => 'Ciudad Test BUG-039', 'department_id' => $depto->id]);
        $centro = TrainingCenter::create([
            'nombre' => 'Centro Test BUG-039', 'codigo' => 'BUG039', 'activo' => true,
            'department_id' => $depto->id, 'city_id' => $ciudad->id,
        ]);

        $admin = User::factory()->create(['training_center_id' => $centro->id, 'estado' => EstadoEnum::Activo]);
        $admin->assignRole('administrador_sistema');

        return [$admin, $centro];
    }

    public function test_rol_global_nuevo_no_definido_en_codigo_se_ve_en_cualquier_centro(): void
    {
        Role::firstOrCreate(['name' => 'administrador_sistema', 'guard_name' => 'web']);
        // Rol inventado en este test, fuera de CENTRO_BOUND_ROLE_NAMES y sin
        // ninguna mención en TrainingCenterAccess.php.
        Role::firstOrCreate(['name' => 'auditor_externo', 'guard_name' => 'web']);

        [$admin] = $this->crearAdminConCentro();

        $auditor = User::factory()->create(['training_center_id' => null, 'estado' => EstadoEnum::Activo]);
        $auditor->assignRole('auditor_externo');

        $emails = TrainingCenterAccess::scopeUserQueryForMetrics(User::query(), $admin)->pluck('email');

        $this->assertContains($auditor->email, $emails);
    }

    public function test_rol_centro_bound_sigue_filtrado_por_centro(): void
    {
        Role::firstOrCreate(['name' => 'administrador_sistema', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'director_semilleros', 'guard_name' => 'web']);

        [$admin, $centroPropio] = $this->crearAdminConCentro();
        $centroAjeno = TrainingCenter::create([
            'nombre' => 'Centro Ajeno BUG-039', 'codigo' => 'BUG039-B', 'activo' => true,
            'department_id' => $centroPropio->department_id, 'city_id' => $centroPropio->city_id,
        ]);

        $directorAjeno = User::factory()->create(['training_center_id' => $centroAjeno->id, 'estado' => EstadoEnum::Activo]);
        $directorAjeno->assignRole('director_semilleros');

        $emails = TrainingCenterAccess::scopeUserQueryForMetrics(User::query(), $admin)->pluck('email');

        $this->assertNotContains($directorAjeno->email, $emails);
    }

    public function test_super_administrador_sigue_excluido_aunque_no_este_en_ninguna_lista_explicita(): void
    {
        Role::firstOrCreate(['name' => 'administrador_sistema', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'super_administrador', 'guard_name' => 'web']);

        [$admin, $centro] = $this->crearAdminConCentro();

        $superAdmin = User::factory()->create(['training_center_id' => $centro->id, 'estado' => EstadoEnum::Activo]);
        $superAdmin->assignRole('super_administrador');

        $emails = TrainingCenterAccess::scopeUserQueryForMetrics(User::query(), $admin)->pluck('email');

        $this->assertNotContains($superAdmin->email, $emails);
    }
}
