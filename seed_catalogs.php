<?php
// Script: crea un training_record base y siembra los programas de formación
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// 1. Crear training_record base si no existe
$recordId = DB::table('training_records')->value('id');
if (!$recordId) {
    $recordId = DB::table('training_records')->insertGetId([
        'codigo'      => 2026001,
        'descripccion' => 'Ficha de formación 2026 – Semestre I',
        'created_at'  => now(),
        'updated_at'  => now(),
    ]);
    echo "✅ training_record base creado (id=$recordId)\n";
} else {
    echo "ℹ️  Usando training_record existente (id=$recordId)\n";
}

// Helper
$tipóId = fn($nombre) => DB::table('training_program_types')->where('nombre', $nombre)->value('id');

// 2. Programas de formación
$programas = [
    ['nombre' => 'Análisis y Desarrollo de Software',            'tipo' => 'Tecnólogo',                   'jornada' => 'diurna',   'modalidad' => 'presencial'],
    ['nombre' => 'Desarrollo de Software',                        'tipo' => 'Técnico',                     'jornada' => 'diurna',   'modalidad' => 'presencial'],
    ['nombre' => 'Sistemas',                                      'tipo' => 'Técnico',                     'jornada' => 'diurna',   'modalidad' => 'presencial'],
    ['nombre' => 'Diseño de Aplicaciones Web',                    'tipo' => 'Tecnólogo',                   'jornada' => 'nocturna', 'modalidad' => 'presencial'],
    ['nombre' => 'Gestión Empresarial',                           'tipo' => 'Tecnólogo',                   'jornada' => 'diurna',   'modalidad' => 'presencial'],
    ['nombre' => 'Contabilización de Operaciones',                'tipo' => 'Técnico',                     'jornada' => 'diurna',   'modalidad' => 'presencial'],
    ['nombre' => 'Electrónica',                                   'tipo' => 'Técnico',                     'jornada' => 'diurna',   'modalidad' => 'presencial'],
    ['nombre' => 'Mantenimiento de Computadores',                 'tipo' => 'Técnico',                     'jornada' => 'diurna',   'modalidad' => 'presencial'],
    ['nombre' => 'Multimedia',                                    'tipo' => 'Técnico',                     'jornada' => 'diurna',   'modalidad' => 'presencial'],
    ['nombre' => 'Automatización Industrial',                     'tipo' => 'Tecnólogo',                   'jornada' => 'diurna',   'modalidad' => 'presencial'],
    ['nombre' => 'Logística',                                     'tipo' => 'Tecnólogo',                   'jornada' => 'diurna',   'modalidad' => 'presencial'],
    ['nombre' => 'Programación de Software',                      'tipo' => 'Especialización Tecnológica', 'jornada' => 'nocturna', 'modalidad' => 'virtual'],
    ['nombre' => 'Operaciones Comerciales en El Punto de Venta',  'tipo' => 'Técnico',                     'jornada' => 'diurna',   'modalidad' => 'presencial'],
    ['nombre' => 'Servicios Farmacéuticos',                       'tipo' => 'Técnico',                     'jornada' => 'diurna',   'modalidad' => 'presencial'],
    ['nombre' => 'Cocina',                                        'tipo' => 'Técnico',                     'jornada' => 'diurna',   'modalidad' => 'presencial'],
];

$count = 0;
foreach ($programas as $p) {
    $tId = $tipóId($p['tipo']);
    if (!$tId) {
        echo "⚠️  Tipo '{$p['tipo']}' no encontrado, omitiendo '{$p['nombre']}'\n";
        continue;
    }
    DB::table('training_programs')->updateOrInsert(
        ['nombre' => $p['nombre'], 'training_program_type_id' => $tId],
        [
            'training_record_id'       => $recordId,
            'training_program_type_id' => $tId,
            'nombre'                   => $p['nombre'],
            'jornada'                  => $p['jornada'],
            'modalidad'                => $p['modalidad'],
            'estado'                   => 'activo',
            'created_at'               => now(),
            'updated_at'               => now(),
        ]
    );
    $count++;
}
echo "✅ $count programas de formación sembrados\n";
echo "Listo.\n";
