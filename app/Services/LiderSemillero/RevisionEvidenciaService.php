<?php

declare(strict_types=1);

namespace App\Services\LiderSemillero;

use App\Enums\EstadoRevisionEnum;
use App\Models\ProjectEvidence;
use App\Models\User;

/**
 * Flujo de aprobación de 2 etapas de la evidencia de producto final
 * (rediseño de roles): Líder de Semillero -> Director de Semilleros.
 * Un rechazo en cualquiera de las 2 etapas deja la evidencia cerrada;
 * el Líder de Proyecto corrige subiendo una evidencia nueva.
 */
class RevisionEvidenciaService
{
    public function aprobarEtapaLider(ProjectEvidence $evidencia, User $lider, ?string $observaciones): void
    {
        $evidencia->update([
            'estado_revision_lider' => EstadoRevisionEnum::Aprobado,
            'observacion_lider' => $observaciones,
            'revisado_lider_por' => $lider->id,
            'revisado_lider_at' => now(),
            'estado_revision_director' => EstadoRevisionEnum::Pendiente,
            // El líder de proyecto tiene una novedad sin ver; el líder de
            // semillero acaba de actuar, así que para él queda visto.
            'visto_por_lider_proyecto_at' => null,
            'visto_por_lider_semillero_at' => now(),
        ]);
    }

    public function rechazarEtapaLider(ProjectEvidence $evidencia, User $lider, string $observaciones): void
    {
        $evidencia->update([
            'estado_revision_lider' => EstadoRevisionEnum::Rechazado,
            'observacion_lider' => $observaciones,
            'revisado_lider_por' => $lider->id,
            'revisado_lider_at' => now(),
            'visto_por_lider_proyecto_at' => null,
            'visto_por_lider_semillero_at' => now(),
        ]);
    }

    public function aprobarEtapaDirector(ProjectEvidence $evidencia, User $director, ?string $observaciones): void
    {
        $evidencia->update([
            'estado_revision_director' => EstadoRevisionEnum::Aprobado,
            'observacion_director' => $observaciones,
            'revisado_director_por' => $director->id,
            'revisado_director_at' => now(),
            // El director actuó: tanto el líder de proyecto como el líder de
            // semillero tienen una novedad sin ver.
            'visto_por_lider_proyecto_at' => null,
            'visto_por_lider_semillero_at' => null,
        ]);
    }

    public function rechazarEtapaDirector(ProjectEvidence $evidencia, User $director, string $observaciones): void
    {
        $evidencia->update([
            'estado_revision_director' => EstadoRevisionEnum::Rechazado,
            'observacion_director' => $observaciones,
            'revisado_director_por' => $director->id,
            'revisado_director_at' => now(),
            'visto_por_lider_proyecto_at' => null,
            'visto_por_lider_semillero_at' => null,
        ]);
    }

    /**
     * Aprobación de 1 sola etapa (Formulación/Ejecución): a diferencia de
     * aprobarEtapaLider(), NO abre la etapa de director — esos tipos no
     * pasan por el Director de Semilleros.
     */
    public function aprobarEtapaUnica(ProjectEvidence $evidencia, User $lider, ?string $observaciones): void
    {
        $evidencia->update([
            'estado_revision_lider' => EstadoRevisionEnum::Aprobado,
            'observacion_lider' => $observaciones,
            'revisado_lider_por' => $lider->id,
            'revisado_lider_at' => now(),
            'visto_por_lider_proyecto_at' => null,
        ]);
    }

    public function rechazarEtapaUnica(ProjectEvidence $evidencia, User $lider, string $observaciones): void
    {
        $evidencia->update([
            'estado_revision_lider' => EstadoRevisionEnum::Rechazado,
            'observacion_lider' => $observaciones,
            'revisado_lider_por' => $lider->id,
            'revisado_lider_at' => now(),
            'visto_por_lider_proyecto_at' => null,
        ]);
    }
}
