<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingProgramType extends Model
{
    use HasFactory;

    protected $table = 'training_program_types';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    // ─────────────────────────────────────────────
    // RELACIONES
    // ─────────────────────────────────────────────

    public function trainingPrograms(): HasMany
    {
        return $this->hasMany(TrainingProgram::class, 'training_program_type_id');
    }
}
