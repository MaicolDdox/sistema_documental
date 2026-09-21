<?php

namespace App\Http\Controllers\DirectorGrupoInvestigacion;

use App\Enums\EstadoRevisionEnum;
use App\Http\Controllers\Controller;
use App\Models\GrupoInvestigacion;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $grupo = GrupoInvestigacion::where('director_id', Auth::id())->firstOrFail();

        $porEstado = $grupo->mincienciasProducts()
            ->selectRaw('estado_revision, COUNT(*) as total')
            ->groupBy('estado_revision')
            ->pluck('total', 'estado_revision');

        $conteos = [
            'pendiente' => (int) ($porEstado[EstadoRevisionEnum::Pendiente->value] ?? 0),
            'aprobado' => (int) ($porEstado[EstadoRevisionEnum::Aprobado->value] ?? 0),
            'rechazado' => (int) ($porEstado[EstadoRevisionEnum::Rechazado->value] ?? 0),
        ];

        return view('director_grupo_investigacion.dashboard', compact('grupo', 'conteos'));
    }
}
