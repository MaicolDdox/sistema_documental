<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\TrainingProgramType;
use Illuminate\Http\Request;


use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TrainingProgramTypeController extends Controller
{
    private function getFields() {
        return [
            'nombre' => ['type' => 'text', 'options' => []],
            'descripcion' => ['type' => 'text', 'options' => []],

        ];
    }

    public function index(): View
    {
        $items = TrainingProgramType::paginate(10);
        return view('admin.parametric.index', [
            'items' => $items,
            'title' => 'Tipos de Programas',
            'routePrefix' => 'admin.training-program-types',
            'fields' => $this->getFields()
        ]);
    }

    public function create(): View
    {
        return view('admin.parametric.create', [
            'title' => 'Crear Tipos de Programas',
            'routePrefix' => 'admin.training-program-types',
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
        
        TrainingProgramType::create($validated);
        
        return redirect()->route('admin.training-program-types.index')
            ->with('success', 'Registro creado exitosamente.');
    }

    public function edit(TrainingProgramType $trainingprogramtype): View
    {
        return view('admin.parametric.edit', [
            'item' => $trainingprogramtype,
            'title' => 'Editar Tipos de Programas',
            'routePrefix' => 'admin.training-program-types',
            'fields' => $this->getFields()
        ]);
    }

    public function update(Request $request, TrainingProgramType $trainingprogramtype): RedirectResponse
    {
        $validated = $request->validate($this->getValidationRules());
        if (method_exists($request, 'validated') && 'Request' !== 'Request') {
             $validated = $request->validated();
        }
        
        $trainingprogramtype->update($validated);
        
        return redirect()->route('admin.training-program-types.index')
            ->with('success', 'Registro actualizado exitosamente.');
    }

    public function destroy(TrainingProgramType $trainingprogramtype): RedirectResponse
    {
        try {
            $trainingprogramtype->delete();
            return redirect()->route('admin.training-program-types.index')
                ->with('success', 'Registro eliminado exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('admin.training-program-types.index')
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
    // Ensure the parameter name $trainingprogramtype matches route.
}