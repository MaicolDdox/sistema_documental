<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TiposInvestigacionesSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            'Investigación Aplicada',
            'Investigación Formativa',
            'Desarrollo Tecnológico',
            'Investigación Exploratoria',
            'Innovación',
        ];

        foreach (\App\Models\TrainingCenter::all() as $centro) {
            foreach ($tipos as $nombre) {
                \App\Models\InvestigationType::firstOrCreate(
                    ['training_center_id' => $centro->id, 'nombre' => $nombre],
                    ['descripcion' => null]
                );
            }
        }
    }
}
