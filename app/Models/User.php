<?php

namespace App\Models;

use App\Enums\EstadoEnum;
use App\Enums\TipoDocumentoEnum;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable, TwoFactorAuthenticatable;

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'training_center_id',
        'created_by_user_id',
        'primary_role_name',
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

    /** Usuario que creó esta cuenta (regla de edición uno-a-uno). */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
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

    // BelongsToMany
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
     * Dirección de email para notificaciones (reset password, etc.)
     * Usa siempre users.email para que el hash de verificación de Fortify coincida.
     */
    public function routeNotificationForMail($notification = null): string|array
    {
        return $this->email;
    }

    /**
     * Envía la notificación de verificación de email con manejo de errores.
     */
    public function sendEmailVerificationNotification(): void
    {
        try {
            $this->notify(new VerifyEmail);
        } catch (\Throwable $e) {
            Log::error('User: fallo al enviar email de verificación', [
                'user_id' => $this->id,
                'email' => $this->email,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Nombre para mostrar: primer nombre + primer apellido, con fallback al email.
     */
    public function getNameAttribute(): string
    {
        if ($this->person) {
            $nombre = trim(($this->person->primer_nombre ?? '').' '.($this->person->primer_apellido ?? ''));
            if ($nombre !== '') {
                return $nombre;
            }
        }

        return $this->email ?? 'Usuario';
    }

    /**
     * Get the user's initials from the person profile.
     */
    public function initials(): string
    {
        if ($this->person) {
            return Str::upper(
                Str::substr($this->person->primer_nombre, 0, 1).
                Str::substr($this->person->primer_apellido, 0, 1)
            );
        }

        return Str::of($this->email)
            ->before('@')
            ->substr(0, 2)
            ->upper();
    }
}
