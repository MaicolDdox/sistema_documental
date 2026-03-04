<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\TrainingProgram;
use Illuminate\Http\Request;
use App\Http\Requests\StoreTrainingProgramRequest;
use App\Http\Requests\UpdateTrainingProgramRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TrainingProgramController extends Controller
{
    private function getFields() {
        return [
            'training_record_id' => ['type' => 'relation', 'options' => \App\Models\TrainingRecord::all()],
            'training_program_type_id' => ['type' => 'relation', 'options' => \App\Models\TrainingProgramType::all()],
            'nombre' => ['type' => 'text', 'options' => []],
            'descripccion' => ['type' => 'text', 'options' => []],
            'jornada' => ['type' => 'enum', 'options' => ['DIURNA','NOCTURNA','MIXTA','VIRTUAL']],
            'modalidad' => ['type' => 'enum', 'options' => ['PRESENCIAL','VIRTUAL','A DISTANCIA']],
            'estado' => ['type' => 'enum', 'options' => ['activo','inactivo']],

        ];
    }

    public function index(): View
    {
        $items = TrainingProgram::paginate(10);
        return view('admin.parametric.index', [
            'items' => $items,
            'title' => 'Programas de Formación',
            'routePrefix' => 'admin.training-programs',
            'fields' => $this->getFields()
        ]);
    }

    public function create(): View
    {
        return view('admin.parametric.create', [
            'title' => 'Crear Programas de Formación',
            'routePrefix' => 'admin.training-programs',
            'fields' => $this->getFields()
        ]);
    }

    public function store(StoreTrainingProgramRequest $request): RedirectResponse
    {
        $validated = $request->validate($this->getValidationRules());
        // Custom request logic applies if $request is not just a base Request.
        if (method_exists($request, 'validated') && 'StoreTrainingProgramRequest' !== 'Request') {
             $validated = $request->validated();
        }
        
        TrainingProgram::create($validated);
        
        return redirect()->route('admin.training-programs.index')
            ->with('success', 'Registro creado exitosamente.');
    }

    public function edit(TrainingProgram $trainingprogram): View
    {
        return view('admin.parametric.edit', [
            'item' => $trainingprogram,
            'title' => 'Editar Programas de Formación',
            'routePrefix' => 'admin.training-programs',
            'fields' => $this->getFields()
        ]);
    }

    public function update(UpdateTrainingProgramRequest $request, TrainingProgram $trainingprogram): RedirectResponse
    {
        $validated = $request->validate($this->getValidationRules());
        if (method_exists($request, 'validated') && 'UpdateTrainingProgramRequest' !== 'Request') {
             $validated = $request->validated();
        }
        
        $trainingprogram->update($validated);
        
        return redirect()->route('admin.training-programs.index')
            ->with('success', 'Registro actualizado exitosamente.');
    }

    public function destroy(TrainingProgram $trainingprogram): RedirectResponse
    {
        try {
            $trainingprogram->delete();
            return redirect()->route('admin.training-programs.index')
                ->with('success', 'Registro eliminado exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('admin.training-programs.index')
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
    // Ensure the parameter name $trainingprogram matches route.
}