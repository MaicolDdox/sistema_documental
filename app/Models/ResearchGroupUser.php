<?php

namespace App\Models;

use App\Enums\RolGrupoEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResearchGroupUser extends Model
{
    use HasFactory;

    protected $table = 'research_group_users';

    protected $fillable = [
        'research_group_id',
        'user_id',
        'rol',
    ];

    protected $casts = [
        'rol' => RolGrupoEnum::class,
    ];

    // ─────────────────────────────────────────────
    // RELACIONES
    // ─────────────────────────────────────────────

    public function researchGroup(): BelongsTo
    {
        return $this->belongsTo(ResearchGroup::class, 'research_group_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
