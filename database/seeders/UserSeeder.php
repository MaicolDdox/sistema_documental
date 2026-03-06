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
        ];

        foreach ($users as $userData) {
            User::firstOrCreate(
                ['numero_documento' => $userData['numero_documento']],
                $userData
            );
        }

        $this->command->info('✅ Usuarios creados:');
        $this->command->info('   ydmoreno@sena.edu.co → Password123! (administrador)');
        $this->command->info('   jovalenciap@sena.edu.co → Password123! (administrador)');
        $this->command->info('   directorsem@sena.edu.co → Password123! (director semilleros)');
        $this->command->info('   dirsemillero@sena.edu.co → Password123! (director semilleros)');
        $this->command->info('   lidersem@sena.edu.co → Password123! (líder de semillero)');
    }
}
