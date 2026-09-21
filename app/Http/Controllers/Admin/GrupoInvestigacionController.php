<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EstadoEnum;
use App\Http\Controllers\Controller;
use App\Models\GrupoInvestigacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Gestión de grupos_investigacion por administrador_sistema — reforma GDI/SDI.
 * El admin crea el grupo con datos básicos (nombre, código, centro); la
 * descripción, el logo y la línea de investigación los completa después el
 * director_grupo_investigacion asignado (ver DirectorGrupoInvestigacion\GrupoInvestigacionController).
 */
class GrupoInvestigacionController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('grupos_investigacion.listar');

        $user = Auth::user();

        $query = GrupoInvestigacion::with(['director.person'])
            ->where('training_center_id', $user->training_center_id);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('codigo', 'like', "%{$search}%");
            });
        }

        $grupos = $query->orderBy('nombre')->paginate(10)->withQueryString();

        return view('admin.grupos_investigacion.index', compact('grupos'));
    }

    public function create()
    {
        $this->authorize('grupos_investigacion.crear');

        return view('admin.grupos_investigacion.create');
    }

    public function store(Request $request)
    {
        $this->authorize('grupos_investigacion.crear');

        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'codigo' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9]+$/', 'unique:grupos_investigacion,codigo'],
        ], [
            'codigo.regex' => 'El código solo puede tener letras y números, sin espacios ni símbolos.',
        ]);

        GrupoInvestigacion::create([
            'training_center_id' => Auth::user()->training_center_id,
            'creator_id' => Auth::id(),
            'director_id' => null,
            'nombre' => $validated['nombre'],
            'codigo' => $validated['codigo'],
            'estado' => EstadoEnum::Activo,
        ]);

        return redirect()->route('admin.grupos-investigacion.index')
            ->with('success', 'Grupo de investigación creado correctamente.');
    }

    public function show(GrupoInvestigacion $grupos_investigacion)
    {
        $this->authorize('grupos_investigacion.ver_detalle');
        $this->checkCentroFormacion($grupos_investigacion);

        $grupos_investigacion->load(['director.person', 'lineaInvestigacionPrincipal', 'mincienciasProducts']);

        return view('admin.grupos_investigacion.show', ['grupo' => $grupos_investigacion]);
    }

    public function edit(GrupoInvestigacion $grupos_investigacion)
    {
        $this->authorize('grupos_investigacion.editar');
        $this->checkCentroFormacion($grupos_investigacion);

        return view('admin.grupos_investigacion.edit', ['grupo' => $grupos_investigacion]);
    }

    public function update(Request $request, GrupoInvestigacion $grupos_investigacion)
    {
        $this->authorize('grupos_investigacion.editar');
        $this->checkCentroFormacion($grupos_investigacion);

        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'codigo' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9]+$/', 'unique:grupos_investigacion,codigo,'.$grupos_investigacion->id],
        ], [
            'codigo.regex' => 'El código solo puede tener letras y números, sin espacios ni símbolos.',
        ]);

        $grupos_investigacion->update([
            'nombre' => $validated['nombre'],
            'codigo' => $validated['codigo'],
        ]);

        return redirect()->route('admin.grupos-investigacion.index')
            ->with('success', 'Grupo de investigación actualizado correctamente.');
    }

    private function checkCentroFormacion(GrupoInvestigacion $grupo): void
    {
        $user = Auth::user();
        if (! $user->training_center_id) {
            return;
        }

        if ((int) $grupo->training_center_id !== (int) $user->training_center_id) {
            abort(403, 'No tienes permiso para gestionar grupos de investigación de otros centros de formación.');
        }
    }
}
