<?php

namespace App\Models;

use App\Enums\EstadoEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResearchGroup extends Model
{
    use HasFactory;

    protected $table = 'research_groups';

    protected $fillable = [
        'training_center_id',
        'nombre',
        'codigo',
        'logo',
        'descripccion',
        'estado',
    ];

    protected $casts = [
        'estado' => EstadoEnum::class,
    ];

    // ─────────────────────────────────────────────
    // RELACIONES
    // ─────────────────────────────────────────────

    // BelongsTo
    public function trainingCenter(): BelongsTo
    {
        return $this->belongsTo(TrainingCenter::class, 'training_center_id');
    }

    // HasMany
    public function seedlings(): HasMany
    {
        return $this->hasMany(Seedling::class, 'research_group_id');
    }

    public function researchGroupUsers(): HasMany
    {
        return $this->hasMany(ResearchGroupUser::class, 'research_group_id');
    }

    public function macroProjectLinkages(): HasMany
    {
        return $this->hasMany(MacroProjectLinkage::class, 'research_group_id');
    }

    // BelongsToMany
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'research_group_users',
            'research_group_id',
            'user_id'
        )->withPivot('rol')->withTimestamps();
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(
            Project::class,
            'project_groups',
            'research_group_id',
            'project_id'
        )->withPivot('tipo_participacion')->withTimestamps();
    }

    // ─────────────────────────────────────────────
    // SCOPES
    // ─────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('estado', EstadoEnum::Activo);
    }
}
