<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingRecord extends Model
{
    use HasFactory;

    protected $table = 'training_records';

    protected $fillable = [
        'codigo',
        'descripcion',
    ];

    // ─────────────────────────────────────────────
    // RELACIONES
    // ─────────────────────────────────────────────

    public function trainingPrograms(): HasMany
    {
        return $this->hasMany(TrainingProgram::class, 'training_record_id');
    }
}
