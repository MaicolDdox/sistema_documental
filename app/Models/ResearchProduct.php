<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResearchProduct extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'investigator_id',
        'research_group_id',
        'project_origin_type',
        'origin_project_code',
        'minciencias_typology_id',
        'product_type_id',
        'name',
        'description',
        'publication_year',
        'training_program_name',
        'has_repository',
        'repository_url',
        'review_status',
        'submitted_at',
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
            'investigator_id' => 'integer',
            'research_group_id' => 'integer',
            'minciencias_typology_id' => 'integer',
            'product_type_id' => 'integer',
            'has_repository' => 'boolean',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function investigator(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function researchGroup(): BelongsTo
    {
        return $this->belongsTo(ResearchGroup::class);
    }

    public function mincienciasTypology(): BelongsTo
    {
        return $this->belongsTo(MincienciasTypology::class);
    }

    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class);
    }
}
