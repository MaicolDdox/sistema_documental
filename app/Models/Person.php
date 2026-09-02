<?php

namespace App\Models;

use App\Enums\GeneroEnum;
use App\Enums\NivelFormacionEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Person extends Model
{
    use HasFactory;

    protected $table = 'people';

    protected $fillable = [
        'user_id',
        'entity_position_id',
        'linkage_type_id',
        'training_program_id',
        'training_program_otro',
        'primer_nombre',
        'segundo_nombre',
        'primer_apellido',
        'segundo_apellido',
        'genero',
        'telefono',
        'celular',
        'eps',
        'cvlac_link',
        'nivel_formacion',
        'fecha_vinculacion',
        'email_institucional',
    ];

    protected $casts = [
        'genero' => GeneroEnum::class,
        'nivel_formacion' => NivelFormacionEnum::class,
        'fecha_vinculacion' => 'date',
    ];

    // ─────────────────────────────────────────────
    // RELACIONES
    // ─────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function entityPosition(): BelongsTo
    {
        return $this->belongsTo(EntityPosition::class, 'entity_position_id');
    }

    public function linkageType(): BelongsTo
    {
        return $this->belongsTo(LinkageType::class, 'linkage_type_id');
    }

    public function trainingProgram(): BelongsTo
    {
        return $this->belongsTo(TrainingProgram::class, 'training_program_id');
    }

    // ─────────────────────────────────────────────
    // ACCESSORS
    // ─────────────────────────────────────────────

    /**
     * Nombre completo de la persona.
     */
    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->primer_nombre} {$this->segundo_nombre} {$this->primer_apellido} {$this->segundo_apellido}");
    }
}
