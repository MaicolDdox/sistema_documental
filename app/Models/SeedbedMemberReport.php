<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeedbedMemberReport extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'seedbed_id',
        'apprentice_id',
        'advisor_id',
        'member_type',
        'period',
        'notes',
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
            'seedbed_id' => 'integer',
            'apprentice_id' => 'integer',
            'advisor_id' => 'integer',
            'report_date' => 'date',
        ];
    }

    public function seedbed(): BelongsTo
    {
        return $this->belongsTo(Seedbed::class);
    }

    public function apprentice(): BelongsTo
    {
        return $this->belongsTo(Apprentice::class);
    }

    public function advisor(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
