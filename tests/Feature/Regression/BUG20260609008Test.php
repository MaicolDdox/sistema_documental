<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Models\City;
use App\Models\Department;
use App\Models\ResearchGroup;
use App\Models\Seedling;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Regresión: BUG-20260609-008
 * SemilleroController::index() no aplicaba ningún scope cuando el director
 * tenía training_center_id = null, exponiendo todos los semilleros del sistema.
 * El scope primario dependía de whereHas('leader') lo cual fallaba si
 * el semillero no tenía líder asignado.
 * Corregido: 2026-06-09 — scope via researchGroup.training_center_id como primario,
 * fallback a creator.training_center_id para semilleros sin grupo.
 */
class BUG20260609008Test extends TestCase
{
    use RefreshDatabase;

    private ?Department $dpto    = null;
    private ?City       $ciudad  = null;

    private function crearCentro(string $nombre, int $codigo): TrainingCenter
    {
        $this->dpto   ??= Department::firstOrCreate(['nombre' => 'Depto Test']);
        $this->ciudad ??= City::firstOrCreate(['nombre' => 'Ciudad Test', 'department_id' => $this->dpto->id]);

        return TrainingCenter::create([
            'department_id' => $this->dpto->id,
            'city_id'       => $this->ciudad->id,
            'nombre'        => $nombre,
            'codigo'        => $codigo,
        ]);
    }

    private function crearDirectorConCentro(int $centroId): User
    {
        Permission::firstOrCreate(['name' => 'semilleros.listar', 'guard_name' => 'web']);
        $rol = Role::firstOrCreate(['name' => 'director_semilleros', 'guard_name' => 'web']);
        $rol->givePermissionTo('semilleros.listar');

        $director = User::factory()->create([
            'training_center_id' => $centroId,
            'estado'             => EstadoEnum::Activo,
        ]);
        $director->assignRole('director_semilleros');

        return $director;
    }

    private function crearSemillero(int $grupoId, int $creadorId, string $nombre, int $codigo): Seedling
    {
        return Seedling::create([
            'research_group_id' => $grupoId,
            'creator_id'        => $creadorId,
            'nombre'            => $nombre,
            'codigo'            => $codigo,
            'logo'              => 'default.png',
            'estado'            => EstadoEnum::Activo,
        ]);
    }

    public function test_director_solo_ve_semilleros_de_su_centro(): void
    {
        $centro1 = $this->crearCentro('Centro A', 101);
        $centro2 = $this->crearCentro('Centro B', 102);

        $grupo1 = ResearchGroup::create([
            'training_center_id' => $centro1->id,
            'nombre'             => 'Grupo Centro A',
            'estado'             => EstadoEnum::Activo,
        ]);
        $grupo2 = ResearchGroup::create([
            'training_center_id' => $centro2->id,
            'nombre'             => 'Grupo Centro B',
            'estado'             => EstadoEnum::Activo,
        ]);

        $creador = User::factory()->create();

        $this->crearSemillero($grupo1->id, $creador->id, 'Semillero A1', 201);
        $this->crearSemillero($grupo2->id, $creador->id, 'Semillero B1', 202);

        $director = $this->crearDirectorConCentro($centro1->id);

        $resultado = Seedling::when(
            $director->training_center_id,
            fn ($q) => $q->where(function ($inner) use ($director) {
                $inner->whereHas('researchGroup', fn ($g) =>
                    $g->where('training_center_id', $director->training_center_id)
                )->orWhere(function ($noGroup) use ($director) {
                    $noGroup->whereNull('research_group_id')
                        ->whereHas('creator', fn ($c) =>
                            $c->where('training_center_id', $director->training_center_id)
                        );
                });
            })
        )->pluck('nombre');

        $this->assertContains('Semillero A1', $resultado);
        $this->assertNotContains('Semillero B1', $resultado);
    }

    public function test_scope_via_creator_cuando_semillero_no_tiene_grupo(): void
    {
        $centro = $this->crearCentro('Centro X', 501);

        $creadorDelCentro = User::factory()->create(['training_center_id' => $centro->id]);
        $creadorOtro      = User::factory()->create(['training_center_id' => null]);

        Seedling::create([
            'research_group_id' => null,
            'creator_id'        => $creadorDelCentro->id,
            'nombre'            => 'Sin Grupo Centro X',
            'codigo'            => 601,
            'logo'              => 'default.png',
            'estado'            => EstadoEnum::Activo,
        ]);
        Seedling::create([
            'research_group_id' => null,
            'creator_id'        => $creadorOtro->id,
            'nombre'            => 'Sin Grupo Otro',
            'codigo'            => 602,
            'logo'              => 'default.png',
            'estado'            => EstadoEnum::Activo,
        ]);

        $director = $this->crearDirectorConCentro($centro->id);

        $resultado = Seedling::when(
            $director->training_center_id,
            fn ($q) => $q->where(function ($inner) use ($director) {
                $inner->whereHas('researchGroup', fn ($g) =>
                    $g->where('training_center_id', $director->training_center_id)
                )->orWhere(function ($noGroup) use ($director) {
                    $noGroup->whereNull('research_group_id')
                        ->whereHas('creator', fn ($c) =>
                            $c->where('training_center_id', $director->training_center_id)
                        );
                });
            })
        )->pluck('nombre');

        $this->assertContains('Sin Grupo Centro X', $resultado, 'Semillero sin grupo creado por usuario del centro debe aparecer');
        $this->assertNotContains('Sin Grupo Otro', $resultado, 'Semillero sin grupo de otro creador NO debe aparecer');
    }
}
