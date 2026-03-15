<?php

namespace App\Models;

use App\Enums\EstadoRevisionEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupProductReview extends Model
{
    use HasFactory;

    protected $table = 'group_product_reviews';

    protected $fillable = [
        'group_product_id',
        'reviewer_id',
        'research_group_id',
        'accion',
        'observaciones',
    ];

    // ─────────────────────────────────────────────
    // RELACIONES
    // ─────────────────────────────────────────────

    public function groupProduct(): BelongsTo
    {
        return $this->belongsTo(GroupProduct::class, 'group_product_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function researchGroup(): BelongsTo
    {
        return $this->belongsTo(ResearchGroup::class, 'research_group_id');
    }
}
