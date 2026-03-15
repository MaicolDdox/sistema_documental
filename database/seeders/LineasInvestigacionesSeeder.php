<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class LineasInvestigacionesSeeder extends Seeder
{
    public function run(): void
    {
        $lineas = [
            'Producción Agropecuaria sostenible',
            'Desarrollo Agroindustrial de base tecnológica',
            'Empresarismo e inteligencia de mercados de base',
            'Gestión ambiental y aprovechamiento sostenible de los recursos naturales',
            'TIC aplicado al desarrollo sostenible',
        ];

        foreach ($lineas as $nombre) {
            \App\Models\ResearchLine::firstOrCreate(
                ['nombre' => $nombre],
                ['descripccion' => null]
            );
        }
    }
}
