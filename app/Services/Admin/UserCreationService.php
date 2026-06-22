<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Enums\EstadoEnum;
use App\Models\Person;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserCreationService
{
    /**
     * Crea un usuario con su perfil Person y opcionalmente le asigna un rol.
     * Todo en una transacción atómica.
     *
     * @param array{
     *     email: string,
     *     numero_documento: string|int,
     *     tipo_documento: string,
     *     password: string,
     *     primer_nombre: string,
     *     primer_apellido: string,
     *     segundo_nombre?: ?string,
     *     segundo_apellido?: ?string,
     *     rol?: ?string,
     *     estado?: EstadoEnum,
     *     genero?: string,
     *     celular?: int|string,
     *     eps?: string,
     * } $data
     */
    public function crearUsuario(array $data, ?int $trainingCenterId): User
    {
        return DB::transaction(function () use ($data, $trainingCenterId) {
            $user = User::create([
                'training_center_id' => $trainingCenterId,
                'email'              => $data['email'],
                'numero_documento'   => $data['numero_documento'],
                'tipo_documento'     => $data['tipo_documento'],
                'password'           => Hash::make($data['password']),
                'estado'             => $data['estado'] ?? EstadoEnum::Activo,
            ]);

            Person::create([
                'user_id'             => $user->id,
                'primer_nombre'       => $data['primer_nombre'],
                'segundo_nombre'      => $data['segundo_nombre'] ?? null,
                'primer_apellido'     => $data['primer_apellido'],
                'segundo_apellido'    => $data['segundo_apellido'] ?? null,
                'email_institucional' => $data['email'],
                'genero'              => $data['genero'] ?? 'prefiero no decirlo',
                'celular'             => $data['celular'] ?? 0,
                'eps'                 => $data['eps'] ?? '',
            ]);

            if (! empty($data['rol'])) {
                $user->assignRole($data['rol']);
                $user->forceFill(['primary_role_name' => $data['rol']])->saveQuietly();
            }

            return $user;
        });
    }

    /**
     * Separa un nombre o apellido compuesto en primer y segundo componente.
     * "Juan Carlos" → ["Juan", "Carlos"], "Pérez" → ["Pérez", ""]
     */
    public function splitNombre(string $valor): array
    {
        $valor = trim(preg_replace('/\s+/', ' ', $valor));
        if ($valor === '') {
            return ['', ''];
        }
        $partes = explode(' ', $valor, 2);

        return [$partes[0], $partes[1] ?? ''];
    }
}
