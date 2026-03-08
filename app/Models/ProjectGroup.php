<?php

namespace App\Models;

use App\Enums\TipoParticipacionEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectGroup extends Model
{
    use HasFactory;

    protected $table = 'project_groups';

    protected $fillable = [
        'research_group_id',
        'project_id',
        'tipo_participacion',
    ];

    protected $casts = [
        'tipo_participacion' => TipoParticipacionEnum::class,
    ];

    // ─────────────────────────────────────────────
    // RELACIONES
    // ─────────────────────────────────────────────

    public function researchGroup(): BelongsTo
    {
        return $this->belongsTo(ResearchGroup::class, 'research_group_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
