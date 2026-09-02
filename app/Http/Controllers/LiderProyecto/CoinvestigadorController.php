<?php

namespace App\Http\Controllers\LiderProyecto;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CoinvestigadorController extends Controller
{
    use LiderProyectoContext;

    public function index(Request $request): View
    {
        $proyecto = $this->miProyecto();

        $vinculados = $proyecto->authors()
            ->wherePivot('activo', true)
            ->whereHas('roles', fn ($q) => $q->where('name', 'co_investigador'))
            ->with('person')
            ->get();

        $disponiblesQuery = User::role('co_investigador')
            ->with('person')
            ->whereNotIn('id', $vinculados->pluck('id'));

        if ($request->filled('search')) {
            $term = $request->search;
            $disponiblesQuery->where(function ($q) use ($term) {
                $q->where('email', 'like', "%{$term}%")
                    ->orWhere('numero_documento', 'like', "%{$term}%")
                    ->orWhereHas('person', fn ($p) => $p
                        ->where('primer_nombre', 'like', "%{$term}%")
                        ->orWhere('primer_apellido', 'like', "%{$term}%"));
            });
        }

        $disponibles = $disponiblesQuery->orderBy('email')->get();

        return view('lider_proyecto.coinvestigadores.index', compact('proyecto', 'vinculados', 'disponibles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $proyecto = $this->miProyecto();

        $validated = $request->validate(['user_id' => 'required|exists:users,id']);

        $coInvestigador = User::role('co_investigador')->findOrFail($validated['user_id']);

        $proyecto->authors()->syncWithoutDetaching([
            $coInvestigador->id => ['activo' => true],
        ]);

        return redirect()->route('lider-proyecto.coinvestigadores.index')
            ->with('success', 'Co-investigador vinculado al proyecto.');
    }

    public function destroy(User $coinvestigador): RedirectResponse
    {
        $proyecto = $this->miProyecto();

        // BUG-20260813-037 — updateExistingPivot() no lanza error si no hay
        // fila que actualizar, solo devuelve 0 filas afectadas. Sin este
        // chequeo, desvincular a alguien que nunca estuvo vinculado a este
        // proyecto igual mostraba "desvinculado" sin haber hecho nada.
        $actualizados = $proyecto->authors()->updateExistingPivot($coinvestigador->id, ['activo' => false]);

        if ($actualizados === 0) {
            return redirect()->route('lider-proyecto.coinvestigadores.index')
                ->with('error', 'Este co-investigador no está vinculado a tu proyecto.');
        }

        return redirect()->route('lider-proyecto.coinvestigadores.index')
            ->with('success', 'Co-investigador desvinculado del proyecto.');
    }
}
