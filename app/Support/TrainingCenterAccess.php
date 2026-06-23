<?php

namespace App\Support;

use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

/**
 * Reglas de visibilidad de centros de formación según rol.
 * Super administrador: todos los centros activos.
 * Administrador de sistema (y rol legacy admin): solo el centro vinculado a su usuario.
 */
final class TrainingCenterAccess
{
    /**
     * Roles que representan cargos propios de cada centro (no globales).
     * Deben ir siempre con training_center_id y no mezclarse entre sedes.
     */
    public const CENTRO_BOUND_ROLE_NAMES = [
        'director_semilleros',
        'lider_semillero',
        'asesor_semillero',
        'director_investigacion',
        'investigador_asociado',
    ];

    public static function isSuperAdmin(?User $user): bool
    {
        return (bool) $user?->hasRole('super_administrador');
    }

    /**
     * Usuario autenticado no super con centro asignado: listados operativos (grupos, centros en índice, etc.)
     * deben limitarse a ese centro para no mezclar información entre sedes.
     */
    public static function scopedToTrainingCenter(?User $user): bool
    {
        return (bool) ($user
            && ! self::isSuperAdmin($user)
            && $user->training_center_id !== null);
    }

    public static function roleRequiresTrainingCenter(?string $roleName): bool
    {
        if ($roleName === null || $roleName === '') {
            return false;
        }

        return in_array($roleName, self::CENTRO_BOUND_ROLE_NAMES, true);
    }

    /**
     * Valida que un usuario pueda tener un rol ligado a sede (centro asignado y mismo centro que quien asigna).
     *
     * @param  string  $messageKey  Clave del error en el request (p. ej. "rol").
     *
     * @throws ValidationException
     */
    public static function validateCentroBoundRoleAssignment(
        User $targetUser,
        string $roleName,
        ?User $actor = null,
        string $messageKey = 'rol'
    ): void {
        if (! self::roleRequiresTrainingCenter($roleName)) {
            return;
        }

        if ($targetUser->training_center_id === null) {
            throw ValidationException::withMessages([
                $messageKey => 'Este rol exige que el usuario tenga un centro de formación asignado.',
            ]);
        }

        if ($actor
            && ! self::isSuperAdmin($actor)
            && self::scopedToTrainingCenter($actor)
            && (int) $targetUser->training_center_id !== (int) $actor->training_center_id) {
            throw ValidationException::withMessages([
                'user_id' => 'Solo puedes gestionar usuarios de tu mismo centro de formación.',
            ]);
        }
    }

    /**
     * Listados globales (usuarios, etc.): solo el super administrador ve todas las sedes.
     * El resto (admin de centro, director de investigación, etc.) queda acotado a su training_center_id.
     */
    public static function restrictUsersToTrainingCenter(?User $user): bool
    {
        if (! $user || self::isSuperAdmin($user)) {
            return false;
        }

        return true;
    }

    /** Restringe una consulta de usuarios al centro del usuario autenticado (salvo super). */
    public static function scopeUserQueryForList(Builder $query, ?User $user): Builder
    {
        if (! self::restrictUsersToTrainingCenter($user)) {
            return $query;
        }

        // Los super administradores nunca son visibles fuera de su propio dashboard.
        $query = $query->whereDoesntHave('roles', fn (Builder $q) => $q->where('name', 'super_administrador'));

        if ($user->training_center_id) {
            return $query->where('training_center_id', $user->training_center_id);
        }

        return $query->whereRaw('0 = 1');
    }

    /** Admin de un solo centro (no super). */
    public static function isCentroAdmin(?User $user): bool
    {
        if (! $user || self::isSuperAdmin($user)) {
            return false;
        }

        return $user->hasAnyRole(['administrador_sistema', 'admin']);
    }

    /**
     * Centros para selects (crear/editar usuario, grupos, etc.).
     *
     * @return Collection<int, TrainingCenter>
     */
    public static function centersForSelect(?User $user): Collection
    {
        if (! $user) {
            return collect();
        }

        if (self::isSuperAdmin($user)) {
            return TrainingCenter::activos()->orderBy('nombre')->get();
        }

        if (self::scopedToTrainingCenter($user)) {
            return TrainingCenter::query()
                ->where('id', $user->training_center_id)
                ->orderBy('nombre')
                ->get();
        }

        return TrainingCenter::activos()->orderBy('nombre')->get();
    }

    /**
     * IDs permitidos al guardar training_center_id; null = cualquiera (super).
     *
     * @return list<int>|null
     */
    public static function allowedCenterIdsForSave(?User $user): ?array
    {
        if (! $user || self::isSuperAdmin($user)) {
            return null;
        }

        if (self::scopedToTrainingCenter($user)) {
            return [(int) $user->training_center_id];
        }

        return null;
    }

    /**
     * Roles que puede asignar quien crea/edita usuarios.
     * Solo el super administrador puede asignar super_administrador y administrador_sistema.
     * Si $includeAdministradorSistemaForSelf, se añade ese rol (perfil propio del admin de centro).
     *
     * @return Collection<int, Role>
     */
    public static function rolesForUserForm(?User $auth, bool $includeAdministradorSistemaForSelf = false): Collection
    {
        $query = Role::query()->orderBy('name');

        if ($auth?->hasRole('super_administrador')) {
            return $query->get();
        }

        $roles = $query
            ->whereNotIn('name', ['super_administrador', 'administrador_sistema'])
            ->get();

        if ($includeAdministradorSistemaForSelf) {
            $adminRol = Role::where('name', 'administrador_sistema')->where('guard_name', 'web')->first();
            if ($adminRol) {
                $roles = $roles->push($adminRol)->unique('id')->sortBy('name')->values();
            }
        }

        return $roles;
    }
}
