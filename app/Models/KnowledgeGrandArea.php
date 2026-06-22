<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KnowledgeGrandArea extends Model
{
    use HasFactory;

    protected $table = 'knowledge_grand_areas';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    // ─────────────────────────────────────────────
    // RELACIONES
    // ─────────────────────────────────────────────

    public function knowledgeAreas(): HasMany
    {
        return $this->hasMany(KnowledgeArea::class, 'knowledge_grand_area_id');
    }

    public function groupProducts(): HasMany
    {
        return $this->hasMany(GroupProduct::class, 'knowledge_grand_area_id');
    }
}
