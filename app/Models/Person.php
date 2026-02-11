<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Person extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'document_type',
        'document_number',
        'gender',
        'phone',
        'mobile',
        'entity_position_id',
        'engagement_type_id',
        'training_center_id',
        'status',
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
            'user_id' => 'integer',
            'entity_position_id' => 'integer',
            'engagement_type_id' => 'integer',
            'training_center_id' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function entityPosition(): BelongsTo
    {
        return $this->belongsTo(EntityPosition::class);
    }

    public function engagementType(): BelongsTo
    {
        return $this->belongsTo(EngagementType::class);
    }

    public function trainingCenter(): BelongsTo
    {
        return $this->belongsTo(TrainingCenter::class);
    }
}
