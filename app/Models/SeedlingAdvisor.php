<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeedlingAdvisor extends Model
{
    use HasFactory;

    protected $table = 'seedling_advisors';

    protected $fillable = [
        'seedling_id',
        'external_advisor_id',
    ];

    // ─────────────────────────────────────────────
    // RELACIONES
    // ─────────────────────────────────────────────

    public function seedling(): BelongsTo
    {
        return $this->belongsTo(Seedling::class, 'seedling_id');
    }

    public function externalAdvisor(): BelongsTo
    {
        return $this->belongsTo(ExternalAdvisor::class, 'external_advisor_id');
    }
}
