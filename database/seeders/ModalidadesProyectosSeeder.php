<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ModalidadesProyectosSeeder extends Seeder
{
    public function run(): void
    {
        $modalidades = [
            'Capacidad Instalada',
            'Recursos Internos SENA',
            'Recursos Externos Convenios',
            'Otros',
        ];

        foreach ($modalidades as $nombre) {
            \App\Models\ProjectModality::firstOrCreate(
                ['nombre' => $nombre],
                ['descripccion' => null]
            );
        }
    }
}
