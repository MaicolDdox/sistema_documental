<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ProjectModality;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProjectModalityController extends Controller
{
    private function getFields()
    {
        return [
            'nombre' => ['type' => 'text', 'options' => []],
            'descripcion' => ['type' => 'text', 'options' => []],

        ];
    }

    public function index(): View
    {
        $this->authorize('catalogos.leer');

        $items = ProjectModality::where('training_center_id', Auth::user()->training_center_id)->paginate(10);

        return view('admin.parametric.index', [
            'items' => $items,
            'title' => 'Modalidades de Proyectos',
            'routePrefix' => 'admin.project-modalities',
            'fields' => $this->getFields(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('catalogos.crear');

        return view('admin.parametric.create', [
            'title' => 'Crear Modalidades de Proyectos',
            'routePrefix' => 'admin.project-modalities',
            'fields' => $this->getFields(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('catalogos.crear');

        $validated = $request->validate($this->getValidationRules());
        // Custom request logic applies if $request is not just a base Request.
        if (method_exists($request, 'validated') && 'Request' !== 'Request') {
            $validated = $request->validated();
        }

        $validated['training_center_id'] = Auth::user()->training_center_id;
        ProjectModality::create($validated);

        if ($request->input('_from_simples')) {
            return redirect()->route('admin.catalogos.simples')->with('success', 'Modalidad de proyecto creada correctamente.');
        }

        return redirect()->route('admin.project-modalities.index')
            ->with('success', 'Registro creado exitosamente.');
    }

    public function edit(ProjectModality $projectmodality): View
    {
        $this->authorize('catalogos.editar');

        $this->checkCentroFormacion($projectmodality);

        return view('admin.parametric.edit', [
            'item' => $projectmodality,
            'title' => 'Editar Modalidades de Proyectos',
            'routePrefix' => 'admin.project-modalities',
            'fields' => $this->getFields(),
        ]);
    }

    public function update(Request $request, ProjectModality $projectmodality): RedirectResponse
    {
        $this->authorize('catalogos.editar');

        $this->checkCentroFormacion($projectmodality);

        $validated = $request->validate($this->getValidationRules());
        if (method_exists($request, 'validated') && 'Request' !== 'Request') {
            $validated = $request->validated();
        }

        $projectmodality->update($validated);

        if ($request->has('_from_simples')) {
            return redirect()->route('admin.catalogos.simples')
                ->with('success', 'Registro actualizado exitosamente.');
        }

        return redirect()->route('admin.project-modalities.index')
            ->with('success', 'Registro actualizado exitosamente.');
    }

    public function destroy(ProjectModality $projectmodality): RedirectResponse
    {
        $this->authorize('catalogos.eliminar');

        $this->checkCentroFormacion($projectmodality);

        try {
            $projectmodality->delete();

            return redirect()->route('admin.project-modalities.index')
                ->with('success', 'Registro eliminado exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('admin.project-modalities.index')
                ->with('error', 'No se puede eliminar porque está asociado a otros registros.');
        }
    }

    protected function getValidationRules(): array
    {
        $rules = [];
        foreach (array_keys($this->getFields()) as $f) {
            $rules[$f] = 'required';
        }

        return $rules;
    }

    private function checkCentroFormacion(ProjectModality $item): void
    {
        if ((int) $item->training_center_id !== (int) Auth::user()->training_center_id) {
            abort(403, 'No tienes permiso para gestionar catálogos de otros centros de formación.');
        }
    }

    // Note: Laravel resolves model bindings.
    // Ensure the parameter name $projectmodality matches route.
}
