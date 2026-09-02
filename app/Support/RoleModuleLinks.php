<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;

/**
 * Rol principal = primer rol según la misma prioridad que el login (Livewire/Login),
 * porque model_has_roles no tiene columna id ni orden de asignación fiable.
 * Enlaces al panel típico de cada rol.
 */
final class RoleModuleLinks
{
    /** Misma prioridad que App\Livewire\Auth\Login (redirección tras autenticación). */
    private const LOGIN_ROLE_PRIORITY = [
        'super_administrador',
        'administrador_sistema',
        'director_semilleros',
        'lider_semillero',
        'lider_proyecto',
        'co_investigador',
    ];

    /**
     * @param  list<string>  $roleNames
     */
    public static function pickPrimaryRoleNameFromNames(array $roleNames): ?string
    {
        $set = array_flip($roleNames);
        foreach (self::LOGIN_ROLE_PRIORITY as $name) {
            if (isset($set[$name])) {
                return $name;
            }
        }

        sort($roleNames);

        return $roleNames[0] ?? null;
    }

    public static function labelForRoleName(string $name): string
    {
        return match ($name) {
            'super_administrador' => 'Super administrador',
            'administrador_sistema' => 'Administración',
            'director_semilleros' => 'Director de Semilleros',
            'lider_semillero' => 'Líder de Semillero',
            'lider_proyecto' => 'Líder de Proyecto',
            'co_investigador' => 'Co-investigador',
            default => ucfirst(str_replace('_', ' ', $name)),
        };
    }

    /**
     * URL del módulo principal asociado al rol (para abrir en nueva pestaña o navegar).
     */
    public static function urlForRoleName(?string $name): ?string
    {
        if ($name === null) {
            return null;
        }

        return match ($name) {
            'super_administrador' => Route::has('super-admin.dashboard')
                ? route('super-admin.dashboard')
                : url('/super-admin/dashboard'),
            'administrador_sistema' => Route::has('admin.dashboard')
                ? route('admin.dashboard')
                : url('/admin/dashboard'),
            'director_semilleros' => Route::has('dir-sem.dashboard')
                ? route('dir-sem.dashboard')
                : url('/director-semilleros'),
            'lider_semillero' => Route::has('lider-sem.dashboard')
                ? route('lider-sem.dashboard')
                : url('/lider-semillero'),
            'lider_proyecto' => Route::has('lider-proyecto.dashboard')
                ? route('lider-proyecto.dashboard')
                : url('/lider-proyecto'),
            'co_investigador' => Route::has('co-investigador.dashboard')
                ? route('co-investigador.dashboard')
                : url('/co-investigador'),
            default => null,
        };
    }

    /**
     * URL del dashboard que corresponde al rol principal (misma prioridad que el login).
     */
    public static function dashboardUrlForUser(User $user): string
    {
        $user->loadMissing('roles');
        $primary = self::primaryRole($user);
        if ($primary === null) {
            return route('dashboard');
        }

        return self::urlForRoleName($primary->name) ?? route('dashboard');
    }

    /**
     * Nombre del rol principal: preferencia en users.primary_role_name si sigue asignado;
     * si no, misma prioridad que el login (LOGIN_ROLE_PRIORITY).
     */
    public static function primaryRoleNameForUser(User $user): ?string
    {
        $user->loadMissing('roles');
        $names = $user->roles->pluck('name')->all();
        if ($names === []) {
            return null;
        }
        $preferred = $user->primary_role_name;
        if (is_string($preferred) && $preferred !== '' && in_array($preferred, $names, true)) {
            return $preferred;
        }

        return self::pickPrimaryRoleNameFromNames($names);
    }

    /**
     * Antes de asignar un rol adicional, fija el principal actual si aún no estaba guardado,
     * para que el nuevo rol no lo sustituya solo por tener mayor prioridad automática.
     */
    public static function lockPrimaryRoleBeforeAddingRole(User $user): void
    {
        $user->loadMissing('roles');
        $names = $user->roles->pluck('name')->all();
        if ($names === []) {
            return;
        }
        if (filled($user->primary_role_name)) {
            return;
        }
        $user->forceFill([
            'primary_role_name' => self::pickPrimaryRoleNameFromNames($names),
        ])->saveQuietly();
    }

    /** Rol principal según preferencia guardada o prioridad de login (ver LOGIN_ROLE_PRIORITY). */
    public static function primaryRole(User $user): ?Role
    {
        $user->loadMissing('roles');
        if ($user->roles->isEmpty()) {
            return null;
        }

        $picked = self::primaryRoleNameForUser($user);

        return $picked ? $user->roles->firstWhere('name', $picked) : $user->roles->first();
    }

    /**
     * @return array<int, string|null> map user id => role name
     */
    public static function primaryRoleNamesByUserIds(array $userIds): array
    {
        if ($userIds === []) {
            return [];
        }

        $table = config('permission.table_names.model_has_roles');
        $modelType = (new User)->getMorphClass();

        $primaryOverrides = User::query()
            ->whereIn('id', $userIds)
            ->pluck('primary_role_name', 'id');

        $pivots = DB::table($table)
            ->where('model_type', $modelType)
            ->whereIn('model_id', $userIds)
            ->get(['model_id', 'role_id']);

        $roleIdsByUser = [];
        foreach ($pivots as $p) {
            $mid = (int) $p->model_id;
            if (! isset($roleIdsByUser[$mid])) {
                $roleIdsByUser[$mid] = [];
            }
            $roleIdsByUser[$mid][(int) $p->role_id] = true;
        }

        if ($roleIdsByUser === []) {
            return [];
        }

        $allRoleIds = [];
        foreach ($roleIdsByUser as $ids) {
            $allRoleIds = array_merge($allRoleIds, array_keys($ids));
        }
        $allRoleIds = array_unique($allRoleIds);
        $namesByRoleId = Role::whereIn('id', $allRoleIds)->pluck('name', 'id');

        $out = [];
        foreach ($roleIdsByUser as $uid => $idSet) {
            $names = [];
            foreach (array_keys($idSet) as $rid) {
                if (isset($namesByRoleId[$rid])) {
                    $names[] = $namesByRoleId[$rid];
                }
            }
            $uid = (int) $uid;
            $override = $primaryOverrides[$uid] ?? null;
            if (is_string($override) && $override !== '' && in_array($override, $names, true)) {
                $out[$uid] = $override;
            } else {
                $out[$uid] = self::pickPrimaryRoleNameFromNames($names);
            }
        }

        return $out;
    }

    /**
     * @return list<array{role: string, label: string, url: string|null, is_primary: bool}>
     */
    public static function moduleLinksWithPrimary(User $user): array
    {
        $primary = self::primaryRole($user);
        $primaryName = $primary?->name;

        $links = [];
        foreach ($user->roles as $role) {
            $name = $role->name;
            $links[] = [
                'role' => $name,
                'label' => self::labelForRoleName($name),
                'url' => self::urlForRoleName($name),
                'is_primary' => $primaryName !== null && $name === $primaryName,
            ];
        }

        usort($links, fn ($a, $b) => ($b['is_primary'] <=> $a['is_primary']) ?: strcmp($a['label'], $b['label']));

        return $links;
    }
}
