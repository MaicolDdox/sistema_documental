<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeArea;
use Illuminate\Http\Request;


use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class KnowledgeAreaController extends Controller
{
    private function getFields() {
        return [
            'knowledge_grand_area_id' => ['type' => 'relation', 'options' => \App\Models\KnowledgeGrandArea::all()],
            'nombre' => ['type' => 'text', 'options' => []],
            'descripccion' => ['type' => 'text', 'options' => []],

        ];
    }

    public function index(): View
    {
        $items = KnowledgeArea::paginate(10);
        return view('admin.parametric.index', [
            'items' => $items,
            'title' => 'Áreas de Conocimiento',
            'routePrefix' => 'admin.knowledge-areas',
            'fields' => $this->getFields()
        ]);
    }

    public function create(): View
    {
        return view('admin.parametric.create', [
            'title' => 'Crear Áreas de Conocimiento',
            'routePrefix' => 'admin.knowledge-areas',
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
        
        KnowledgeArea::create($validated);
        
        return redirect()->route('admin.knowledge-areas.index')
            ->with('success', 'Registro creado exitosamente.');
    }

    public function edit(KnowledgeArea $knowledgearea): View
    {
        return view('admin.parametric.edit', [
            'item' => $knowledgearea,
            'title' => 'Editar Áreas de Conocimiento',
            'routePrefix' => 'admin.knowledge-areas',
            'fields' => $this->getFields()
        ]);
    }

    public function update(Request $request, KnowledgeArea $knowledgearea): RedirectResponse
    {
        $validated = $request->validate($this->getValidationRules());
        if (method_exists($request, 'validated') && 'Request' !== 'Request') {
             $validated = $request->validated();
        }
        
        $knowledgearea->update($validated);
        
        return redirect()->route('admin.knowledge-areas.index')
            ->with('success', 'Registro actualizado exitosamente.');
    }

    public function destroy(KnowledgeArea $knowledgearea): RedirectResponse
    {
        try {
            $knowledgearea->delete();
            return redirect()->route('admin.knowledge-areas.index')
                ->with('success', 'Registro eliminado exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('admin.knowledge-areas.index')
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
    // Ensure the parameter name $knowledgearea matches route.
}