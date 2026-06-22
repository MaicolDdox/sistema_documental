<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ResearchLine;
use Illuminate\Http\Request;
use App\Http\Requests\StoreResearchLineRequest;
use App\Http\Requests\UpdateResearchLineRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ResearchLineController extends Controller
{
    private function getFields() {
        return [
            'nombre' => ['type' => 'text', 'options' => []],
            'descripcion' => ['type' => 'text', 'options' => []],

        ];
    }

    public function index(): View
    {
        $items = ResearchLine::paginate(10);
        return view('admin.parametric.index', [
            'items' => $items,
            'title' => 'Líneas de Investigación',
            'routePrefix' => 'admin.research-lines',
            'fields' => $this->getFields()
        ]);
    }

    public function create(): View
    {
        return view('admin.parametric.create', [
            'title' => 'Crear Líneas de Investigación',
            'routePrefix' => 'admin.research-lines',
            'fields' => $this->getFields()
        ]);
    }

    public function store(StoreResearchLineRequest $request): RedirectResponse
    {
        $validated = $request->validate($this->getValidationRules());
        // Custom request logic applies if $request is not just a base Request.
        if (method_exists($request, 'validated') && 'StoreResearchLineRequest' !== 'Request') {
             $validated = $request->validated();
        }
        
        ResearchLine::create($validated);
        
        return redirect()->route('admin.research-lines.index')
            ->with('success', 'Registro creado exitosamente.');
    }

    public function edit(ResearchLine $researchline): View
    {
        return view('admin.parametric.edit', [
            'item' => $researchline,
            'title' => 'Editar Líneas de Investigación',
            'routePrefix' => 'admin.research-lines',
            'fields' => $this->getFields()
        ]);
    }

    public function update(UpdateResearchLineRequest $request, ResearchLine $researchline): RedirectResponse
    {
        $validated = $request->validate($this->getValidationRules());
        if (method_exists($request, 'validated') && 'UpdateResearchLineRequest' !== 'Request') {
             $validated = $request->validated();
        }
        
        $researchline->update($validated);
        
        return redirect()->route('admin.research-lines.index')
            ->with('success', 'Registro actualizado exitosamente.');
    }

    public function destroy(ResearchLine $researchline): RedirectResponse
    {
        try {
            $researchline->delete();
            return redirect()->route('admin.research-lines.index')
                ->with('success', 'Registro eliminado exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('admin.research-lines.index')
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
    // Ensure the parameter name $researchline matches route.
}