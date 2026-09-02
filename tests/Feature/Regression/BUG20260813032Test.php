<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Models\City;
use App\Models\Department;
use App\Models\Seedling;
use App\Models\SeedlingFile;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Regresión: BUG-20260813-032
 * DocumentoSemilleroController::store() validaba que semillero_id
 * existiera en la BD, pero no que perteneciera al centro de formación del
 * director que sube el documento — un director del Centro A podía subir un
 * documento a un semillero del Centro B con solo conocer su id (fuga de
 * datos entre centros).
 */
class BUG20260813032Test extends TestCase
{
    use RefreshDatabase;

    private function crearCentro(string $nombre): TrainingCenter
    {
        $depto = Department::firstOrCreate(['nombre' => 'Depto Test BUG-032']);
        $ciudad = City::firstOrCreate(['nombre' => 'Ciudad Test BUG-032', 'department_id' => $depto->id]);

        return TrainingCenter::create([
            'nombre' => $nombre, 'codigo' => 'BUG032-'.$nombre, 'activo' => true,
            'department_id' => $depto->id, 'city_id' => $ciudad->id,
        ]);
    }

    private function crearDirector(TrainingCenter $centro): User
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $director = User::factory()->create(['training_center_id' => $centro->id]);
        $director->assignRole('director_semilleros');

        return $director;
    }

    private function crearSemillero(TrainingCenter $centro, User $creador): Seedling
    {
        return Seedling::create([
            'creator_id' => $creador->id,
            'training_center_id' => $centro->id,
            'nombre' => 'Semillero de '.$centro->nombre,
            'codigo' => random_int(1000, 999999),
            'logo' => '',
            'estado' => EstadoEnum::Activo,
        ]);
    }

    public function test_director_no_puede_subir_documento_a_semillero_de_otro_centro(): void
    {
        Storage::fake('public');

        $centroA = $this->crearCentro('Centro A');
        $centroB = $this->crearCentro('Centro B');
        $directorA = $this->crearDirector($centroA);
        $semilleroB = $this->crearSemillero($centroB, $directorA);

        $response = $this->actingAs($directorA)->post(route('dir-sem.documentos.store'), [
            'nombre' => 'Documento intruso',
            'semillero_id' => $semilleroB->id,
            'archivo' => UploadedFile::fake()->create('doc.pdf', 100),
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('seedling_files', [
            'seedling_id' => $semilleroB->id,
            'archivo' => 'Documento intruso',
        ]);
    }

    public function test_director_si_puede_subir_documento_a_su_propio_semillero(): void
    {
        Storage::fake('public');

        $centro = $this->crearCentro('Centro C');
        $director = $this->crearDirector($centro);
        $semillero = $this->crearSemillero($centro, $director);

        $response = $this->actingAs($director)->post(route('dir-sem.documentos.store'), [
            'nombre' => 'Documento propio',
            'semillero_id' => $semillero->id,
            'archivo' => UploadedFile::fake()->create('doc.pdf', 100),
        ]);

        $response->assertRedirect(route('dir-sem.semilleros.show', $semillero->id));
        $this->assertDatabaseHas('seedling_files', [
            'seedling_id' => $semillero->id,
            'archivo' => 'Documento propio',
        ]);
    }
}
