<?php

namespace App\Models;

use App\Enums\EstadoEnum;
use App\Enums\JornadaEnum;
use App\Enums\ModalidadEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingProgram extends Model
{
    use HasFactory;

    protected $table = 'training_programs';

    protected $fillable = [
        'training_record_id',
        'training_program_type_id',
        'nombre',
        'descripcion',
        'jornada',
        'modalidad',
        'estado',
    ];

    protected $casts = [
        'jornada' => JornadaEnum::class,
        'modalidad' => ModalidadEnum::class,
        'estado' => EstadoEnum::class,
    ];

    // ─────────────────────────────────────────────
    // RELACIONES
    // ─────────────────────────────────────────────

    public function trainingRecord(): BelongsTo
    {
        return $this->belongsTo(TrainingRecord::class, 'training_record_id');
    }

    public function trainingProgramType(): BelongsTo
    {
        return $this->belongsTo(TrainingProgramType::class, 'training_program_type_id');
    }

    public function people(): HasMany
    {
        return $this->hasMany(Person::class, 'training_program_id');
    }

    // ─────────────────────────────────────────────
    // SCOPES
    // ─────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('estado', EstadoEnum::Activo);
    }
}
