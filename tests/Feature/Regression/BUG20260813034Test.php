<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Livewire\Admin\Users\UserEdit;
use App\Models\City;
use App\Models\Department;
use App\Models\Person;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Regresión: BUG-20260813-034
 * UserEdit::update() sobreescribía training_center_id con el centro del
 * admin que edita SIN importar el rol del usuario editado. Un
 * administrador_sistema editando un co_investigador (rol global, sin
 * centro por diseño) terminaba asignándole su propio centro.
 */
class BUG20260813034Test extends TestCase
{
    use RefreshDatabase;

    private function crearCentro(): TrainingCenter
    {
        $depto = Department::firstOrCreate(['nombre' => 'Depto Test BUG-034']);
        $ciudad = City::firstOrCreate(['nombre' => 'Ciudad Test BUG-034', 'department_id' => $depto->id]);

        return TrainingCenter::create([
            'nombre' => 'Centro Test BUG-034', 'codigo' => 'BUG034', 'activo' => true,
            'department_id' => $depto->id, 'city_id' => $ciudad->id,
        ]);
    }

    public function test_editar_co_investigador_no_le_asigna_el_centro_del_admin(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $centro = $this->crearCentro();

        $admin = User::factory()->create(['training_center_id' => $centro->id]);
        $admin->assignRole('administrador_sistema');

        $coInvestigador = User::factory()->create([
            'training_center_id' => null,
            'created_by_user_id' => $admin->id,
        ]);
        $coInvestigador->assignRole('co_investigador');
        Person::create([
            'user_id' => $coInvestigador->id,
            'entity_position_id' => null,
            'linkage_type_id' => null,
            'training_program_id' => null,
            'primer_nombre' => 'Co',
            'primer_apellido' => 'Investigador',
            'genero' => 'prefiero no decirlo',
            'celular' => 0,
            'eps' => '',
            'email_institucional' => 'ci-institucional@test.com',
        ]);

        $this->actingAs($admin);

        Livewire::test(UserEdit::class, ['user' => $coInvestigador])
            ->set('role', 'co_investigador')
            ->set('numero_documento', (string) $coInvestigador->numero_documento)
            ->set('tipo_documento', 'cedula ciudadana')
            ->set('estado', EstadoEnum::Activo->value)
            ->set('primer_nombre', 'Co')
            ->set('primer_apellido', 'Investigador')
            ->call('update')
            ->assertHasNoErrors();

        $this->assertNull($coInvestigador->fresh()->training_center_id);
    }

    public function test_editar_director_semilleros_si_recibe_el_centro_del_admin(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $centro = $this->crearCentro();

        $admin = User::factory()->create(['training_center_id' => $centro->id]);
        $admin->assignRole('administrador_sistema');

        $director = User::factory()->create([
            'training_center_id' => $centro->id,
            'created_by_user_id' => $admin->id,
        ]);
        $director->assignRole('director_semilleros');
        Person::create([
            'user_id' => $director->id,
            'entity_position_id' => null,
            'linkage_type_id' => null,
            'training_program_id' => null,
            'primer_nombre' => 'Dir',
            'primer_apellido' => 'Semilleros',
            'genero' => 'prefiero no decirlo',
            'celular' => 0,
            'eps' => '',
            'email_institucional' => 'dir-institucional@test.com',
        ]);

        $this->actingAs($admin);

        Livewire::test(UserEdit::class, ['user' => $director])
            ->set('role', 'director_semilleros')
            ->set('numero_documento', (string) $director->numero_documento)
            ->set('tipo_documento', 'cedula ciudadana')
            ->set('estado', EstadoEnum::Activo->value)
            ->set('primer_nombre', 'Dir')
            ->set('primer_apellido', 'Semilleros')
            ->call('update')
            ->assertHasNoErrors();

        $this->assertSame($centro->id, $director->fresh()->training_center_id);
    }
}
