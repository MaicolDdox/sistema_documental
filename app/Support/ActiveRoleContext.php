<?php

namespace App\Support;

use App\Models\User;

/**
 * Rol activo del usuario autenticado (feature multi-rol, FEAT-20260830-001).
 * Vive en sesión, NO en base de datos: se reinicia al rol principal en cada
 * login. Distinto de "rol principal" (App\Support\RoleModuleLinks), que es
 * una preferencia persistida — el rol activo es solo el contexto de
 * navegación actual dentro de la sesión.
 */
final class ActiveRoleContext
{
    private const SESSION_KEY = 'rol_activo';

    /**
     * Nombre del rol activo del usuario autenticado. Si no hay ninguno
     * válido en sesión (primer acceso tras login, o el rol guardado ya no
     * está asignado al usuario), se inicializa con el rol principal.
     */
    public static function current(): ?string
    {
        $user = auth()->user();
        if (! $user) {
            return null;
        }

        $user->loadMissing('roles');
        $assigned = $user->roles->pluck('name')->all();

        $stored = session(self::SESSION_KEY);
        if (is_string($stored) && $stored !== '' && in_array($stored, $assigned, true)) {
            return $stored;
        }

        $primary = RoleModuleLinks::primaryRoleNameForUser($user);
        if ($primary !== null) {
            session([self::SESSION_KEY => $primary]);
        }

        return $primary;
    }

    /**
     * Cambia el rol activo del usuario dado. Devuelve false (sin cambiar
     * nada) si el usuario no tiene ese rol asignado.
     */
    public static function switchTo(User $user, string $roleName): bool
    {
        $user->loadMissing('roles');
        if (! $user->hasRole($roleName)) {
            return false;
        }

        session([self::SESSION_KEY => $roleName]);

        return true;
    }

    /** Inicializa el rol activo al rol principal. Se llama al hacer login. */
    public static function initializeForUser(User $user): void
    {
        $user->loadMissing('roles');
        $primary = RoleModuleLinks::primaryRoleNameForUser($user);

        if ($primary !== null) {
            session([self::SESSION_KEY => $primary]);
        } else {
            session()->forget(self::SESSION_KEY);
        }
    }

    public static function isPrimary(User $user): bool
    {
        $current = self::current();
        $primary = RoleModuleLinks::primaryRoleNameForUser($user);

        return $current !== null && $current === $primary;
    }

    public static function reset(): void
    {
        session()->forget(self::SESSION_KEY);
    }
}
