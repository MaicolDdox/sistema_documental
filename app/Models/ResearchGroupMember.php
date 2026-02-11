<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResearchGroupMember extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'research_group_id',
        'researcher_id',
        'joined_on',
        'left_on',
        'status',
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
            'research_group_id' => 'integer',
            'researcher_id' => 'integer',
            'joined_on' => 'date',
            'left_on' => 'date',
        ];
    }

    public function researchGroup(): BelongsTo
    {
        return $this->belongsTo(ResearchGroup::class);
    }

    public function researcher(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
