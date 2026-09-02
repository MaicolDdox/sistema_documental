<?php

namespace Tests\Feature\Regression;

use App\Enums\NivelFormacionEnum;
use App\Models\EntityPosition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresión: BUG-20260813-044
 * Formulario de perfil: "Nivel de Formación" y "Fecha de Vinculación" solo
 * para co_investigador; "Cargo / Posición" en todos los roles, ahora
 * gestionable desde Catálogos Simples con la lista institucional del SENA.
 */
class BUG20260813044Test extends TestCase
{
    use RefreshDatabase;

    public function test_co_investigador_ve_nivel_de_formacion_y_fecha_de_vinculacion(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('co_investigador');

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response->assertOk();
        $response->assertSee('Nivel de Formación');
        $response->assertSee('Fecha de Vinculación');
    }

    public function test_otro_rol_no_ve_nivel_de_formacion_ni_fecha_de_vinculacion(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('lider_proyecto');

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response->assertOk();
        $response->assertDontSee('Nivel de Formación');
        $response->assertDontSee('Fecha de Vinculación');
    }

    public function test_co_investigador_puede_guardar_nivel_de_formacion_y_fecha(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('co_investigador');

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'primer_nombre' => 'Ana', 'segundo_nombre' => '',
            'primer_apellido' => 'Torres', 'segundo_apellido' => '',
            'email' => $user->email, 'telefono' => '', 'celular' => '',
            'genero' => '', 'eps' => '', 'email_institucional' => '',
            'entity_position_id' => '', 'linkage_type_id' => '', 'cvlac_link' => '',
            'nivel_formacion' => NivelFormacionEnum::Tecnologo->value,
            'fecha_vinculacion' => '2026-01-15',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $this->assertDatabaseHas('people', [
            'user_id' => $user->id,
            'nivel_formacion' => 'tecnologo',
            'fecha_vinculacion' => '2026-01-15 00:00:00',
        ]);
    }

    public function test_otro_rol_puede_actualizar_perfil_sin_enviar_nivel_ni_fecha(): void
    {
        // El campo ni siquiera está en el DOM para otros roles — el
        // controlador no debe romper si esas claves no llegan en el request.
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('lider_proyecto');

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'primer_nombre' => 'Juan', 'segundo_nombre' => '',
            'primer_apellido' => 'Perez', 'segundo_apellido' => '',
            'email' => $user->email, 'telefono' => '', 'celular' => '',
            'genero' => '', 'eps' => '', 'email_institucional' => '',
            'entity_position_id' => '', 'linkage_type_id' => '', 'cvlac_link' => '',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionDoesntHaveErrors();
    }

    public function test_catalogo_cargo_posicion_tiene_la_lista_institucional(): void
    {
        (new \Database\Seeders\CargosEntidadesSeeder)->run();

        $this->assertSame(13, EntityPosition::count());
        $this->assertDatabaseHas('entity_positions', ['nombre' => 'Investigador(a) SENNOVA']);
        $this->assertDatabaseHas('entity_positions', ['nombre' => 'Otro:']);
    }

    public function test_admin_puede_crear_un_cargo_nuevo_desde_catalogos_simples(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('administrador_sistema');
        $admin->givePermissionTo(['catalogos.leer', 'catalogos.crear']);

        $response = $this->actingAs($admin)->post(route('admin.entity-positions.store'), [
            'nombre' => 'Cargo de prueba',
            'descripcion' => 'Creado desde el test',
            '_from_simples' => '1',
        ]);

        $response->assertRedirect(route('admin.catalogos.simples'));
        $this->assertDatabaseHas('entity_positions', ['nombre' => 'Cargo de prueba']);
    }

    public function test_pagina_catalogos_simples_muestra_el_cargo_en_el_perfil(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        (new \Database\Seeders\CargosEntidadesSeeder)->run();

        $admin = User::factory()->create();
        $admin->assignRole('administrador_sistema');
        $admin->givePermissionTo('catalogos.leer');

        $response = $this->actingAs($admin)->get(route('admin.catalogos.simples'));

        $response->assertOk();
        $response->assertSee('Investigador(a) SENNOVA');
    }

    public function test_catalogo_se_llama_cargo_posicion_en_catalogos_simples_y_no_cargos_en_entidad(): void
    {
        // Corrección dentro de BUG-20260813-044: mismo nombre en todo el
        // sistema — "Cargo / Posición", no "Cargos en Entidad".
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('administrador_sistema');
        $admin->givePermissionTo('catalogos.leer');

        $response = $this->actingAs($admin)->get(route('admin.catalogos.simples'));

        $response->assertOk();
        $response->assertSee('Cargo / Posición');
        $response->assertDontSee('Cargos en Entidad');
        $response->assertDontSee('Cargos de Entidades');
    }

    public function test_cargo_posicion_aparece_en_perfil_de_cualquier_rol(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        (new \Database\Seeders\CargosEntidadesSeeder)->run();

        $user = User::factory()->create();
        $user->assignRole('lider_semillero');

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response->assertOk();
        $response->assertSee('Cargo / Posición');
        $response->assertSee('Instructor(a)');
    }
}
