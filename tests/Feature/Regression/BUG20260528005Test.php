<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Enums\EstadoRevisionEnum;
use App\Models\GroupProduct;
use App\Models\User;
use App\Policies\GroupProductPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresión: BUG-20260528-005
 * GroupProductPolicy no tenía el método subirEvidencia(), causando un 403
 * silencioso al intentar subir evidencias en el detalle del producto.
 * Corregido: 2026-05-28 — métodos subirEvidencia, view, update, delete añadidos.
 */
class BUG20260528005Test extends TestCase
{
    use RefreshDatabase;

    public function test_author_can_subir_evidencia_when_product_is_pending(): void
    {
        $author = User::factory()->create();
        $other  = User::factory()->create();

        $product = new GroupProduct([
            'author_id'      => $author->id,
            'estado_revision' => EstadoRevisionEnum::Pendiente,
        ]);

        $policy = new GroupProductPolicy();

        $this->assertTrue($policy->subirEvidencia($author, $product));
        $this->assertFalse($policy->subirEvidencia($other, $product));
    }

    public function test_author_cannot_subir_evidencia_when_product_is_approved(): void
    {
        $author = User::factory()->create();

        $product = new GroupProduct([
            'author_id'      => $author->id,
            'estado_revision' => EstadoRevisionEnum::Aprobado,
        ]);

        $policy = new GroupProductPolicy();

        $this->assertFalse($policy->subirEvidencia($author, $product));
    }

    public function test_author_can_update_only_when_rejected(): void
    {
        $author = User::factory()->create();

        $rejected = new GroupProduct(['author_id' => $author->id, 'estado_revision' => EstadoRevisionEnum::Rechazado]);
        $pending  = new GroupProduct(['author_id' => $author->id, 'estado_revision' => EstadoRevisionEnum::Pendiente]);

        $policy = new GroupProductPolicy();

        $this->assertTrue($policy->update($author, $rejected));
        $this->assertFalse($policy->update($author, $pending));
    }

    public function test_author_can_delete_only_when_pending(): void
    {
        $author = User::factory()->create();

        $pending  = new GroupProduct(['author_id' => $author->id, 'estado_revision' => EstadoRevisionEnum::Pendiente]);
        $approved = new GroupProduct(['author_id' => $author->id, 'estado_revision' => EstadoRevisionEnum::Aprobado]);

        $policy = new GroupProductPolicy();

        $this->assertTrue($policy->delete($author, $pending));
        $this->assertFalse($policy->delete($author, $approved));
    }

    public function test_director_can_view_any_group_product(): void
    {
        \Spatie\Permission\Models\Role::create(['name' => 'director_investigacion', 'guard_name' => 'web']);

        $director = User::factory()->create();
        $director->assignRole('director_investigacion');

        $product = new GroupProduct(['author_id' => 999, 'estado_revision' => EstadoRevisionEnum::Pendiente]);

        $policy = new GroupProductPolicy();

        $this->assertTrue($policy->view($director, $product));
    }
}
