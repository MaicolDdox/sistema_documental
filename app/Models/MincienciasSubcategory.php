<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MincienciasSubcategory extends Model
{
    use HasFactory;

    protected $table = 'minciencias_subcategories';

    protected $fillable = [
        'training_center_id',
        'minciencias_typology_id',
        'nombre',
        'descripcion',
    ];

    // ─────────────────────────────────────────────
    // RELACIONES
    // ─────────────────────────────────────────────

    public function mincienciasTypology(): BelongsTo
    {
        return $this->belongsTo(MincienciasTypology::class, 'minciencias_typology_id');
    }

    public function trainingCenter(): BelongsTo
    {
        return $this->belongsTo(TrainingCenter::class, 'training_center_id');
    }
}
