<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\TechnologicalLine;
use Illuminate\Http\Request;
use App\Http\Requests\StoreTechnologicalLineRequest;
use App\Http\Requests\UpdateTechnologicalLineRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TechnologicalLineController extends Controller
{
    private function getFields() {
        return [
            'nombre' => ['type' => 'text', 'options' => []],
            'descripccion' => ['type' => 'text', 'options' => []],

        ];
    }

    public function index(): View
    {
        $items = TechnologicalLine::paginate(10);
        return view('admin.parametric.index', [
            'items' => $items,
            'title' => 'Líneas Tecnológicas',
            'routePrefix' => 'admin.technological-lines',
            'fields' => $this->getFields()
        ]);
    }

    public function create(): View
    {
        return view('admin.parametric.create', [
            'title' => 'Crear Líneas Tecnológicas',
            'routePrefix' => 'admin.technological-lines',
            'fields' => $this->getFields()
        ]);
    }

    public function store(StoreTechnologicalLineRequest $request): RedirectResponse
    {
        $validated = $request->validate($this->getValidationRules());
        // Custom request logic applies if $request is not just a base Request.
        if (method_exists($request, 'validated') && 'StoreTechnologicalLineRequest' !== 'Request') {
             $validated = $request->validated();
        }
        
        TechnologicalLine::create($validated);

        if ($request->input('_from_simples')) {
            return redirect()->route('admin.catalogos.simples')->with('success', 'Línea tecnológica creada correctamente.');
        }
        return redirect()->route('admin.technological-lines.index')
            ->with('success', 'Registro creado exitosamente.');
    }

    public function edit(TechnologicalLine $technologicalline): View
    {
        return view('admin.parametric.edit', [
            'item' => $technologicalline,
            'title' => 'Editar Líneas Tecnológicas',
            'routePrefix' => 'admin.technological-lines',
            'fields' => $this->getFields()
        ]);
    }

    public function update(UpdateTechnologicalLineRequest $request, TechnologicalLine $technologicalline): RedirectResponse
    {
        $validated = $request->validate($this->getValidationRules());
        if (method_exists($request, 'validated') && 'UpdateTechnologicalLineRequest' !== 'Request') {
             $validated = $request->validated();
        }
        
        $technologicalline->update($validated);

        if ($request->has('_from_simples')) {
            return redirect()->route('admin.catalogos.simples')
                ->with('success', 'Registro actualizado exitosamente.');
        }
        return redirect()->route('admin.technological-lines.index')
            ->with('success', 'Registro actualizado exitosamente.');
    }

    public function destroy(TechnologicalLine $technologicalline): RedirectResponse
    {
        try {
            $technologicalline->delete();
            return redirect()->route('admin.technological-lines.index')
                ->with('success', 'Registro eliminado exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('admin.technological-lines.index')
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
    // Ensure the parameter name $technologicalline matches route.
}