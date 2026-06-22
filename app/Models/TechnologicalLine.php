<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TechnologicalLine extends Model
{
    use HasFactory;

    protected $table = 'technological_lines';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    // ─────────────────────────────────────────────
    // RELACIONES
    // ─────────────────────────────────────────────

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'technological_line_id');
    }
}
