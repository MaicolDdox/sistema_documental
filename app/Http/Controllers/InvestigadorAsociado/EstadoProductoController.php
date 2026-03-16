<?php

namespace App\Http\Controllers\InvestigadorAsociado;

use App\Enums\EstadoRevisionEnum;
use App\Http\Controllers\Controller;
use App\Models\GroupProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Solo lectura — muestra al investigador el estado de sus productos
 * y el historial de observaciones del Director.
 */
class EstadoProductoController extends Controller
{
    use InvestigadorContext;

    /**
     * Resumen de estados de todos sus productos.
     */
    public function index(Request $request): View
    {
        $query = GroupProduct::with([
                'product.project',
                'reviews.reviewer.person',
            ])
            ->where('author_id', Auth::id());

        // Filtro por estado si se especifica
        if ($request->filled('estado')) {
            $query->where('estado_revision', $request->estado);
        }

        $productos = $query->latest()->paginate(20)->withQueryString();

        $contadores = [
            'pendiente'   => GroupProduct::where('author_id', Auth::id())->where('estado_revision', EstadoRevisionEnum::Pendiente)->count(),
            'en_revision' => GroupProduct::where('author_id', Auth::id())->where('estado_revision', EstadoRevisionEnum::EnRevision)->count(),
            'aprobado'    => GroupProduct::where('author_id', Auth::id())->where('estado_revision', EstadoRevisionEnum::Aprobado)->count(),
            'rechazado'   => GroupProduct::where('author_id', Auth::id())->where('estado_revision', EstadoRevisionEnum::Rechazado)->count(),
        ];

        $estadosRevision = EstadoRevisionEnum::cases();

        return view('investigador.estados.index', compact('productos', 'contadores', 'estadosRevision'));
    }
}
