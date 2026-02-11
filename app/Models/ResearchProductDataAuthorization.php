<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResearchProductDataAuthorization extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'research_product_id',
        'authorized',
        'authorized_at',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'research_product_id' => 'integer',
            'authorized' => 'boolean',
            'authorized_at' => 'datetime',
        ];
    }

    public function researchProduct(): BelongsTo
    {
        return $this->belongsTo(ResearchProduct::class);
    }
}
