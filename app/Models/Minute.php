<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Minute extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'seedbed_id',
        'document_name',
        'file_path',
        'description',
        'document_date',
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
            'document_date' => 'date',
        ];
    }

    public function seedbed(): BelongsTo
    {
        return $this->belongsTo(Seedbed::class);
    }
}
