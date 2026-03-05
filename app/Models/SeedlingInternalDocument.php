<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeedlingInternalDocument extends Model
{
    use HasFactory;

    protected $table = 'seedling_internal_documents';

    protected $fillable = [
        'seedling_id',
        'user_id',
        'titulo',
        'tipo',
        'url_archivo',
    ];

    public function seedling(): BelongsTo
    {
        return $this->belongsTo(Seedling::class, 'seedling_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
