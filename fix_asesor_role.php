<?php
// Script temporal: asigna rol asesor_semillero a todos los usuarios
// que tienen un ExternalAdvisor vinculado a un Seedling pero no tienen rol
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Encontrar usuarios en external_advisors que tienen user_id pero no rol
$advisors = \App\Models\ExternalAdvisor::whereNotNull('user_id')->get();
foreach ($advisors as $adv) {
    $user = \App\Models\User::find($adv->user_id);
    if ($user && !$user->hasAnyRole(['asesor_semillero','lider_semillero','director_semilleros','administrador_sistema','director_investigacion','investigador_asociado'])) {
        $user->syncRoles(['asesor_semillero']);
        echo "✅ Rol asesor_semillero asignado a: {$user->email}\n";
    }
}
echo "Listo.\n";
