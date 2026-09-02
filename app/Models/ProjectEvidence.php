<?php

namespace App\Models;

use App\Enums\EstadoRevisionEnum;
use App\Enums\TipoEvidenciaEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Evidencia de un proyecto. tipo=desarrollo no lleva aprobación.
 * tipo=producto_final es "el producto" del rediseño: su propio flujo de
 * revisión en 2 etapas (lider de semillero -> director de semilleros)
 * reemplaza los antiguos modelos Product/GroupProduct.
 */
class ProjectEvidence extends Model
{
    use HasFactory;

    protected $table = 'project_evidences';

    protected $fillable = [
        'project_id',
        'tipo',
        'nombre',
        'uploaded_by',
        'archivo',
        'url_archivo',
        'descripcion',
        'estado_revision_lider',
        'observacion_lider',
        'revisado_lider_por',
        'revisado_lider_at',
        'estado_revision_director',
        'observacion_director',
        'revisado_director_por',
        'revisado_director_at',
        'visto_por_lider_proyecto_at',
        'visto_por_lider_semillero_at',
    ];

    protected $casts = [
        'tipo' => TipoEvidenciaEnum::class,
        'estado_revision_lider' => EstadoRevisionEnum::class,
        'estado_revision_director' => EstadoRevisionEnum::class,
        'revisado_lider_at' => 'datetime',
        'revisado_director_at' => 'datetime',
        'visto_por_lider_proyecto_at' => 'datetime',
        'visto_por_lider_semillero_at' => 'datetime',
    ];

    // ─────────────────────────────────────────────
    // RELACIONES
    // ─────────────────────────────────────────────

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function revisadoLiderPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revisado_lider_por');
    }

    public function revisadoDirectorPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revisado_director_por');
    }
}
