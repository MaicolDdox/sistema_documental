<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MincienciasProductFile extends Model
{
    use HasFactory;

    protected $table = 'minciencias_product_files';

    protected $fillable = [
        'minciencias_product_id',
        'archivo',
        'url_archivo',
        'descripcion',
        'uploaded_by',
    ];

    // ─────────────────────────────────────────────
    // RELACIONES
    // ─────────────────────────────────────────────

    public function product(): BelongsTo
    {
        return $this->belongsTo(MincienciasProduct::class, 'minciencias_product_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
