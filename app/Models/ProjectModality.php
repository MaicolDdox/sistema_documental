<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectModality extends Model
{
    use HasFactory;

    protected $table = 'project_modalities';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    // ─────────────────────────────────────────────
    // RELACIONES
    // ─────────────────────────────────────────────

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'project_modality_id');
    }
}
