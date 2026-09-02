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

        foreach ($tipos as $nombre) {
            \App\Models\InvestigationType::firstOrCreate(
                ['nombre' => $nombre],
                ['descripcion' => null]
            );
        }
    }
}
