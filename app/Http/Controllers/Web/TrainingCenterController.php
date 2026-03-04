<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\TrainingCenter;
use Illuminate\Http\Request;
use App\Http\Requests\StoreTrainingCenterRequest;
use App\Http\Requests\UpdateTrainingCenterRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TrainingCenterController extends Controller
{
    private function getFields() {
        return [
            'nombre' => ['type' => 'text', 'options' => []],
            'codigo' => ['type' => 'text', 'options' => []],
            'department_id' => ['type' => 'relation', 'options' => \App\Models\Department::all()],
            'city_id' => ['type' => 'relation', 'options' => \App\Models\City::all()],

        ];
    }

    public function index(): View
    {
        $items = TrainingCenter::paginate(10);
        return view('admin.parametric.index', [
            'items' => $items,
            'title' => 'Centros de Formación',
            'routePrefix' => 'admin.training-centers',
            'fields' => $this->getFields()
        ]);
    }

    public function create(): View
    {
        return view('admin.parametric.create', [
            'title' => 'Crear Centros de Formación',
            'routePrefix' => 'admin.training-centers',
            'fields' => $this->getFields()
        ]);
    }

    public function store(StoreTrainingCenterRequest $request): RedirectResponse
    {
        $validated = $request->validate($this->getValidationRules());
        // Custom request logic applies if $request is not just a base Request.
        if (method_exists($request, 'validated') && 'StoreTrainingCenterRequest' !== 'Request') {
             $validated = $request->validated();
        }
        
        TrainingCenter::create($validated);
        
        return redirect()->route('admin.training-centers.index')
            ->with('success', 'Registro creado exitosamente.');
    }

    public function edit(TrainingCenter $trainingcenter): View
    {
        return view('admin.parametric.edit', [
            'item' => $trainingcenter,
            'title' => 'Editar Centros de Formación',
            'routePrefix' => 'admin.training-centers',
            'fields' => $this->getFields()
        ]);
    }

    public function update(UpdateTrainingCenterRequest $request, TrainingCenter $trainingcenter): RedirectResponse
    {
        $validated = $request->validate($this->getValidationRules());
        if (method_exists($request, 'validated') && 'UpdateTrainingCenterRequest' !== 'Request') {
             $validated = $request->validated();
        }
        
        $trainingcenter->update($validated);
        
        return redirect()->route('admin.training-centers.index')
            ->with('success', 'Registro actualizado exitosamente.');
    }

    public function destroy(TrainingCenter $trainingcenter): RedirectResponse
    {
        try {
            $trainingcenter->delete();
            return redirect()->route('admin.training-centers.index')
                ->with('success', 'Registro eliminado exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('admin.training-centers.index')
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
    // Ensure the parameter name $trainingcenter matches route.
}