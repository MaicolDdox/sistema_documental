<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\ResearchGroupUser;

$u = User::find(9);
echo '=== DIAGNÓSTICO INVESTIGADOR ===' . PHP_EOL;
echo 'Email: ' . $u->email . PHP_EOL;
echo 'Estado raw: ' . $u->getRawOriginal('estado') . PHP_EOL;
echo 'Roles Spatie: ' . $u->getRoleNames()->implode(', ') . PHP_EOL;

$all_rgu = ResearchGroupUser::where('user_id', $u->id)->get();
echo 'Registros research_group_users: ' . $all_rgu->count() . PHP_EOL;
foreach ($all_rgu as $rgu) {
    echo '  -> grupo_id=' . $rgu->research_group_id . ' rol_raw=' . $rgu->getRawOriginal('rol') . PHP_EOL;
}

$rolSpatie = \Spatie\Permission\Models\Role::where('name', 'investigador_asociado')->first();
echo 'Rol investigador_asociado Spatie: ' . ($rolSpatie ? 'SÍ id=' . $rolSpatie->id : 'NO EXISTE') . PHP_EOL;
