<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Spatie\Permission\Models\Role;

/**
 * Un centro de formación solo puede tener un administrador del sistema vinculado;
 * la vinculación explícita se hace en «Centro ↔ administrador» (o con cuidado al editar usuario).
 */
final class SystemAdminCenterLink
{
    public const ROLE_NAMES = ['administrador_sistema', 'admin'];

    /**
     * @return array{0: list<int>, 1: list<string>}
     */
    public static function webRoleIdsAndNames(): array
    {
        $roles = Role::query()
            ->where('guard_name', 'web')
            ->whereIn('name', self::ROLE_NAMES)
            ->get(['id', 'name']);

        return [$roles->pluck('id')->all(), $roles->pluck('name')->all()];
    }

    /**
     * @param  Builder<User>|Relation  $query  Relación (p. ej. HasMany en with()/whereDoesntHave) o query directa sobre User
     * @param  list<int>|null  $roleIds
     * @param  list<string>|null  $roleNames
     * @return Builder<User>|Relation
     */
    public static function applyAdminSistemaNoSuperScope(Builder|Relation $query, ?array $roleIds = null, ?array $roleNames = null): Builder|Relation
    {
        if ($roleIds === null || $roleNames === null) {
            [$roleIds, $roleNames] = self::webRoleIdsAndNames();
        }

        if ($roleIds === [] || $roleNames === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query
            ->where(function (Builder $q) use ($roleIds, $roleNames) {
                $q->whereHas('roles', fn (Builder $r) => $r->whereIn((new Role)->getTable().'.id', $roleIds))
                    ->orWhereIn('primary_role_name', $roleNames);
            })
            ->whereDoesntHave('roles', fn (Builder $r) => $r->where('name', 'super_administrador')->where('guard_name', 'web'))
            ->where(fn (Builder $q) => $q->whereNull('primary_role_name')
                ->orWhere('primary_role_name', '!=', 'super_administrador'));
    }

    public static function trainingCenterHasSystemAdmin(int $trainingCenterId, ?int $exceptUserId = null): bool
    {
        [$roleIds, $roleNames] = self::webRoleIdsAndNames();

        $q = User::query()->where('training_center_id', $trainingCenterId);
        if ($exceptUserId !== null) {
            $q->where('users.id', '!=', $exceptUserId);
        }

        return self::applyAdminSistemaNoSuperScope($q, $roleIds, $roleNames)->exists();
    }

    public static function roleNameIsSystemAdministrator(string $roleName): bool
    {
        return in_array($roleName, self::ROLE_NAMES, true);
    }
}
