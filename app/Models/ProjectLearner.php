<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Aprendiz registrado como DATO dentro de un proyecto (no es un usuario
 * del sistema). Ficha es texto libre. El programa de formación (antes texto
 * libre "nombre_tecnologo") se conecta al catálogo real training_programs
 * desde BUG-20260813-047.
 */
class ProjectLearner extends Model
{
    use HasFactory;

    protected $table = 'project_learners';

    protected $fillable = [
        'project_id',
        'created_by_user_id',
        'nombre_completo',
        'numero_documento',
        'ficha',
        'telefono',
        'email',
        'training_program_id',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function trainingProgram(): BelongsTo
    {
        return $this->belongsTo(TrainingProgram::class, 'training_program_id');
    }
}
