<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\TrainingCenter;
use App\Models\ResearchGroup;
use App\Models\ResearchGroupUser;
use App\Models\Seedling;
use App\Models\ExternalAdvisor;
use App\Models\SeedlingAdvisor;
use App\Enums\RolGrupoEnum;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $centroAgroindustrial = TrainingCenter::where('codigo', 9116)->firstOrFail();
        $centroIndustria = TrainingCenter::where('codigo', 9527)->firstOrFail();

        $users = [
            [
                'training_center_id' => $centroAgroindustrial->id,
                'email' => 'ydmoreno@sena.edu.co',
                'tipo_documento' => 'cedula ciudadana',
                'numero_documento' => 34327134,
                'password' => Hash::make('Password123!'),
                'estado' => 'activo',
            ],
            [
                'training_center_id' => $centroIndustria->id,
                'email' => 'jovalenciap@sena.edu.co',
                'tipo_documento' => 'cedula ciudadana',
                'numero_documento' => 10304952,
                'password' => Hash::make('Password123!'),
                'estado' => 'activo',
            ],
            [
                'training_center_id' => $centroAgroindustrial->id,
                'email' => 'directorsem@sena.edu.co',
                'tipo_documento' => 'cedula ciudadana',
                'numero_documento' => 1076504087,
                'password' => Hash::make('Password123!'),
                'estado' => 'activo',
            ],
            [
                // Director semilleros del Centro de Formación Agroindustrial (Campoalegre / sede distinta a Industria-Neiva)
                'training_center_id' => $centroAgroindustrial->id,
                'email' => 'dirsemillero@sena.edu.co',
                'tipo_documento' => 'cedula ciudadana',
                'numero_documento' => 52345678,
                'password' => Hash::make('Password123!'),
                'estado' => 'activo',
            ],
            [
                'training_center_id' => $centroAgroindustrial->id,
                'email' => 'lidersem@sena.edu.co',
                'tipo_documento' => 'cedula ciudadana',
                'numero_documento' => 87654321,
                'password' => Hash::make('Password123!'),
                'estado' => 'activo',
            ],
            [
                'training_center_id' => $centroAgroindustrial->id,
                'email' => 'asesorsem@sena.edu.co',
                'tipo_documento' => 'cedula ciudadana',
                'numero_documento' => 55114455,
                'password' => Hash::make('Password123!'),
                'estado' => 'activo',
            ],
            // Directores de Grupo de Investigación (datos de prueba)
            [
                // Director de investigación del grupo GIDESTH Industria (centro Neiva / 9527)
                'training_center_id' => $centroIndustria->id,
                'email' => 'dirgrupo1@sena.edu.co',
                'tipo_documento' => 'cedula ciudadana',
                'numero_documento' => 11111111,
                'password' => Hash::make('Password123!'),
                'estado' => 'activo',
            ],
            [
                // Director de investigación del grupo GIDESTH Agroindustrial (centro Campoalegre / 9116)
                'training_center_id' => $centroAgroindustrial->id,
                'email' => 'dirgrupo2@sena.edu.co',
                'tipo_documento' => 'cedula ciudadana',
                'numero_documento' => 22222222,
                'password' => Hash::make('Password123!'),
                'estado' => 'activo',
            ],
            // Investigador Asociado (datos de prueba)
            [
                'training_center_id' => $centroAgroindustrial->id,
                'email' => 'investigador@sena.edu.co',
                'tipo_documento' => 'cedula ciudadana',
                'numero_documento' => 33333333,
                'password' => Hash::make('Password123!'),
                'estado' => 'activo',
            ],
            // Super administrador (vista global / todos los permisos web)
            [
                'training_center_id' => $centroAgroindustrial->id,
                'email' => 'superadmin@sena.edu.co',
                'tipo_documento' => 'cedula ciudadana',
                'numero_documento' => 900000001,
                'password' => Hash::make('Password123!'),
                'estado' => 'activo',
            ],
        ];

        $rolesByEmail = [
            'ydmoreno@sena.edu.co' => 'administrador_sistema',
            'jovalenciap@sena.edu.co' => 'administrador_sistema',
            'directorsem@sena.edu.co' => 'director_semilleros',
            'dirsemillero@sena.edu.co' => 'director_semilleros',
            'lidersem@sena.edu.co' => 'lider_semillero',
            'asesorsem@sena.edu.co' => 'asesor_semillero',
            'dirgrupo1@sena.edu.co' => 'director_investigacion',
            'dirgrupo2@sena.edu.co' => 'director_investigacion',
            'investigador@sena.edu.co' => 'investigador_asociado',
            'superadmin@sena.edu.co' => 'super_administrador',
        ];

        foreach ($users as $userData) {
            $user = User::firstOrCreate(
                ['numero_documento' => $userData['numero_documento']],
                $userData
            );

            if (isset($rolesByEmail[$user->email])) {
                $rol = $rolesByEmail[$user->email];
                if ($rol === 'super_administrador') {
                    $user->syncRoles(['super_administrador']);
                } else {
                    $user->assignRole($rol);
                }
            }
        }

        // firstOrCreate no actualiza sede si el usuario ya existía: alinear centros de prueba
        User::where('email', 'dirsemillero@sena.edu.co')->update(['training_center_id' => $centroAgroindustrial->id]);
        User::where('email', 'dirgrupo1@sena.edu.co')->update(['training_center_id' => $centroIndustria->id]);
        User::where('email', 'dirgrupo2@sena.edu.co')->update(['training_center_id' => $centroAgroindustrial->id]);

        // Vincular directores de investigación a sus grupos en research_group_users
        // Se busca por training_center_id para garantizar que cada director
        // quede vinculado al grupo de SU propio centro (lógica multiplatforma).
        $dirGrupo1 = User::where('email', 'dirgrupo1@sena.edu.co')->first();
        $dirGrupo2 = User::where('email', 'dirgrupo2@sena.edu.co')->first();

        $grupoAgroindustrial = ResearchGroup::where('training_center_id', $centroAgroindustrial->id)->first();
        $grupoIndustria = ResearchGroup::where('training_center_id', $centroIndustria->id)->first();

        // dirgrupo1 → grupo del centro Industria (9527); dirgrupo2 → grupo Agroindustrial (9116)
        if ($dirGrupo1 && $grupoIndustria) {
            ResearchGroupUser::where('user_id', $dirGrupo1->id)->delete();
            ResearchGroupUser::firstOrCreate(
                ['research_group_id' => $grupoIndustria->id, 'user_id' => $dirGrupo1->id],
                ['rol' => RolGrupoEnum::Director]
            );
        }

        if ($dirGrupo2 && $grupoAgroindustrial) {
            ResearchGroupUser::where('user_id', $dirGrupo2->id)->delete();
            ResearchGroupUser::firstOrCreate(
                ['research_group_id' => $grupoAgroindustrial->id, 'user_id' => $dirGrupo2->id],
                ['rol' => RolGrupoEnum::Director]
            );
        }

        // Vincular investigador asociado de prueba al grupo del centro agroindustrial
        $investigador = User::where('email', 'investigador@sena.edu.co')->first();
        if ($investigador && $grupoAgroindustrial) {
            ResearchGroupUser::firstOrCreate(
                ['research_group_id' => $grupoAgroindustrial->id, 'user_id' => $investigador->id],
                ['rol' => RolGrupoEnum::InvestigadorAsociado]
            );
        }

        // Vincular asesor de semillero al único semillero existente
        $asesorSem = User::where('email', 'asesorsem@sena.edu.co')->first();
        $semilleroUnico = Seedling::first();
        if ($asesorSem && $semilleroUnico) {
            // Crear (o reutilizar) registro de asesor externo ligado a este usuario
            $external = ExternalAdvisor::firstOrCreate(
                ['user_id' => $asesorSem->id],
                [
                    'nombre_completo' => $asesorSem->person?->primer_nombre
                        ? trim($asesorSem->person->primer_nombre . ' ' . $asesorSem->person->primer_apellido)
                        : $asesorSem->email,
                    'email'           => $asesorSem->email,
                    'telefono'        => '',
                    'institucion'     => 'SENA',
                ]
            );

            SeedlingAdvisor::firstOrCreate(
                [
                    'seedling_id'        => $semilleroUnico->id,
                    'external_advisor_id'=> $external->id,
                ],
                ['activo' => true]
            );
        }

        $this->command->info('✅ Usuarios creados:');
        $this->command->info('   ydmoreno@sena.edu.co       → Password123! (administrador)');
        $this->command->info('   jovalenciap@sena.edu.co    → Password123! (administrador)');
        $this->command->info('   directorsem@sena.edu.co    → Password123! (director semilleros)');
        $this->command->info('   dirsemillero@sena.edu.co   → Password123! (director semilleros)');
        $this->command->info('   lidersem@sena.edu.co       → Password123! (líder de semillero)');
        $this->command->info('   asesorsem@sena.edu.co      → Password123! (asesor de semillero)');
        $this->command->info('   dirgrupo1@sena.edu.co      → Password123! (director inv. — grupo Industria 9527)');
        $this->command->info('   dirgrupo2@sena.edu.co      → Password123! (director inv. — grupo Agroindustrial 9116)');
        $this->command->info('   investigador@sena.edu.co   → Password123! (investigador asociado - grupo 1)');
        $this->command->info('   superadmin@sena.edu.co     → Password123! (super administrador — CC 900000001)');
    }
}