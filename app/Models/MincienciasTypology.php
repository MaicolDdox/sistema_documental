<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MincienciasTypology extends Model
{
    use HasFactory;

    protected $table = 'minciencias_typologies';

    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion',
    ];

    // ─────────────────────────────────────────────
    // RELACIONES
    // ─────────────────────────────────────────────

    public function subcategories(): HasMany
    {
        return $this->hasMany(MincienciasSubcategory::class, 'minciencias_typology_id');
    }
}
