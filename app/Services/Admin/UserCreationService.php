<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Enums\EstadoEnum;
use App\Models\Person;
use App\Models\User;
use App\Support\TrainingCenterAccess;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

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
     *     additional_roles?: list<string>,
     *     estado?: EstadoEnum,
     *     genero?: string,
     *     celular?: int|string,
     *     eps?: string,
     *     created_by_user_id?: ?int,
     * } $data
     */
    public function crearUsuario(array $data, ?int $trainingCenterId): User
    {
        $additionalRoles = $data['additional_roles'] ?? [];

        // BUG-20260813-031/038 — punto único de creación de usuarios: si el
        // rol exige centro (CENTRO_BOUND_ROLE_NAMES) y no llegó ninguno, se
        // corta acá. Evita que un llamador se olvide de chequearlo (ya pasó
        // con Admin/UsuarioController creando director_semilleros con centro
        // null cuando el administrador_sistema que lo creaba tampoco tenía).
        // FEAT-20260830-001: el chequeo también cubre los roles adicionales
        // (multi-rol) — un solo training_center_id sirve para todos los
        // roles del usuario, así que si CUALQUIERA de ellos lo exige, sigue
        // siendo obligatorio.
        $algunRolExigeCentro = TrainingCenterAccess::roleRequiresTrainingCenter($data['rol'] ?? null)
            || collect($additionalRoles)->contains(fn (string $r) => TrainingCenterAccess::roleRequiresTrainingCenter($r));

        if ($algunRolExigeCentro && $trainingCenterId === null) {
            throw ValidationException::withMessages([
                'training_center_id' => 'Este rol exige un centro de formación.',
            ]);
        }

        return DB::transaction(function () use ($data, $trainingCenterId, $additionalRoles) {
            $user = User::create([
                'training_center_id' => $trainingCenterId,
                'created_by_user_id' => $data['created_by_user_id'] ?? null,
                'email' => $data['email'],
                'numero_documento' => $data['numero_documento'],
                'tipo_documento' => $data['tipo_documento'],
                'password' => Hash::make($data['password']),
                'estado' => $data['estado'] ?? EstadoEnum::Activo,
            ]);

            Person::create([
                'user_id' => $user->id,
                'primer_nombre' => $data['primer_nombre'],
                'segundo_nombre' => $data['segundo_nombre'] ?? null,
                'primer_apellido' => $data['primer_apellido'],
                'segundo_apellido' => $data['segundo_apellido'] ?? null,
                'email_institucional' => $data['email'],
                'genero' => $data['genero'] ?? 'prefiero no decirlo',
                'celular' => $data['celular'] ?? 0,
                'eps' => $data['eps'] ?? '',
            ]);

            if (! empty($data['rol'])) {
                $user->assignRole($data['rol']);
                $user->forceFill(['primary_role_name' => $data['rol']])->saveQuietly();
            }

            // FEAT-20260830-001: roles adicionales (multi-rol), asignados
            // desde el mismo formulario de creación. El caller es quien ya
            // filtró esta lista contra el universo permitido (nunca
            // super_administrador, nunca el rol principal) — ver
            // RoleAssignmentMatrix::additionalRoleOptionNamesFor().
            foreach ($additionalRoles as $roleName) {
                if ($roleName !== ($data['rol'] ?? null)) {
                    $user->assignRole($roleName);
                }
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
