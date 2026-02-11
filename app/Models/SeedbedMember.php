<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeedbedMember extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'seedbed_id',
        'user_id',
        'seedbed_role',
        'joined_on',
        'left_on',
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
            'user_id' => 'integer',
            'joined_on' => 'date',
            'left_on' => 'date',
        ];
    }

    public function seedbed(): BelongsTo
    {
        return $this->belongsTo(Seedbed::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
