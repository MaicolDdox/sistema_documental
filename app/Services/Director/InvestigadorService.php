<?php

namespace App\Services\Director;

use App\Enums\EstadoEnum;
use App\Enums\RolGrupoEnum;
use App\Models\Person;
use App\Models\ResearchGroupUser;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class InvestigadorService
{
    /**
     * Crea un usuario investigador y lo vincula al grupo del director.
     * La contraseña es asignada por el director y entregada personalmente al investigador.
     *
     * @param  array{
     *     training_center_id: int,
     *     email: string,
     *     password: string,
     *     tipo_documento: string,
     *     numero_documento: string|int,
     *     primer_nombre: string,
     *     segundo_nombre: ?string,
     *     primer_apellido: string,
     *     segundo_apellido: ?string,
     *     cvlac_link: ?string,
     * } $data
     */
    public function crearInvestigador(array $data, int $grupoId): User
    {
        return DB::transaction(function () use ($data, $grupoId) {
            // 1. Crear usuario con la contraseña asignada directamente por el director
            $user = User::create([
                'training_center_id' => $data['training_center_id'],
                'email'              => $data['email'],
                'tipo_documento'     => $data['tipo_documento'],
                'numero_documento'   => $data['numero_documento'],
                'password'           => Hash::make($data['password']),
                'estado'             => EstadoEnum::Activo,
            ]);

            // 2. Asignar rol Spatie
            $user->assignRole('investigador_asociado');
            $user->forceFill(['primary_role_name' => 'investigador_asociado'])->saveQuietly();

            // 3. Crear perfil Person
            Person::create([
                'user_id'          => $user->id,
                'primer_nombre'    => $data['primer_nombre'],
                'segundo_nombre'   => $data['segundo_nombre'] ?? null,
                'primer_apellido'  => $data['primer_apellido'],
                'segundo_apellido' => $data['segundo_apellido'] ?? null,
                'cvlac_link'       => $data['cvlac_link'] ?? null,
            ]);

            // 4. Vincular al grupo en research_group_users
            ResearchGroupUser::create([
                'research_group_id' => $grupoId,
                'user_id'           => $user->id,
                'rol'               => RolGrupoEnum::InvestigadorAsociado,
            ]);

            return $user;
        });
    }

    /**
     * Cambia el rol interno del investigador dentro del grupo.
     */
    public function cambiarRol(int $userId, int $grupoId, RolGrupoEnum $nuevoRol): void
    {
        ResearchGroupUser::where('research_group_id', $grupoId)
            ->where('user_id', $userId)
            ->update(['rol' => $nuevoRol]);
    }

    /**
     * Desvincula un investigador del grupo del director (no lo elimina del sistema).
     */
    public function desvincular(int $userId, int $grupoId): void
    {
        ResearchGroupUser::where('research_group_id', $grupoId)
            ->where('user_id', $userId)
            ->whereNot('rol', RolGrupoEnum::Director)
            ->delete();
    }
}
