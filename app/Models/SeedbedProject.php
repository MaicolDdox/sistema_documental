<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeedbedProject extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'seedbed_id',
        'advisor_id',
        'research_line_id',
        'technology_line_id',
        'thematic_area_id',
        'project_modality_id',
        'research_type_id',
        'start_date',
        'end_date',
        'status',
        'linked_to_macro_project',
        'macro_project_code',
        'macro_project_name',
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
            'advisor_id' => 'integer',
            'research_line_id' => 'integer',
            'technology_line_id' => 'integer',
            'thematic_area_id' => 'integer',
            'project_modality_id' => 'integer',
            'research_type_id' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
            'linked_to_macro_project' => 'boolean',
        ];
    }

    public function seedbed(): BelongsTo
    {
        return $this->belongsTo(Seedbed::class);
    }

    public function advisor(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function researchLine(): BelongsTo
    {
        return $this->belongsTo(ResearchLine::class);
    }

    public function technologyLine(): BelongsTo
    {
        return $this->belongsTo(TechnologyLine::class);
    }

    public function thematicArea(): BelongsTo
    {
        return $this->belongsTo(ThematicArea::class);
    }

    public function projectModality(): BelongsTo
    {
        return $this->belongsTo(ProjectModality::class);
    }

    public function researchType(): BelongsTo
    {
        return $this->belongsTo(ResearchType::class);
    }
}
