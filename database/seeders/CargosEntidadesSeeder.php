<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CargosEntidadesSeeder extends Seeder
{
    /**
     * Siembra la tabla entity_positions con los cargos institucionales del
     * SENA usados en el campo "Cargo / Posición" del perfil de usuario
     * (BUG-20260813-044). Usa firstOrCreate para ser idempotente.
     */
    public function run(): void
    {
        $cargos = [
            'Investigador(a) SENNOVA',
            'Instructor(a)',
            'Experto(a) Tecnoparque',
            'Facilitador(a) Tecnoacademia',
            'Administrativo',
            'Apoyo Técnico Tecnoparque',
            'Dinamizador Extensionismo Tecnológico',
            'Dinamizador SENNOVA',
            'Dinamizador Tecnoacademia',
            'Líder Grupo de investigación',
            'Líder Semillero de Investigación',
            'Servicios Tecnológicos',
            'Otro:',
        ];

        foreach ($cargos as $nombre) {
            \App\Models\EntityPosition::firstOrCreate(['nombre' => $nombre]);
        }
    }
}
