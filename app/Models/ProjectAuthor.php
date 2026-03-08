<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectAuthor extends Model
{
    use HasFactory;

    protected $table = 'project_authors';

    protected $fillable = [
        'project_id',
        'user_id',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    // ─────────────────────────────────────────────
    // RELACIONES
    // ─────────────────────────────────────────────

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function productAuthors(): HasMany
    {
        return $this->hasMany(ProductAuthor::class, 'project_author_id');
    }
}
