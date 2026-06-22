<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ThematicArea extends Model
{
    use HasFactory;

    protected $table = 'thematic_areas';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    // ─────────────────────────────────────────────
    // RELACIONES
    // ─────────────────────────────────────────────

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'thematic_area_id');
    }
}
