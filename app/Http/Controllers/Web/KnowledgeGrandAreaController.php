<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeGrandArea;
use Illuminate\Http\Request;


use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class KnowledgeGrandAreaController extends Controller
{
    private function getFields() {
        return [
            'nombre' => ['type' => 'text', 'options' => []],
            'descripccion' => ['type' => 'text', 'options' => []],

        ];
    }

    public function index(): View
    {
        $items = KnowledgeGrandArea::paginate(10);
        return view('admin.parametric.index', [
            'items' => $items,
            'title' => 'Grandes Áreas de Conocimiento',
            'routePrefix' => 'admin.knowledge-grand-areas',
            'fields' => $this->getFields()
        ]);
    }

    public function create(): View
    {
        return view('admin.parametric.create', [
            'title' => 'Crear Grandes Áreas de Conocimiento',
            'routePrefix' => 'admin.knowledge-grand-areas',
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
        
        KnowledgeGrandArea::create($validated);
        
        return redirect()->route('admin.knowledge-grand-areas.index')
            ->with('success', 'Registro creado exitosamente.');
    }

    public function edit(KnowledgeGrandArea $knowledgegrandarea): View
    {
        return view('admin.parametric.edit', [
            'item' => $knowledgegrandarea,
            'title' => 'Editar Grandes Áreas de Conocimiento',
            'routePrefix' => 'admin.knowledge-grand-areas',
            'fields' => $this->getFields()
        ]);
    }

    public function update(Request $request, KnowledgeGrandArea $knowledgegrandarea): RedirectResponse
    {
        $validated = $request->validate($this->getValidationRules());
        if (method_exists($request, 'validated') && 'Request' !== 'Request') {
             $validated = $request->validated();
        }
        
        $knowledgegrandarea->update($validated);
        
        return redirect()->route('admin.knowledge-grand-areas.index')
            ->with('success', 'Registro actualizado exitosamente.');
    }

    public function destroy(KnowledgeGrandArea $knowledgegrandarea): RedirectResponse
    {
        try {
            $knowledgegrandarea->delete();
            return redirect()->route('admin.knowledge-grand-areas.index')
                ->with('success', 'Registro eliminado exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('admin.knowledge-grand-areas.index')
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
    // Ensure the parameter name $knowledgegrandarea matches route.
}