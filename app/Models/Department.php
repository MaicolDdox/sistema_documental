<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    protected $table = 'departments';

    protected $fillable = [
        'nombre',
    ];

    // ─────────────────────────────────────────────
    // RELACIONES
    // ─────────────────────────────────────────────

    public function cities(): HasMany
    {
        return $this->hasMany(City::class, 'department_id');
    }

    public function trainingCenters(): HasMany
    {
        return $this->hasMany(TrainingCenter::class, 'department_id');
    }
}
