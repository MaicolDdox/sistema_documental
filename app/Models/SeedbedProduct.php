<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeedbedProduct extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'seedbed_project_id',
        'name',
        'product_type',
        'description',
        'file_path',
        'obtained_on',
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
            'seedbed_project_id' => 'integer',
            'obtained_on' => 'date',
        ];
    }

    public function seedbedProject(): BelongsTo
    {
        return $this->belongsTo(SeedbedProject::class);
    }
}
