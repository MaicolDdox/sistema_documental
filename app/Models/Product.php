<?php

namespace App\Models;

use App\Enums\EstadoEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'project_id',
        'nombre',
        'archivo',
        'estado',
    ];

    protected $casts = [
        'estado' => EstadoEnum::class,
    ];

    // ─────────────────────────────────────────────
    // RELACIONES
    // ─────────────────────────────────────────────

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function productAuthors(): HasMany
    {
        return $this->hasMany(ProductAuthor::class, 'product_id');
    }

    public function productEvidences(): HasMany
    {
        return $this->hasMany(ProductEvidence::class, 'product_id');
    }

    public function groupProducts(): HasMany
    {
        return $this->hasMany(GroupProduct::class, 'product_id');
    }

    // ─────────────────────────────────────────────
    // SCOPES
    // ─────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('estado', EstadoEnum::Activo);
    }
}
