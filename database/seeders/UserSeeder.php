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
                'training_center_id' => $centroIndustria->id,
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
                'training_center_id' => $centroAgroindustrial->id,
                'email' => 'dirgrupo1@sena.edu.co',
                'tipo_documento' => 'cedula ciudadana',
                'numero_documento' => 11111111,
                'password' => Hash::make('Password123!'),
                'estado' => 'activo',
            ],
            [
                'training_center_id' => $centroIndustria->id,
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
        ];

        foreach ($users as $userData) {
            $user = User::firstOrCreate(
                ['numero_documento' => $userData['numero_documento']],
                $userData
            );

            if (isset($rolesByEmail[$user->email])) {
                $user->assignRole($rolesByEmail[$user->email]);
            }
        }

        // Vincular directores de investigación a sus grupos en research_group_users
        $dirGrupo1 = User::where('email', 'dirgrupo1@sena.edu.co')->first();
        $dirGrupo2 = User::where('email', 'dirgrupo2@sena.edu.co')->first();

        // Obtener los dos grupos de investigación (primero = Agroindustrial, segundo = Industria)
        $grupos = ResearchGroup::orderBy('id')->take(2)->get();

        if ($dirGrupo1 && $grupos->isNotEmpty()) {
            ResearchGroupUser::firstOrCreate(
                ['research_group_id' => $grupos->first()->id, 'user_id' => $dirGrupo1->id],
                ['rol' => RolGrupoEnum::Director]
            );
        }

        if ($dirGrupo2 && $grupos->count() >= 2) {
            ResearchGroupUser::firstOrCreate(
                ['research_group_id' => $grupos->last()->id, 'user_id' => $dirGrupo2->id],
                ['rol' => RolGrupoEnum::Director]
            );
        }

        // Vincular investigador asociado de prueba al grupo 1
        $investigador = User::where('email', 'investigador@sena.edu.co')->first();
        if ($investigador && $grupos->isNotEmpty()) {
            ResearchGroupUser::firstOrCreate(
                ['research_group_id' => $grupos->first()->id, 'user_id' => $investigador->id],
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
        $this->command->info('   dirgrupo1@sena.edu.co      → Password123! (director investigación - grupo 1)');
        $this->command->info('   dirgrupo2@sena.edu.co      → Password123! (director investigación - grupo 2)');
        $this->command->info('   investigador@sena.edu.co   → Password123! (investigador asociado - grupo 1)');
    }
}