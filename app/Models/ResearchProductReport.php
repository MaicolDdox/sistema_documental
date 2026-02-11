<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResearchProductReport extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'research_group_id',
        'research_product_id',
        'file_path',
        'report_type',
        'description',
        'report_date',
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
            'research_group_id' => 'integer',
            'research_product_id' => 'integer',
            'report_date' => 'date',
        ];
    }

    public function researchGroup(): BelongsTo
    {
        return $this->belongsTo(ResearchGroup::class);
    }

    public function researchProduct(): BelongsTo
    {
        return $this->belongsTo(ResearchProduct::class);
    }
}
