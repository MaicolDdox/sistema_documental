<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TrainingCenter;
use App\Models\Department;
use App\Models\City;

class TrainingCenterSeeder extends Seeder
{
    public function run(): void
    {
        $huila = Department::where('nombre', 'Huila')->firstOrFail();

        $centers = [
            [
                'nombre'        => 'Centro de la Industria, la Empresa y los Servicios',
                'codigo'        => 9527,
                'department_id' => $huila->id,
                'city_id'       => City::where('nombre', 'Neiva')
                                       ->where('department_id', $huila->id)
                                       ->firstOrFail()->id,
            ],
            [
                'nombre'        => 'Centro de Formación Agroindustrial',
                'codigo'        => 9116,
                'department_id' => $huila->id,
                'city_id'       => City::where('nombre', 'Campoalegre')
                                       ->where('department_id', $huila->id)
                                       ->firstOrFail()->id,
            ],
        ];

        foreach ($centers as $center) {
            TrainingCenter::firstOrCreate(
                ['codigo' => $center['codigo']],
                $center
            );
        }

        $this->command->info('✅ ' . count($centers) . ' centros de formación insertados.');
    }
}
