<?php

namespace App\Services\Director;

use App\Enums\EstadoEnum;
use App\Enums\RolGrupoEnum;
use App\Models\Person;
use App\Models\ResearchGroupUser;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InvestigadorService
{
    /**
     * Crea un usuario investigador, lo vincula al grupo y le envía
     * sus credenciales temporales por correo.
     *
     * @param  array{
     *     training_center_id: int,
     *     email: string,
     *     tipo_documento: string,
     *     numero_documento: string|int,
     *     primer_nombre: string,
     *     segundo_nombre: ?string,
     *     primer_apellido: string,
     *     segundo_apellido: ?string,
     * } $data
     */
    public function crearInvestigador(array $data, int $grupoId): User
    {
        return DB::transaction(function () use ($data, $grupoId) {
            $passwordTemporal = Str::password(12, symbols: false);

            // 1. Crear usuario
            $user = User::create([
                'training_center_id' => $data['training_center_id'],
                'email'              => $data['email'],
                'tipo_documento'     => $data['tipo_documento'],
                'numero_documento'   => $data['numero_documento'],
                'password'           => Hash::make($passwordTemporal),
                'estado'             => EstadoEnum::Activo,
            ]);

            // 2. Asignar rol Spatie
            $user->assignRole('investigador_asociado');

            // 3. Crear perfil Person
            Person::create([
                'user_id'         => $user->id,
                'primer_nombre'   => $data['primer_nombre'],
                'segundo_nombre'  => $data['segundo_nombre'] ?? null,
                'primer_apellido' => $data['primer_apellido'],
                'segundo_apellido'=> $data['segundo_apellido'] ?? null,
            ]);

            // 4. Vincular al grupo en research_group_users
            ResearchGroupUser::create([
                'research_group_id' => $grupoId,
                'user_id'           => $user->id,
                'rol'               => RolGrupoEnum::InvestigadorAsociado,
            ]);

            // 5. Notificar por correo (Log driver en desarrollo)
            $this->enviarCredenciales($user, $passwordTemporal);

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
            ->whereNot('rol', RolGrupoEnum::Director) // seguridad: no puede desvincularse a sí mismo
            ->delete();
    }

    /**
     * Envía las credenciales temporales al investigador.
     * En producción usa el driver SMTP configurado en .env.
     */
    private function enviarCredenciales(User $user, string $password): void
    {
        // Se usa Mail::raw para no requerir un Mailable dedicado en esta etapa.
        // TODO: reemplazar por un Mailable tipado cuando se diseñe la plantilla.
        Mail::raw(
            "Bienvenido al sistema GIDESTH.\n\nTus credenciales de acceso:\n\nCorreo: {$user->email}\nContraseña temporal: {$password}\n\nPor favor cambia tu contraseña al ingresar por primera vez.",
            fn($msg) => $msg->to($user->email)->subject('Credenciales de acceso — GIDESTH')
        );
    }
}
