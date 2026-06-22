<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\InvestigationType;
use Illuminate\Http\Request;


use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InvestigationTypeController extends Controller
{
    private function getFields() {
        return [
            'nombre' => ['type' => 'text', 'options' => []],
            'descripcion' => ['type' => 'text', 'options' => []],

        ];
    }

    public function index(): View
    {
        $items = InvestigationType::paginate(10);
        return view('admin.parametric.index', [
            'items' => $items,
            'title' => 'Tipos de Investigadores',
            'routePrefix' => 'admin.investigation-types',
            'fields' => $this->getFields()
        ]);
    }

    public function create(): View
    {
        return view('admin.parametric.create', [
            'title' => 'Crear Tipos de Investigadores',
            'routePrefix' => 'admin.investigation-types',
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
        
        InvestigationType::create($validated);

        if ($request->input('_from_simples')) {
            return redirect()->route('admin.catalogos.simples')->with('success', 'Tipo de investigación creado correctamente.');
        }
        return redirect()->route('admin.investigation-types.index')
            ->with('success', 'Registro creado exitosamente.');
    }

    public function edit(InvestigationType $investigationtype): View
    {
        return view('admin.parametric.edit', [
            'item' => $investigationtype,
            'title' => 'Editar Tipos de Investigadores',
            'routePrefix' => 'admin.investigation-types',
            'fields' => $this->getFields()
        ]);
    }

    public function update(Request $request, InvestigationType $investigationtype): RedirectResponse
    {
        $validated = $request->validate($this->getValidationRules());
        if (method_exists($request, 'validated') && 'Request' !== 'Request') {
             $validated = $request->validated();
        }
        
        $investigationtype->update($validated);

        if ($request->has('_from_simples')) {
            return redirect()->route('admin.catalogos.simples')
                ->with('success', 'Registro actualizado exitosamente.');
        }
        return redirect()->route('admin.investigation-types.index')
            ->with('success', 'Registro actualizado exitosamente.');
    }

    public function destroy(InvestigationType $investigationtype): RedirectResponse
    {
        try {
            $investigationtype->delete();
            return redirect()->route('admin.investigation-types.index')
                ->with('success', 'Registro eliminado exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('admin.investigation-types.index')
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
    // Ensure the parameter name $investigationtype matches route.
}