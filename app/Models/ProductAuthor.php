<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAuthor extends Model
{
    use HasFactory;

    protected $table = 'product_authors';

    protected $fillable = [
        'product_id',
        'project_author_id',
    ];

    // ─────────────────────────────────────────────
    // RELACIONES
    // ─────────────────────────────────────────────

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function projectAuthor(): BelongsTo
    {
        return $this->belongsTo(ProjectAuthor::class, 'project_author_id');
    }
}
