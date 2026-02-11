<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResearchProductReview extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'research_product_id',
        'director_id',
        'decision',
        'notes',
        'reviewed_at',
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
            'director_id' => 'integer',
            'reviewed_at' => 'datetime',
        ];
    }

    public function researchProduct(): BelongsTo
    {
        return $this->belongsTo(ResearchProduct::class);
    }

    public function director(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
