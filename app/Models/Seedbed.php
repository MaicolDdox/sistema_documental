<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Seedbed extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'seedbed_code',
        'logo_path',
        'description',
        'director_id',
        'training_center_id',
        'status',
        'founded_on',
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
            'director_id' => 'integer',
            'training_center_id' => 'integer',
            'founded_on' => 'date',
        ];
    }

    public function director(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trainingCenter(): BelongsTo
    {
        return $this->belongsTo(TrainingCenter::class);
    }
}
