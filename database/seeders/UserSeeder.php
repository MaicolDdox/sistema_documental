<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\TrainingCenter;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $centroAgroindustrial = TrainingCenter::where('codigo', 9116)->firstOrFail();
        $centroIndustria      = TrainingCenter::where('codigo', 9527)->firstOrFail();

        $users = [
            [
                'training_center_id' => $centroAgroindustrial->id,
                'email'              => 'ydmoreno@sena.edu.co',
                'tipo_documento'     => 'cedula ciudadana',
                'numero_documento'   => 34327134,
                'password'           => Hash::make('Password123!'),
                'estado'             => 'activo',
            ],
            [
                'training_center_id' => $centroIndustria->id,
                'email'              => 'jovalenciap@sena.edu.co',
                'tipo_documento'     => 'cedula ciudadana',
                'numero_documento'   => 10304952,
                'password'           => Hash::make('Password123!'),
                'estado'             => 'activo',
            ],
            [
                'training_center_id' => $centroAgroindustrial->id,
                'email'              => 'directorsem@sena.edu.co',
                'tipo_documento'     => 'cedula ciudadana',
                'numero_documento'   => 1076504087,
                'password'           => Hash::make('Password123!'),
                'estado'             => 'activo',
            ],
            [
                'training_center_id' => $centroIndustria->id,
                'email'              => 'dirsemillero@sena.edu.co',
                'tipo_documento'     => 'cedula ciudadana',
                'numero_documento'   => 52345678,
                'password'           => Hash::make('Password123!'),
                'estado'             => 'activo',
            ],
            [
                'training_center_id' => $centroAgroindustrial->id,
                'email'              => 'lidersem@sena.edu.co',
                'tipo_documento'     => 'cedula ciudadana',
                'numero_documento'   => 87654321,
                'password'           => Hash::make('Password123!'),
                'estado'             => 'activo',
            ],
            [
                 'training_center_id' => $centroAgroindustrial->id,
                'email'              => 'asesorsem@sena.edu.co',
                'tipo_documento'     => 'cedula ciudadana',
                'numero_documento'   => 55114455,
                'password'           => Hash::make('Password123!'),
                'estado'             => 'activo',
            ],
        ];

        $rolesByEmail = [
            'ydmoreno@sena.edu.co'    => 'administrador_sistema',
            'jovalenciap@sena.edu.co' => 'administrador_sistema',
            'directorsem@sena.edu.co' => 'director_semilleros',
            'dirsemillero@sena.edu.co'=> 'director_semilleros',
            'lidersem@sena.edu.co'    => 'lider_semillero',
            'asesorsem@sena.edu.co'   => 'asesor_semillero',
        ];

        foreach ($users as $userData) {
            // firstOrCreate no borra ni reemplaza, solo crea si no existe
            $user = User::firstOrCreate(
                ['numero_documento' => $userData['numero_documento']],
                $userData
            );

            // Asignar rol correspondiente
            if (isset($rolesByEmail[$user->email])) {
                $user->assignRole($rolesByEmail[$user->email]);
            }
        }

        $this->command->info('✅ Usuarios creados:');
        $this->command->info('   ydmoreno@sena.edu.co → Password123! (administrador)');
        $this->command->info('   jovalenciap@sena.edu.co → Password123! (administrador)');
        $this->command->info('   directorsem@sena.edu.co → Password123! (director semilleros)');
        $this->command->info('   dirsemillero@sena.edu.co → Password123! (director semilleros)');
        $this->command->info('   lidersem@sena.edu.co → Password123! (líder de semillero)');
        $this->command->info('   asesorsem@sena.edu.co → Password123! (asesor de semillero)');
    }
}
