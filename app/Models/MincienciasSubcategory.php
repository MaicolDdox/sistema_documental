<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MincienciasSubcategory extends Model
{
    use HasFactory;

    protected $table = 'minciencias_subcategories';

    protected $fillable = [
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

    public function groupProducts(): HasMany
    {
        return $this->hasMany(GroupProduct::class, 'minciencias_subcategory_id');
    }
}
