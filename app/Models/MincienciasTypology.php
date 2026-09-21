<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MincienciasTypology extends Model
{
    use HasFactory;

    protected $table = 'minciencias_typologies';

    protected $fillable = [
        'training_center_id',
        'nombre',
        'codigo',
        'descripcion',
    ];

    // ─────────────────────────────────────────────
    // RELACIONES
    // ─────────────────────────────────────────────

    public function trainingCenter(): BelongsTo
    {
        return $this->belongsTo(TrainingCenter::class, 'training_center_id');
    }

    public function subcategories(): HasMany
    {
        return $this->hasMany(MincienciasSubcategory::class, 'minciencias_typology_id');
    }
}
