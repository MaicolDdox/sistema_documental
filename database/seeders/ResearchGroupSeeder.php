<?php

namespace Database\Seeders;

use App\Enums\EstadoEnum;
use App\Models\ResearchGroup;
use App\Models\TrainingCenter;
use Illuminate\Database\Seeder;

class ResearchGroupSeeder extends Seeder
{
    public function run(): void
    {
        $centroAgroindustrial = TrainingCenter::where('codigo', 9116)->first();
        $centroIndustria      = TrainingCenter::where('codigo', 9527)->first();

        $grupos = [
            [
                'training_center_id' => $centroAgroindustrial?->id,
                'nombre'     => 'SIGESI Agroindustrial',
                'codigo'     => 9116,
                'descripccion' => 'Grupo de investigación del Centro Agroempresarial y Desarrollo Pecuario del Huila.',
                'estado'     => EstadoEnum::Activo,
                'logo'       => '',
            ],
            [
                'training_center_id' => $centroIndustria?->id,
                'nombre'     => 'SIGESI Industria',
                'codigo'     => 9527,
                'descripccion' => 'Grupo de investigación del Centro de la Industria, la Empresa y los Servicios.',
                'estado'     => EstadoEnum::Activo,
                'logo'       => '',
            ],
        ];

        foreach ($grupos as $datos) {
            ResearchGroup::firstOrCreate(
                ['codigo' => $datos['codigo']],
                $datos
            );
        }

        $this->command->info('✅ Grupos de investigación creados:');
        foreach (ResearchGroup::all() as $g) {
            $this->command->info("   [{$g->id}] {$g->nombre} — {$g->codigo}");
        }
    }
}
