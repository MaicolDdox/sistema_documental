<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ProjectModality;
use Illuminate\Http\Request;


use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProjectModalityController extends Controller
{
    private function getFields() {
        return [
            'nombre' => ['type' => 'text', 'options' => []],
            'descripccion' => ['type' => 'text', 'options' => []],

        ];
    }

    public function index(): View
    {
        $items = ProjectModality::paginate(10);
        return view('admin.parametric.index', [
            'items' => $items,
            'title' => 'Modalidades de Proyectos',
            'routePrefix' => 'admin.project-modalities',
            'fields' => $this->getFields()
        ]);
    }

    public function create(): View
    {
        return view('admin.parametric.create', [
            'title' => 'Crear Modalidades de Proyectos',
            'routePrefix' => 'admin.project-modalities',
            'fields' => $this->getFields()
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->getValidationRules());
        // Custom request logic applies if $request is not just a base Request.
        if (method_exists($request, 'validated') && 'Request' !== 'Request') {
             $validated = $request->validated();
        }
        
        ProjectModality::create($validated);

        if ($request->input('_from_simples')) {
            return redirect()->route('admin.catalogos.simples')->with('success', 'Modalidad de proyecto creada correctamente.');
        }
        return redirect()->route('admin.project-modalities.index')
            ->with('success', 'Registro creado exitosamente.');
    }

    public function edit(ProjectModality $projectmodality): View
    {
        return view('admin.parametric.edit', [
            'item' => $projectmodality,
            'title' => 'Editar Modalidades de Proyectos',
            'routePrefix' => 'admin.project-modalities',
            'fields' => $this->getFields()
        ]);
    }

    public function update(Request $request, ProjectModality $projectmodality): RedirectResponse
    {
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
        foreach(array_keys($this->getFields()) as $f) {
             $rules[$f] = 'required';
        }
        return $rules;
    }
    
    // Note: Laravel resolves model bindings. 
    // Ensure the parameter name $projectmodality matches route.
}