<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\TrainingCenter;

class ExternalAdvisor extends Model
{
    use HasFactory;

    protected $table = 'external_advisors';

    protected $fillable = [
        'user_id',
        'training_center_id',
        'nombre_completo',
        'email',
        'telefono',
        'institucion',
        'cvlac_link',
    ];

    // ─────────────────────────────────────────────
    // RELACIONES
    // ─────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function trainingCenter(): BelongsTo
    {
        return $this->belongsTo(TrainingCenter::class, 'training_center_id');
    }

    public function seedlings(): BelongsToMany
    {
        return $this->belongsToMany(
            Seedling::class,
            'seedling_advisors',
            'external_advisor_id',
            'seedling_id'
        )->withTimestamps();
    }
}
