<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EntityPosition extends Model
{
    use HasFactory;

    protected $table = 'entity_positions';

    protected $fillable = [
        'nombre',
        'descripccion',
    ];

    // ─────────────────────────────────────────────
    // RELACIONES
    // ─────────────────────────────────────────────

    public function people(): HasMany
    {
        return $this->hasMany(Person::class, 'entity_position_id');
    }
}
