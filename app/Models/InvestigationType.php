<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvestigationType extends Model
{
    use HasFactory;

    protected $table = 'investigation_types';

    protected $fillable = [
        'nombre',
        'descripccion',
    ];

    // ─────────────────────────────────────────────
    // RELACIONES
    // ─────────────────────────────────────────────

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'investigation_type_id');
    }
}
