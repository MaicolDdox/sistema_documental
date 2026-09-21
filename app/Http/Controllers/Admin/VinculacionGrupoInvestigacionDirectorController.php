<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GrupoInvestigacion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Vinculación 1-a-1 grupo_investigacion <-> director_grupo_investigacion,
 * calco estructural de DirectorSemilleros\VinculacionSemilleroLiderController
 * adaptado a que aquí quien vincula (administrador_sistema) SÍ tiene su
 * propio training_center_id fijo — a diferencia de director_semilleros, que
 * filtraba semilleros ajenos vía leader/creator.
 */
class VinculacionGrupoInvestigacionDirectorController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('grupos_investigacion.asignar_director');

        $user = Auth::user();
        $search = trim((string) $request->query('search', ''));

        $grupos = GrupoInvestigacion::query()
            ->with(['director.person'])
            ->where('training_center_id', $user->training_center_id)
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($nested) use ($search) {
                    $nested->where('nombre', 'like', "%{$search}%")
                        ->orWhere('codigo', 'like', "%{$search}%");
                });
            })
            ->orderBy('nombre')
            ->paginate(12)
            ->withQueryString();

        $directores = User::role('director_grupo_investigacion')
            ->with('person')
            ->where('training_center_id', $user->training_center_id)
            ->active()
            ->orderBy('email')
            ->get();

        $directoresYaVinculadosIds = GrupoInvestigacion::query()
            ->where('training_center_id', $user->training_center_id)
            ->whereNotNull('director_id')
            ->pluck('director_id')
            ->unique()
            ->values()
            ->all();

        return view('admin.vinculaciones_grupo_investigacion.index', compact('grupos', 'directores', 'search', 'directoresYaVinculadosIds'));
    }

    public function update(Request $request, GrupoInvestigacion $grupo)
    {
        $this->authorize('grupos_investigacion.asignar_director');
        // BUG-20260914-004: reemplaza checkCentroFormacion() por la Policy
        // centralizada que usa el campo directo training_center_id.
        $this->authorize('update', $grupo);

        $validated = $request->validate([
            'director_id' => [
                'nullable',
                Rule::exists('users', 'id')->where('training_center_id', Auth::user()->training_center_id),
            ],
        ]);

        if (! empty($validated['director_id'])) {
            $director = User::findOrFail($validated['director_id']);

            if (! $director->hasRole('director_grupo_investigacion')) {
                return back()->withErrors(['director_id' => 'El usuario seleccionado no es director de grupo de investigación.']);
            }

            if ((int) $director->training_center_id !== (int) Auth::user()->training_center_id) {
                return back()->withErrors(['director_id' => 'El director debe pertenecer a tu centro de formación.']);
            }

            $yaAsignadoAOtroGrupo = GrupoInvestigacion::query()
                ->where('director_id', $director->id)
                ->whereKeyNot($grupo->id)
                ->exists();

            if ($yaAsignadoAOtroGrupo) {
                return back()->withErrors(['director_id' => 'Este director ya está vinculado a otro grupo de investigación.']);
            }
        }

        $grupo->update([
            'director_id' => $validated['director_id'] ?? null,
        ]);

        return redirect()
            ->route('admin.vinculaciones-grupo-investigacion.index')
            ->with('success', 'Vinculación actualizada correctamente.');
    }
}
