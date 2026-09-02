<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingCenter extends Model
{
    use HasFactory;

    protected $table = 'training_centers';

    protected $fillable = [
        'nombre',
        'codigo',
        'department_id',
        'city_id',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * Scope: solo centros activos (los desactivados no deben aparecer en el sistema).
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    // ─────────────────────────────────────────────
    // RELACIONES
    // ─────────────────────────────────────────────

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function seedlings(): HasMany
    {
        return $this->hasMany(Seedling::class, 'training_center_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'training_center_id');
    }
}
