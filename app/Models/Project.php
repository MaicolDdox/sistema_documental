<?php

namespace App\Models;

use App\Enums\EstadoEnum;
use App\Enums\TipoProyectoOrigenEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $table = 'projects';

    protected $fillable = [
        'project_creator_id',
        'seedling_id',
        'lider_proyecto_user_id',
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
        'vinculacion_macro_proyecto',
        'macro_project_id',
        'tipo_financiacion',
        'tipo_proyecto_origen',
    ];

    protected $casts = [
        'estado' => EstadoEnum::class,
        'tipo_proyecto_origen' => TipoProyectoOrigenEnum::class,
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'vinculacion_macro_proyecto' => 'boolean',
    ];

    // ─────────────────────────────────────────────
    // RELACIONES
    // ─────────────────────────────────────────────

    // BelongsTo
    public function projectCreator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'project_creator_id');
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

    // HasMany
    public function projectAuthors(): HasMany
    {
        return $this->hasMany(ProjectAuthor::class, 'project_id');
    }

    public function macroProject(): BelongsTo
    {
        return $this->belongsTo(MacroProject::class, 'macro_project_id');
    }

    /**
     * Alias de compatibilidad para código legado que aún
     * espera una relación llamada macroProjectLinkages().
     */
    public function macroProjectLinkages(): BelongsTo
    {
        return $this->macroProject();
    }

    public function projectEvidences(): HasMany
    {
        return $this->hasMany(ProjectEvidence::class, 'project_id');
    }

    /** Evidencias de trabajo en curso — sin aprobación. */
    public function evidenciasDesarrollo(): HasMany
    {
        return $this->projectEvidences()->where('tipo', \App\Enums\TipoEvidenciaEnum::Desarrollo);
    }

    /** Evidencias de producto final — flujo de aprobación de 2 etapas. */
    public function evidenciasProductoFinal(): HasMany
    {
        return $this->projectEvidences()->where('tipo', \App\Enums\TipoEvidenciaEnum::ProductoFinal);
    }

    /** Evidencias de formulación (30% del avance) — aprobación de 1 sola etapa (líder de semillero). */
    public function evidenciasFormulacion(): HasMany
    {
        return $this->projectEvidences()->where('tipo', \App\Enums\TipoEvidenciaEnum::Formulacion);
    }

    /** Evidencias de ejecución (50% del avance) — aprobación de 1 sola etapa (líder de semillero). */
    public function evidenciasEjecucion(): HasMany
    {
        return $this->projectEvidences()->where('tipo', \App\Enums\TipoEvidenciaEnum::Ejecucion);
    }

    public function learners(): HasMany
    {
        return $this->hasMany(ProjectLearner::class, 'project_id');
    }

    // BelongsTo
    public function seedling(): BelongsTo
    {
        return $this->belongsTo(Seedling::class, 'seedling_id');
    }

    public function liderProyecto(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lider_proyecto_user_id');
    }

    // BelongsToMany
    /** Co-investigadores vinculados al proyecto (reutiliza project_authors). */
    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'project_authors',
            'project_id',
            'user_id'
        )->withPivot('activo')->withTimestamps();
    }

    // ─────────────────────────────────────────────
    // SCOPES
    // ─────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('estado', EstadoEnum::Activo);
    }
}
