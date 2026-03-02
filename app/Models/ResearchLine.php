<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResearchLine extends Model
{
    use HasFactory;

    protected $table = 'research_lines';

    protected $fillable = [
        'nombre',
        'descripccion',
    ];

    // ─────────────────────────────────────────────
    // RELACIONES
    // ─────────────────────────────────────────────

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'research_line_id');
    }
}
