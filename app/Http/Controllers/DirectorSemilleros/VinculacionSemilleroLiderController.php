<?php

namespace App\Http\Controllers\DirectorSemilleros;

use App\Http\Controllers\Controller;
use App\Models\Seedling;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VinculacionSemilleroLiderController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('semilleros.editar');

        $user = Auth::user();
        $search = trim((string) $request->query('search', ''));

        $semilleros = Seedling::query()
            ->with(['leader.person', 'creator'])
            ->where(function ($q) use ($user) {
                $q->whereHas('leader', function ($leaderQuery) use ($user) {
                    $leaderQuery->where('training_center_id', $user->training_center_id);
                })->orWhereHas('creator', function ($creatorQuery) use ($user) {
                    $creatorQuery->where('training_center_id', $user->training_center_id);
                });
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($nested) use ($search) {
                    $nested->where('nombre', 'like', "%{$search}%")
                        ->orWhere('codigo', 'like', "%{$search}%");
                });
            })
            ->orderBy('nombre')
            ->paginate(12)
            ->withQueryString();

        $lideres = User::role('lider_semillero')
            ->with('person')
            ->where('training_center_id', $user->training_center_id)
            ->active()
            ->orderBy('email')
            ->get();

        $lideresYaVinculadosIds = Seedling::query()
            ->whereNotNull('leader_id')
            ->pluck('leader_id')
            ->unique()
            ->values()
            ->all();

        return view('director_semilleros.vinculaciones.index', compact('semilleros', 'lideres', 'search', 'lideresYaVinculadosIds'));
    }

    public function update(Request $request, Seedling $semillero)
    {
        $this->authorize('semilleros.editar');
        $this->checkCentroFormacion($semillero);

        $validated = $request->validate([
            'lider_id' => 'nullable|exists:users,id',
        ]);

        if (! empty($validated['lider_id'])) {
            $lider = User::findOrFail($validated['lider_id']);

            if (! $lider->hasRole('lider_semillero')) {
                return back()->withErrors(['lider_id' => 'El usuario seleccionado no es líder de semillero.']);
            }

            if ((int) $lider->training_center_id !== (int) Auth::user()->training_center_id) {
                return back()->withErrors(['lider_id' => 'El líder debe pertenecer a tu centro de formación.']);
            }

            $yaAsignadoAOtroSemillero = Seedling::query()
                ->where('leader_id', $lider->id)
                ->whereKeyNot($semillero->id)
                ->exists();

            if ($yaAsignadoAOtroSemillero) {
                return back()->withErrors(['lider_id' => 'Este líder ya está vinculado a otro semillero.']);
            }
        }

        $semillero->update([
            'leader_id' => $validated['lider_id'] ?? null,
        ]);

        return redirect()
            ->route('dir-sem.vinculaciones.index')
            ->with('success', 'Vinculación actualizada correctamente.');
    }

    private function checkCentroFormacion(Seedling $semillero): void
    {
        $user = Auth::user();
        $leaderCenterId = $semillero->leader?->training_center_id;
        $creatorCenterId = $semillero->creator?->training_center_id;

        if (
            ($leaderCenterId !== null && $leaderCenterId !== $user->training_center_id)
            || ($leaderCenterId === null && $creatorCenterId !== null && $creatorCenterId !== $user->training_center_id)
        ) {
            abort(403, 'No tienes permiso para gestionar semilleros de otros centros de formación.');
        }
    }
}
