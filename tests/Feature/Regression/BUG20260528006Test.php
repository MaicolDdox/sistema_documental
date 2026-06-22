<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoRevisionEnum;
use App\Enums\TipoProyectoOrigenEnum;
use App\Models\GroupProduct;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresión: BUG-20260528-006
 * El formulario de subida de evidencias no mostraba ningún feedback al usuario.
 * La vista show.blade.php carecía de directivas @error('archivos'), por lo que
 * cualquier error de validación o RuntimeException del servicio quedaba invisible.
 * Corregido: 2026-05-28 — directivas @error y loop de archivos.* añadidos al blade.
 */
class BUG20260528006Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Spatie\Permission\Models\Role::create(['name' => 'investigador_asociado', 'guard_name' => 'web']);
    }

    private function makeAuthor(): User
    {
        $user = User::factory()->create();
        $user->assignRole('investigador_asociado');
        return $user;
    }

    private function makeGroupProduct(User $author): GroupProduct
    {
        $product = Product::create([
            'nombre'          => 'Producto test',
            'estado'          => 'activo',
            'estado_revision' => EstadoRevisionEnum::Pendiente,
        ]);

        return GroupProduct::create([
            'author_id'              => $author->id,
            'product_id'             => $product->id,
            'tipo_proyecto_origen'   => TipoProyectoOrigenEnum::Semilleros,
            'codigo_proyecto_origen' => '0',
            'titulo'                 => 'Producto regresión BUG-006',
            'anio_publicacion'       => 2024,
            'tiene_repositorio'      => false,
            'autoriza_datos'         => true,
            'estado_revision'        => EstadoRevisionEnum::Pendiente,
        ]);
    }

    /**
     * El controlador redirige de vuelta con error en 'archivos' cuando no se envía ningún archivo.
     * Antes del fix, este error era invisible en la vista (no había @error).
     */
    public function test_submitting_no_file_redirects_back_with_archivos_error(): void
    {
        $user    = $this->makeAuthor();
        $product = $this->makeGroupProduct($user);

        $response = $this->actingAs($user)
            ->withoutMiddleware(\App\Http\Middleware\RequireTrainingCenter::class)
            ->post(route('investigador.productos.evidencias.store', $product), []);

        $response->assertRedirect();
        $response->assertSessionHasErrors('archivos');
    }

    /**
     * Un usuario que no es el autor no puede subir evidencias.
     */
    public function test_non_author_cannot_upload_evidence(): void
    {
        $author  = $this->makeAuthor();
        $other   = $this->makeAuthor();
        $product = $this->makeGroupProduct($author);

        $response = $this->actingAs($other)
            ->withoutMiddleware(\App\Http\Middleware\RequireTrainingCenter::class)
            ->post(route('investigador.productos.evidencias.store', $product), []);

        $response->assertStatus(403);
    }
}
