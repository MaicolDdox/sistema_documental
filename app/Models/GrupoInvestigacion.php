<?php

namespace App\Models;

use App\Enums\EstadoEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrupoInvestigacion extends Model
{
    use HasFactory;

    protected $table = 'grupos_investigacion';

    protected $fillable = [
        'training_center_id',
        'creator_id',
        'director_id',
        'nombre',
        'codigo',
        'logo',
        'descripcion',
        'estado',
    ];

    protected $casts = [
        'estado' => EstadoEnum::class,
    ];

    // ─────────────────────────────────────────────
    // RELACIONES
    // ─────────────────────────────────────────────

    public function trainingCenter(): BelongsTo
    {
        return $this->belongsTo(TrainingCenter::class, 'training_center_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function director(): BelongsTo
    {
        return $this->belongsTo(User::class, 'director_id');
    }

    public function lineasInvestigacion(): BelongsToMany
    {
        return $this->belongsToMany(ResearchLine::class, 'grupo_investigacion_lineas', 'grupo_investigacion_id', 'research_line_id');
    }

    public function mincienciasProducts(): HasMany
    {
        return $this->hasMany(MincienciasProduct::class, 'grupo_investigacion_id');
    }

    // ─────────────────────────────────────────────
    // SCOPES
    // ─────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('estado', EstadoEnum::Activo);
    }
}
