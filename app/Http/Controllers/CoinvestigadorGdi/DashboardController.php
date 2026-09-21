<?php

namespace App\Http\Controllers\CoinvestigadorGdi;

use App\Enums\EstadoRevisionEnum;
use App\Http\Controllers\Controller;
use App\Models\MincienciasProduct;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $porEstado = MincienciasProduct::where('user_id', Auth::id())
            ->selectRaw('estado_revision, COUNT(*) as total')
            ->groupBy('estado_revision')
            ->pluck('total', 'estado_revision');

        $conteos = [
            'pendiente' => (int) ($porEstado[EstadoRevisionEnum::Pendiente->value] ?? 0),
            'aprobado' => (int) ($porEstado[EstadoRevisionEnum::Aprobado->value] ?? 0),
            'rechazado' => (int) ($porEstado[EstadoRevisionEnum::Rechazado->value] ?? 0),
        ];

        return view('co_investigador_gdi.dashboard', compact('conteos'));
    }
}
