<?php

namespace App\Services\Director;

use App\Enums\EstadoRevisionEnum;
use App\Models\GroupProduct;
use App\Models\GroupProductReview;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RevisionProductoService
{
    /**
     * Aprueba un GroupProduct.
     * Regla: debe tener al menos una evidencia adjunta.
     */
    public function aprobar(GroupProduct $producto, int $reviewerId, int $grupoId): void
    {
        // Validar evidencias en product_evidences
        $tieneEvidencias = $producto->product->productEvidences()->exists();
        if (! $tieneEvidencias) {
            throw new RuntimeException('El producto debe tener al menos una evidencia antes de ser aprobado.');
        }

        DB::transaction(function () use ($producto, $reviewerId, $grupoId) {
            $producto->update(['estado_revision' => EstadoRevisionEnum::Aprobado]);

            GroupProductReview::create([
                'group_product_id'  => $producto->id,
                'reviewer_id'       => $reviewerId,
                'research_group_id' => $grupoId,
                'accion'            => 'aprobado',
                'observaciones'     => null,
            ]);
        });
    }

    /**
     * Rechaza un GroupProduct con observaciones obligatorias.
     */
    public function rechazar(GroupProduct $producto, int $reviewerId, int $grupoId, string $observaciones): void
    {
        if (trim($observaciones) === '') {
            throw new RuntimeException('Las observaciones son obligatorias al rechazar un producto.');
        }

        DB::transaction(function () use ($producto, $reviewerId, $grupoId, $observaciones) {
            $producto->update([
                'estado_revision'        => EstadoRevisionEnum::Rechazado,
                'observaciones_revision' => $observaciones,
            ]);

            GroupProductReview::create([
                'group_product_id'  => $producto->id,
                'reviewer_id'       => $reviewerId,
                'research_group_id' => $grupoId,
                'accion'            => 'rechazado',
                'observaciones'     => $observaciones,
            ]);
        });
    }

    /**
     * Marca un producto como "en revisión".
     */
    public function marcarEnRevision(GroupProduct $producto, int $reviewerId, int $grupoId): void
    {
        DB::transaction(function () use ($producto, $reviewerId, $grupoId) {
            $producto->update(['estado_revision' => EstadoRevisionEnum::EnRevision]);

            GroupProductReview::create([
                'group_product_id'  => $producto->id,
                'reviewer_id'       => $reviewerId,
                'research_group_id' => $grupoId,
                'accion'            => 'en_revision',
                'observaciones'     => null,
            ]);
        });
    }
}
