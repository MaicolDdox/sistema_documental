<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Apprentice extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'full_name',
        'document_type',
        'document_number',
        'gender',
        'birth_date',
        'phone',
        'mobile',
        'email',
        'blood_type',
        'health_provider',
        'training_cohort_id',
        'support_type',
        'seedbed_id',
        'seedbed_project_id',
        'project_file_path',
        'status',
        'admission_date',
        'withdrawal_date',
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
            'birth_date' => 'date',
            'training_cohort_id' => 'integer',
            'seedbed_id' => 'integer',
            'seedbed_project_id' => 'integer',
            'admission_date' => 'date',
            'withdrawal_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trainingCohort(): BelongsTo
    {
        return $this->belongsTo(TrainingCohort::class);
    }

    public function seedbed(): BelongsTo
    {
        return $this->belongsTo(Seedbed::class);
    }

    public function seedbedProject(): BelongsTo
    {
        return $this->belongsTo(SeedbedProject::class);
    }
}
