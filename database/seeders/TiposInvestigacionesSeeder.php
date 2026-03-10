<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TiposInvestigacionesSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            [
                'nombre'       => 'Aplicada',
                'descripccion' => 'Investigación orientada a resolver problemas prácticos concretos.',
            ],
            [
                'nombre'       => 'Innovación',
                'descripccion' => 'Investigación orientada a desarrollar nuevos productos, procesos o servicios.',
            ],
        ];

        foreach ($tipos as $tipo) {
            \App\Models\InvestigationType::firstOrCreate(
                ['nombre' => $tipo['nombre']],
                ['descripccion' => $tipo['descripccion']]
            );
        }
    }
}
