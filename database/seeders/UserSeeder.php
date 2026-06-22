<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Person;
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
                'training_center_id' => $centroIndustria->id,
                'email' => 'liderIndustrialsem@sena.edu.co',
                'tipo_documento' => 'cedula ciudadana',
                'numero_documento' => 103049521,
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
            [
                'training_center_id' => $centroIndustria->id,
                'email' => 'asesorIndu@sena.edu.co',
                'tipo_documento' => 'cedula ciudadana',
                'numero_documento' => 55114456,
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
            [

            'training_center_id' => $centroIndustria->id,
                'email' => 'investigadorIndu@sena.edu.co',
                'tipo_documento' => 'cedula ciudadana',
                'numero_documento' => 33333334,
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

        // Datos de persona por email: [primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, genero, entity_position_id]
        $peopleByEmail = [
            'ydmoreno@sena.edu.co'           => ['Yolanda',  'Del Carmen', 'Moreno',        'Ospina',   'femenino',  5],
            'jovalenciap@sena.edu.co'        => ['Jorge',    'Andrés',     'Valencia',      'Parra',    'masculino', 5],
            'directorsem@sena.edu.co'        => ['Carlos',   'Alberto',    'Rodríguez',     'Gómez',    'masculino', 6],
            'dirsemillero@sena.edu.co'       => ['Ana',      'Lucía',      'Soto',          'Jiménez',  'femenino',  6],
            'lidersem@sena.edu.co'           => ['Luis',     'Fernando',   'Bermúdez',      'Torres',   'masculino', 6],
            'liderIndustrialsem@sena.edu.co' => ['María',    'Isabel',     'Patiño',        'Ruiz',     'femenino',  6],
            'asesorsem@sena.edu.co'          => ['Pedro',    'José',       'Herrera',       'Castillo', 'masculino', 10],
            'asesorIndu@sena.edu.co'         => ['Sandra',   'Milena',     'Vargas',        'Peña',     'femenino',  10],
            'dirgrupo1@sena.edu.co'          => ['Roberto',  'Carlos',     'Montoya',       'Ríos',     'masculino', 5],
            'dirgrupo2@sena.edu.co'          => ['Patricia', 'Elena',      'Gutiérrez',     'Lozano',   'femenino',  5],
            'investigador@sena.edu.co'       => ['Felipe',   'Augusto',    'Mora',          'Salinas',  'masculino', 4],
            'investigadorIndu@sena.edu.co'   => ['Laura',    'Cristina',   'Díaz',          'Medina',   'femenino',  4],
            'superadmin@sena.edu.co'         => ['Super',    null,         'Administrador', null,       'masculino', 2],
        ];

        $rolesByEmail = [
            'ydmoreno@sena.edu.co' => 'administrador_sistema',
            'jovalenciap@sena.edu.co' => 'administrador_sistema',
            'directorsem@sena.edu.co' => 'director_semilleros',
            'dirsemillero@sena.edu.co' => 'director_semilleros',
            'lidersem@sena.edu.co' => 'lider_semillero',
            'liderIndustrialsem@sena.edu.co' => 'lider_semillero',
            'asesorsem@sena.edu.co' => 'asesor_semillero',
            'asesorIndu@sena.edu.co' => 'asesor_semillero',
            'dirgrupo1@sena.edu.co' => 'director_investigacion',
            'dirgrupo2@sena.edu.co' => 'director_investigacion',
            'investigador@sena.edu.co' => 'investigador_asociado',
            'investigadorIndu@sena.edu.co' => 'investigador_asociado',
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

            // Crear registro en people si no existe
            if (!$user->person && isset($peopleByEmail[$user->email])) {
                [$pNombre, $sNombre, $pApellido, $sApellido, $genero, $posId] = $peopleByEmail[$user->email];
                Person::create([
                    'user_id'             => $user->id,
                    'primer_nombre'       => $pNombre,
                    'segundo_nombre'      => $sNombre,
                    'primer_apellido'     => $pApellido,
                    'segundo_apellido'    => $sApellido,
                    'genero'              => $genero,
                    'email_institucional' => $user->email,
                    'entity_position_id'  => $posId,
                    'linkage_type_id'     => 5,
                ]);
            }
        }

        // firstOrCreate no actualiza sede si el usuario ya existía: alinear centros de prueba
        User::where('email', 'directorsem@sena.edu.co')->update(['training_center_id' => $centroAgroindustrial->id]);
        User::where('email', 'dirsemillero@sena.edu.co')->update(['training_center_id' => $centroIndustria->id]);
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

        // Vincular investigadorIndu al grupo del centro Industria
        $investigadorIndu = User::where('email', 'investigadorIndu@sena.edu.co')->first();
        if ($investigadorIndu && $grupoIndustria) {
            ResearchGroupUser::firstOrCreate(
                ['research_group_id' => $grupoIndustria->id, 'user_id' => $investigadorIndu->id],
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

        // Vincular asesorIndu al semillero del grupo de Industria
        $asesorIndu = User::where('email', 'asesorIndu@sena.edu.co')->first();
        $semilleroIndustria = Seedling::where('research_group_id', $grupoIndustria?->id)->first();
        if ($asesorIndu && $semilleroIndustria) {
            $externalIndu = ExternalAdvisor::firstOrCreate(
                ['user_id' => $asesorIndu->id],
                [
                    'nombre_completo' => $asesorIndu->person?->primer_nombre
                        ? trim($asesorIndu->person->primer_nombre . ' ' . $asesorIndu->person->primer_apellido)
                        : $asesorIndu->email,
                    'email'       => $asesorIndu->email,
                    'telefono'    => '',
                    'institucion' => 'SENA',
                ]
            );
            SeedlingAdvisor::firstOrCreate(
                [
                    'seedling_id'         => $semilleroIndustria->id,
                    'external_advisor_id' => $externalIndu->id,
                ],
                ['activo' => true]
            );
        }

        $this->command->info('Usuarios creados:');
        $this->command->info('   ydmoreno@sena.edu.co       → Password123! (administrador)');
        $this->command->info('   jovalenciap@sena.edu.co    → Password123! (administrador)');
        $this->command->info('   directorsem@sena.edu.co    → Password123! (director semilleros)');
        $this->command->info('   dirsemillero@sena.edu.co   → Password123! (director semilleros)');
        $this->command->info('   lidersem@sena.edu.co       → Password123! (líder de semillero - Agroindustrial)');
        $this->command->info('   liderIndustrialsem@sena.edu.co → Password123! (líder de semillero - Industria)');
        $this->command->info('   asesorsem@sena.edu.co      → Password123! (asesor semillero - Agroindustrial 9116)');
        $this->command->info('   asesorIndu@sena.edu.co     → Password123! (asesor semillero - Industria 9527)');
        $this->command->info('   dirgrupo1@sena.edu.co      → Password123! (director inv. — grupo Industria 9527)');
        $this->command->info('   dirgrupo2@sena.edu.co      → Password123! (director inv. — grupo Agroindustrial 9116)');
        $this->command->info('   investigador@sena.edu.co   → Password123! (investigador asociado - grupo 2)');
        $this->command->info('   investigadorIndu@sena.edu.co → Password123! (investigador asociado - grupo 1)');
        $this->command->info('   superadmin@sena.edu.co     → Password123! (super administrador — CC 900000001)');
    }
}