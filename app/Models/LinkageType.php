<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LinkageType extends Model
{
    use HasFactory;

    protected $table = 'linkage_types';

    protected $fillable = [
        'nombre',
        'descripccion',
    ];

    // ─────────────────────────────────────────────
    // RELACIONES
    // ─────────────────────────────────────────────

    public function people(): HasMany
    {
        return $this->hasMany(Person::class, 'linkage_type_id');
    }
}
