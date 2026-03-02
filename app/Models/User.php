<?php

namespace App\Models;

use App\Enums\EstadoEnum;
use App\Enums\TipoDocumentoEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasRoles;

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'training_center_id',
        'email',
        'tipo_documento',
        'numero_documento',
        'password',
        'estado',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'tipo_documento' => TipoDocumentoEnum::class,
            'estado' => EstadoEnum::class,
        ];
    }

    // ─────────────────────────────────────────────
    // RELACIONES
    // ─────────────────────────────────────────────

    // BelongsTo
    public function trainingCenter(): BelongsTo
    {
        return $this->belongsTo(TrainingCenter::class, 'training_center_id');
    }

    // HasOne
    public function person(): HasOne
    {
        return $this->hasOne(Person::class, 'user_id');
    }

    // HasMany
    public function externalAdvisors(): HasMany
    {
        return $this->hasMany(ExternalAdvisor::class, 'user_id');
    }

    public function createdSeedlings(): HasMany
    {
        return $this->hasMany(Seedling::class, 'creator_id');
    }

    public function ledSeedlings(): HasMany
    {
        return $this->hasMany(Seedling::class, 'leader_id');
    }

    public function createdProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'project_creator_id');
    }

    public function projectAuthors(): HasMany
    {
        return $this->hasMany(ProjectAuthor::class, 'user_id');
    }

    public function groupProducts(): HasMany
    {
        return $this->hasMany(GroupProduct::class, 'author_id');
    }

    // BelongsToMany
    public function researchGroups(): BelongsToMany
    {
        return $this->belongsToMany(
            ResearchGroup::class,
            'research_group_users',
            'user_id',
            'research_group_id'
        )->withPivot('rol')->withTimestamps();
    }

    public function seedlings(): BelongsToMany
    {
        return $this->belongsToMany(
            Seedling::class,
            'seedling_members',
            'user_id',
            'seedling_id'
        )->withTimestamps();
    }

    // ─────────────────────────────────────────────
    // SCOPES
    // ─────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('estado', EstadoEnum::Activo);
    }

    // ─────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────

    /**
     * Get the user's initials from the person profile.
     */
    public function initials(): string
    {
        if ($this->person) {
            return Str::upper(
                Str::substr($this->person->primer_nombre, 0, 1) .
                Str::substr($this->person->primer_apellido, 0, 1)
            );
        }

        return Str::of($this->email)
            ->before('@')
            ->substr(0, 2)
            ->upper();
    }
}
