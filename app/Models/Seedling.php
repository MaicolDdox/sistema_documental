<?php

namespace App\Models;

use App\Enums\EstadoEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seedling extends Model
{
    use HasFactory;

    protected $table = 'seedlings';

    protected $fillable = [
        'creator_id',
        'leader_id',
        'research_group_id',
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
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function researchGroup(): BelongsTo
    {
        return $this->belongsTo(ResearchGroup::class, 'research_group_id');
    }

    // HasMany
    public function seedlingFiles(): HasMany
    {
        return $this->hasMany(SeedlingFile::class, 'seedling_id');
    }

    // BelongsToMany
    public function advisors(): BelongsToMany
    {
        return $this->belongsToMany(
            ExternalAdvisor::class,
            'seedling_advisors',
            'seedling_id',
            'external_advisor_id'
        )->withTimestamps();
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'seedling_members',
            'seedling_id',
            'user_id'
        )->withTimestamps();
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(
            Project::class,
            'project_seedlings',
            'seedling_id',
            'project_id'
        )->withTimestamps();
    }

    // ─────────────────────────────────────────────
    // SCOPES
    // ─────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('estado', EstadoEnum::Activo);
    }
}
