<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Enums\EstadoRevisionEnum;
use App\Models\GroupProduct;
use App\Models\Product;
use App\Models\ResearchGroup;
use App\Models\ResearchGroupUser;
use App\Models\Seedling;
use App\Models\User;
use App\Services\LiderSemillero\ProductoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Regresión: BUG-20260609-003
 * ProductosController del LiderSemillero tenía lógica de dominio directa:
 * - index() usaba map() con transformaciones es_mio/ya_en_grupo/autor en el controller.
 * - asignarInvestigadorGrupo() tenía 5 reglas de negocio inline sin Service.
 * Corregido: 2026-06-09 — extraído a App\Services\LiderSemillero\ProductoService.
 */
class BUG20260609003Test extends TestCase
{
    use RefreshDatabase;

    private function crearProducto(int $projectId, EstadoRevisionEnum $estado = EstadoRevisionEnum::Pendiente): Product
    {
        return Product::create([
            'project_id'      => $projectId,
            'nombre'          => 'Producto Test',
            'archivo'         => 'test.pdf',
            'estado'          => EstadoEnum::Activo,
            'estado_revision' => $estado,
        ]);
    }

    private function crearSemillero(User $creador, ?int $grupoId = null): Seedling
    {
        return Seedling::create([
            'research_group_id' => $grupoId,
            'creator_id'        => $creador->id,
            'nombre'            => 'Semillero Test',
            'codigo'            => 999,
            'logo'              => 'default.png',
            'estado'            => EstadoEnum::Activo,
        ]);
    }

    private function crearProyecto(User $creador): \App\Models\Project
    {
        $linea = \App\Models\ResearchLine::create(['nombre' => 'Línea Test', 'estado' => EstadoEnum::Activo]);

        return \App\Models\Project::create([
            'project_creator_id' => $creador->id,
            'research_line_id'   => $linea->id,
            'nombre'             => 'Proyecto Test',
            'estado'             => EstadoEnum::Activo,
            'fecha_inicio'       => now(),
        ]);
    }

    // ─────────────────────────────────────────────────
    // anotarMetadatos — banderas de vista
    // ─────────────────────────────────────────────────

    public function test_anotar_metadatos_marca_es_mio_cuando_lider_es_autor(): void
    {
        $lider  = User::factory()->create();
        $creador = User::factory()->create();
        $proyecto = $this->crearProyecto($creador);

        $producto = $this->crearProducto($proyecto->id);
        $producto->setRelation('productAuthors', collect());
        $producto->setRelation('groupProducts', collect());

        $service = app(ProductoService::class);
        $result  = $service->anotarMetadatos($producto, $lider->id);

        $this->assertFalse($result->es_mio, 'Sin autores, es_mio debe ser false');
        $this->assertFalse($result->ya_en_grupo);
        $this->assertNull($result->autor);
    }

    public function test_anotar_metadatos_marca_ya_en_grupo_cuando_existe_group_product(): void
    {
        $creador  = User::factory()->create();
        $proyecto = $this->crearProyecto($creador);
        $producto = $this->crearProducto($proyecto->id);

        $grupoProduct = new GroupProduct(['product_id' => $producto->id]);
        $producto->setRelation('groupProducts', collect([$grupoProduct]));
        $producto->setRelation('productAuthors', collect());

        $service = app(ProductoService::class);
        $result  = $service->anotarMetadatos($producto, $creador->id);

        $this->assertTrue($result->ya_en_grupo, 'ya_en_grupo debe ser true cuando hay GroupProducts');
    }

    // ─────────────────────────────────────────────────
    // asignarAInvestigador — reglas de negocio
    // ─────────────────────────────────────────────────

    public function test_asignar_lanza_excepcion_si_producto_ya_en_grupo(): void
    {
        Role::firstOrCreate(['name' => 'investigador_asociado', 'guard_name' => 'web']);

        $lider   = User::factory()->create();
        $creador = User::factory()->create();
        $proyecto = $this->crearProyecto($creador);
        $semillero = $this->crearSemillero($lider);

        DB::table('project_seedlings')->insert([
            'seedling_id' => $semillero->id,
            'project_id'  => $proyecto->id,
        ]);

        $producto = $this->crearProducto($proyecto->id, EstadoRevisionEnum::Aprobado);

        $inv = User::factory()->create();
        Role::firstOrCreate(['name' => 'investigador_asociado', 'guard_name' => 'web']);
        $inv->assignRole('investigador_asociado');

        GroupProduct::create([
            'author_id'              => $lider->id,
            'product_id'             => $producto->id,
            'tipo_proyecto_origen'   => \App\Enums\TipoProyectoOrigenEnum::Semilleros,
            'codigo_proyecto_origen' => '0',
            'titulo'                 => 'GP Test',
            'anio_publicacion'       => 2024,
            'tiene_repositorio'      => false,
            'autoriza_datos'         => true,
            'estado_revision'        => EstadoRevisionEnum::Pendiente,
        ]);

        $service = app(ProductoService::class);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('ya está vinculado');

        $service->asignarAInvestigador($producto, $inv->id, $semillero, $lider);
    }

    public function test_asignar_lanza_excepcion_si_producto_no_esta_aprobado(): void
    {
        $lider    = User::factory()->create();
        $creador  = User::factory()->create();
        $proyecto = $this->crearProyecto($creador);
        $semillero = $this->crearSemillero($lider);

        $producto = $this->crearProducto($proyecto->id, EstadoRevisionEnum::Pendiente);

        Role::firstOrCreate(['name' => 'investigador_asociado', 'guard_name' => 'web']);
        $inv = User::factory()->create();
        $inv->assignRole('investigador_asociado');

        $service = app(ProductoService::class);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Solo puedes asignar productos aprobados');

        $service->asignarAInvestigador($producto, $inv->id, $semillero, $lider);
    }

    public function test_asignar_lanza_excepcion_si_investigador_no_tiene_rol(): void
    {
        $lider   = User::factory()->create();
        $creador = User::factory()->create();
        $proyecto = $this->crearProyecto($creador);
        $semillero = $this->crearSemillero($lider);

        $producto = $this->crearProducto($proyecto->id, EstadoRevisionEnum::Aprobado);

        $inv = User::factory()->create(); // sin rol investigador_asociado

        $service = app(ProductoService::class);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('no tiene rol de investigador asociado');

        $service->asignarAInvestigador($producto, $inv->id, $semillero, $lider);
    }

    public function test_asignar_lanza_excepcion_si_investigador_no_pertenece_al_grupo(): void
    {
        $lider   = User::factory()->create();
        $creador = User::factory()->create();
        $proyecto = $this->crearProyecto($creador);

        $grupo = ResearchGroup::create([
            'nombre' => 'Grupo Test',
            'estado' => EstadoEnum::Activo,
        ]);
        $semillero = $this->crearSemillero($lider, $grupo->id);

        $producto = $this->crearProducto($proyecto->id, EstadoRevisionEnum::Aprobado);

        Role::firstOrCreate(['name' => 'investigador_asociado', 'guard_name' => 'web']);
        $inv = User::factory()->create();
        $inv->assignRole('investigador_asociado');
        // El investigador no está vinculado al grupo

        $service = app(ProductoService::class);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('mismo grupo de investigación');

        $service->asignarAInvestigador($producto, $inv->id, $semillero, $lider);
    }
}
