<?php

namespace App\Models;

use App\Enums\GeneroEnum;
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
        'primer_nombre',
        'segundo_nombre',
        'primer_apellido',
        'segundo_apellido',
        'genero',
        'telefono',
        'celular',
        'eps',
        'email_institucional',
    ];

    protected $casts = [
        'genero' => GeneroEnum::class,
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
