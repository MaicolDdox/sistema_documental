<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
    ];

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

    public function researchGroups(): HasMany
    {
        return $this->hasMany(ResearchGroup::class, 'training_center_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'training_center_id');
    }
}
