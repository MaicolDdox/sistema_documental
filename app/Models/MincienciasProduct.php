<?php

namespace App\Models;

use App\Enums\EstadoEnum;
use App\Enums\EstadoRevisionEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Producto Minciencias personal de un co-investigador. No se vincula a
 * ningún semillero, proyecto ni líder de proyecto — es propiedad exclusiva
 * del usuario que lo crea (ver user_id). El co-investigador elige el centro
 * de formación al que queda vinculado (training_center_id); el
 * administrador_sistema de ese centro es quien lo aprueba/rechaza
 * (estado_revision), ver BUG-20260813-029.
 */
class MincienciasProduct extends Model
{
    use HasFactory;

    protected $table = 'minciencias_products';

    protected $fillable = [
        'user_id',
        'training_center_id',
        'research_line_id',
        'technological_line_id',
        'thematic_area_id',
        'project_modality_id',
        'investigation_type_id',
        'nombre',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'estado_revision',
        'observacion_admin',
        'revisado_por',
        'revisado_at',
    ];

    protected function casts(): array
    {
        return [
            'estado' => EstadoEnum::class,
            'estado_revision' => EstadoRevisionEnum::class,
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'revisado_at' => 'datetime',
        ];
    }

    // ─────────────────────────────────────────────
    // RELACIONES
    // ─────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function trainingCenter(): BelongsTo
    {
        return $this->belongsTo(TrainingCenter::class, 'training_center_id');
    }

    public function revisadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revisado_por');
    }

    public function researchLine(): BelongsTo
    {
        return $this->belongsTo(ResearchLine::class, 'research_line_id');
    }

    public function technologicalLine(): BelongsTo
    {
        return $this->belongsTo(TechnologicalLine::class, 'technological_line_id');
    }

    public function thematicArea(): BelongsTo
    {
        return $this->belongsTo(ThematicArea::class, 'thematic_area_id');
    }

    public function projectModality(): BelongsTo
    {
        return $this->belongsTo(ProjectModality::class, 'project_modality_id');
    }

    public function investigationType(): BelongsTo
    {
        return $this->belongsTo(InvestigationType::class, 'investigation_type_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(MincienciasProductFile::class, 'minciencias_product_id');
    }
}
