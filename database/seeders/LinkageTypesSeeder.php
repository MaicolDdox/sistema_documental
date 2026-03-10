<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LinkageTypesSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            ['nombre' => 'Aprendiz SENA',          'descripccion' => 'Aprendiz vinculado mediante contrato de aprendizaje con el SENA.'],
            ['nombre' => 'Practicante universitario','descripccion' => 'Estudiante universitario en práctica profesional.'],
            ['nombre' => 'Voluntario',               'descripccion' => 'Participante voluntario sin contrato formal.'],
            ['nombre' => 'Investigador externo',     'descripccion' => 'Investigador vinculado mediante convenio interinstitucional.'],
            ['nombre' => 'Instructor SENA',          'descripccion' => 'Instructor del SENA que participa como integrante.'],
            ['nombre' => 'Contrato de prestación de servicios', 'descripccion' => 'Vinculado mediante contrato de prestación de servicios.'],
        ];

        foreach ($tipos as $tipo) {
            DB::table('linkage_types')->updateOrInsert(
                ['nombre' => $tipo['nombre']],
                array_merge($tipo, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
