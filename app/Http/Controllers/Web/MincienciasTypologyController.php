<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MincienciasTypology;
use Illuminate\Http\Request;


use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MincienciasTypologyController extends Controller
{
    private function getFields() {
        return [
            'nombre' => ['type' => 'text', 'options' => []],
            'codigo' => ['type' => 'text', 'options' => []],
            'descripccion' => ['type' => 'text', 'options' => []],

        ];
    }

    public function index(): View
    {
        $items = MincienciasTypology::paginate(10);
        return view('admin.parametric.index', [
            'items' => $items,
            'title' => 'Tipologías Minciencias',
            'routePrefix' => 'admin.minciencias-typologies',
            'fields' => $this->getFields()
        ]);
    }

    public function create(): View
    {
        return view('admin.parametric.create', [
            'title' => 'Crear Tipologías Minciencias',
            'routePrefix' => 'admin.minciencias-typologies',
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
        
        MincienciasTypology::create($validated);
        
        return redirect()->route('admin.minciencias-typologies.index')
            ->with('success', 'Registro creado exitosamente.');
    }

    public function edit(MincienciasTypology $mincienciastypology): View
    {
        return view('admin.parametric.edit', [
            'item' => $mincienciastypology,
            'title' => 'Editar Tipologías Minciencias',
            'routePrefix' => 'admin.minciencias-typologies',
            'fields' => $this->getFields()
        ]);
    }

    public function update(Request $request, MincienciasTypology $mincienciastypology): RedirectResponse
    {
        $validated = $request->validate($this->getValidationRules());
        if (method_exists($request, 'validated') && 'Request' !== 'Request') {
             $validated = $request->validated();
        }
        
        $mincienciastypology->update($validated);
        
        return redirect()->route('admin.minciencias-typologies.index')
            ->with('success', 'Registro actualizado exitosamente.');
    }

    public function destroy(MincienciasTypology $mincienciastypology): RedirectResponse
    {
        try {
            $mincienciastypology->delete();
            return redirect()->route('admin.minciencias-typologies.index')
                ->with('success', 'Registro eliminado exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('admin.minciencias-typologies.index')
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
    // Ensure the parameter name $mincienciastypology matches route.
}