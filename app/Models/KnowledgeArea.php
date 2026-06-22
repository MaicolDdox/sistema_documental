<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KnowledgeArea extends Model
{
    use HasFactory;

    protected $table = 'knowledge_areas';

    protected $fillable = [
        'knowledge_grand_area_id',
        'nombre',
        'descripcion',
    ];

    // ─────────────────────────────────────────────
    // RELACIONES
    // ─────────────────────────────────────────────

    public function knowledgeGrandArea(): BelongsTo
    {
        return $this->belongsTo(KnowledgeGrandArea::class, 'knowledge_grand_area_id');
    }

    public function groupProducts(): HasMany
    {
        return $this->hasMany(GroupProduct::class, 'knowledge_area_id');
    }
}
