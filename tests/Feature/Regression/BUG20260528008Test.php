<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoRevisionEnum;
use App\Enums\TipoProyectoOrigenEnum;
use App\Http\Middleware\RequireTrainingCenter;
use App\Models\GroupProduct;
use App\Models\Product;
use App\Models\ProductEvidence;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Regresión: BUG-20260528-008
 * El director de investigación obtenía 404/forbidden al intentar ver una evidencia
 * porque la vista usaba asset('storage/...') para archivos en disco local, y no
 * existía ruta de descarga protegida para el rol director_investigacion.
 * Corregido: 2026-05-28 — ruta director.productos.evidencias.download + método downloadEvidencia().
 */
class BUG20260528008Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Spatie\Permission\Models\Role::create(['name' => 'director_investigacion', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::create(['name' => 'investigador_asociado', 'guard_name' => 'web']);
    }

    private function makeDirector(): User
    {
        $user = User::factory()->create();
        $user->assignRole('director_investigacion');
        return $user;
    }

    private function makeProductWithEvidence(): array
    {
        Storage::fake('local');

        $author = User::factory()->create();
        $author->assignRole('investigador_asociado');

        $product = Product::create([
            'nombre'          => 'Producto test BUG-008',
            'estado'          => 'activo',
            'estado_revision' => EstadoRevisionEnum::Pendiente,
        ]);

        $groupProduct = GroupProduct::create([
            'author_id'              => $author->id,
            'product_id'             => $product->id,
            'tipo_proyecto_origen'   => TipoProyectoOrigenEnum::Semilleros,
            'codigo_proyecto_origen' => '0',
            'titulo'                 => 'Producto regresión BUG-008',
            'anio_publicacion'       => 2024,
            'tiene_repositorio'      => false,
            'autoriza_datos'         => true,
            'estado_revision'        => EstadoRevisionEnum::Pendiente,
        ]);

        $fakeFile = 'evidencias/productos/' . $product->id . '/test.pdf';
        Storage::disk('local')->put($fakeFile, 'contenido pdf de prueba');

        $evidence = ProductEvidence::create([
            'product_id'  => $product->id,
            'archivo'     => $fakeFile,
            'nombre'      => 'test.pdf',
            'uploaded_by' => $author->id,
            'descripcion' => null,
        ]);

        return [$groupProduct, $evidence];
    }

    public function test_download_route_exists_for_director(): void
    {
        $this->assertNotNull(
            app('router')->getRoutes()->getByName('director.productos.evidencias.download'),
            'La ruta director.productos.evidencias.download debe existir'
        );
    }

    public function test_director_can_download_product_evidence(): void
    {
        [$groupProduct, $evidence] = $this->makeProductWithEvidence();
        $director = $this->makeDirector();

        $response = $this->actingAs($director)
            ->withoutMiddleware(RequireTrainingCenter::class)
            ->get(route('director.productos.evidencias.download', [$groupProduct, $evidence]));

        $response->assertStatus(200);
    }

    public function test_non_director_cannot_access_download_route(): void
    {
        [$groupProduct, $evidence] = $this->makeProductWithEvidence();
        $other = User::factory()->create();
        $other->assignRole('investigador_asociado');

        $response = $this->actingAs($other)
            ->withoutMiddleware(RequireTrainingCenter::class)
            ->get(route('director.productos.evidencias.download', [$groupProduct, $evidence]));

        $response->assertStatus(403);
    }
}
