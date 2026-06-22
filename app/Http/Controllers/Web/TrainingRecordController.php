<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\TrainingRecord;
use Illuminate\Http\Request;


use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TrainingRecordController extends Controller
{
    private function getFields() {
        return [
            'codigo' => ['type' => 'text', 'options' => []],
            'descripcion' => ['type' => 'text', 'options' => []],

        ];
    }

    public function index(): View
    {
        $items = TrainingRecord::paginate(10);
        return view('admin.parametric.index', [
            'items' => $items,
            'title' => 'Fichas de Formación',
            'routePrefix' => 'admin.training-records',
            'fields' => $this->getFields()
        ]);
    }

    public function create(): View
    {
        return view('admin.parametric.create', [
            'title' => 'Crear Fichas de Formación',
            'routePrefix' => 'admin.training-records',
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
        
        TrainingRecord::create($validated);
        
        return redirect()->route('admin.training-records.index')
            ->with('success', 'Registro creado exitosamente.');
    }

    public function edit(TrainingRecord $trainingrecord): View
    {
        return view('admin.parametric.edit', [
            'item' => $trainingrecord,
            'title' => 'Editar Fichas de Formación',
            'routePrefix' => 'admin.training-records',
            'fields' => $this->getFields()
        ]);
    }

    public function update(Request $request, TrainingRecord $trainingrecord): RedirectResponse
    {
        $validated = $request->validate($this->getValidationRules());
        if (method_exists($request, 'validated') && 'Request' !== 'Request') {
             $validated = $request->validated();
        }
        
        $trainingrecord->update($validated);
        
        return redirect()->route('admin.training-records.index')
            ->with('success', 'Registro actualizado exitosamente.');
    }

    public function destroy(TrainingRecord $trainingrecord): RedirectResponse
    {
        try {
            $trainingrecord->delete();
            return redirect()->route('admin.training-records.index')
                ->with('success', 'Registro eliminado exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('admin.training-records.index')
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
    // Ensure the parameter name $trainingrecord matches route.
}