<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeedbedProgressReport extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'seedbed_id',
        'leader_id',
        'seedbed_project_id',
        'report_date',
        'reported_period',
        'progress_percentage',
        'notes',
        'recommendations',
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
            'seedbed_id' => 'integer',
            'leader_id' => 'integer',
            'seedbed_project_id' => 'integer',
            'report_date' => 'date',
            'progress_percentage' => 'decimal:2',
        ];
    }

    public function seedbed(): BelongsTo
    {
        return $this->belongsTo(Seedbed::class);
    }

    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function seedbedProject(): BelongsTo
    {
        return $this->belongsTo(SeedbedProject::class);
    }
}
