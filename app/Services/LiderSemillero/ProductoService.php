<?php

declare(strict_types=1);

namespace App\Services\LiderSemillero;

use App\Enums\EstadoRevisionEnum;
use App\Models\Product;
use App\Models\Seedling;
use App\Models\User;

class ProductoService
{
    // ─────────────────────────────────────────────────
    // Metadatos de vista
    // ─────────────────────────────────────────────────

    /**
     * Anota las banderas de presentación sobre un producto para la vista del líder.
     * `es_mio`     — el líder es el autor principal del producto.
     * `ya_en_grupo` — el producto ya tiene un GroupProduct vinculado.
     * `autor`      — el usuario autor principal (puede ser null).
     */
    public function anotarMetadatos(Product $producto, int $liderId): Product
    {
        $autorPrincipal = $producto->productAuthors->first()?->projectAuthor?->user;

        $producto->es_mio     = $autorPrincipal && $autorPrincipal->id === $liderId;
        $producto->autor      = $autorPrincipal;
        $producto->ya_en_grupo = $producto->groupProducts->isNotEmpty();

        return $producto;
    }

    // ─────────────────────────────────────────────────
    // Asignación a investigador
    // ─────────────────────────────────────────────────

    /**
     * Valida las reglas de negocio y asigna el producto aprobado al investigador indicado.
     *
     * @throws \DomainException con mensaje localizado cuando una regla falla.
     */
    public function asignarAInvestigador(
        Product $producto,
        int     $investigadorId,
        Seedling $semillero,
        User    $lider,
    ): void {
        if ($producto->groupProducts()->exists()) {
            throw new \DomainException('Este producto ya está vinculado al grupo de investigación.');
        }

        if ($producto->estado_revision !== EstadoRevisionEnum::Aprobado) {
            throw new \DomainException('Solo puedes asignar productos aprobados.');
        }

        $investigador = User::with(['roles', 'researchGroups'])->findOrFail($investigadorId);

        if (! $investigador->hasRole('investigador_asociado')) {
            throw new \DomainException('El usuario seleccionado no tiene rol de investigador asociado.');
        }

        $idsGruposInv = $investigador->researchGroups
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (! in_array((int) $semillero->research_group_id, $idsGruposInv, true)) {
            throw new \DomainException('El investigador debe pertenecer al mismo grupo de investigación vinculado a tu semillero.');
        }

        if ($lider->training_center_id
            && (int) $investigador->training_center_id !== (int) $lider->training_center_id) {
            throw new \DomainException('El investigador debe pertenecer a tu mismo centro de formación.');
        }

        $producto->update(['assigned_investigator_user_id' => $investigadorId]);
    }
}
