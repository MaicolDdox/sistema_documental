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

        foreach (\App\Models\TrainingCenter::all() as $centro) {
            foreach ($modalidades as $nombre) {
                \App\Models\ProjectModality::firstOrCreate(
                    ['training_center_id' => $centro->id, 'nombre' => $nombre],
                    ['descripcion' => null]
                );
            }
        }
    }
}
