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

    /**
     * Reforma GDI/SDI: co_investigador (el rol "global" que motivó este bug,
     * el único que un administrador_sistema podía editar sin que exigiera
     * centro) fue eliminado. Los dos únicos roles que administrador_sistema
     * puede asignar ahora (director_semilleros, director_grupo_investigacion)
     * exigen centro siempre — ya no queda ningún caso de "rol editado por
     * admin que NO deba recibir su centro". Este test se reemplaza por el
     * equivalente con director_grupo_investigacion, confirmando que también
     * hereda el centro del admin (misma invariante que director_semilleros,
     * cubierta en el siguiente test).
     */
    public function test_editar_director_grupo_investigacion_si_recibe_el_centro_del_admin(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $centro = $this->crearCentro();

        $admin = User::factory()->create(['training_center_id' => $centro->id]);
        $admin->assignRole('administrador_sistema');

        $directorGrupo = User::factory()->create([
            'training_center_id' => $centro->id,
            'created_by_user_id' => $admin->id,
        ]);
        $directorGrupo->assignRole('director_grupo_investigacion');
        Person::create([
            'user_id' => $directorGrupo->id,
            'entity_position_id' => null,
            'linkage_type_id' => null,
            'training_program_id' => null,
            'primer_nombre' => 'Director',
            'primer_apellido' => 'Grupo',
            'genero' => 'prefiero no decirlo',
            'celular' => 0,
            'eps' => '',
            'email_institucional' => 'dg-institucional@test.com',
        ]);

        $this->actingAs($admin);

        Livewire::test(UserEdit::class, ['user' => $directorGrupo])
            ->set('role', 'director_grupo_investigacion')
            ->set('numero_documento', (string) $directorGrupo->numero_documento)
            ->set('tipo_documento', 'cedula ciudadana')
            ->set('estado', EstadoEnum::Activo->value)
            ->set('primer_nombre', 'Director')
            ->set('primer_apellido', 'Grupo')
            ->call('update')
            ->assertHasNoErrors();

        $this->assertSame($centro->id, $directorGrupo->fresh()->training_center_id);
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
