<?php

namespace Database\Seeders;

use App\Models\TrainingCenter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LinkageTypesSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            ['nombre' => 'Planta',      'descripcion' => 'Personal vinculado a la planta del centro de formación.'],
            ['nombre' => 'Contratista', 'descripcion' => 'Vinculado mediante contrato de prestación de servicios.'],
            ['nombre' => 'Otros',       'descripcion' => 'Otro tipo de vinculación no contemplado en las anteriores.'],
        ];

        foreach (TrainingCenter::all() as $centro) {
            foreach ($tipos as $tipo) {
                DB::table('linkage_types')->updateOrInsert(
                    ['nombre' => $tipo['nombre'], 'training_center_id' => $centro->id],
                    array_merge($tipo, ['training_center_id' => $centro->id, 'created_at' => now(), 'updated_at' => now()])
                );
            }
        }
    }
}
