<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    private function getFields() {
        return [
            'nombre' => ['type' => 'text', 'options' => []],

        ];
    }

    public function index(): View
    {
        $items = Department::paginate(10);
        return view('admin.parametric.index', [
            'items' => $items,
            'title' => 'Departamentos',
            'routePrefix' => 'admin.departments',
            'fields' => $this->getFields()
        ]);
    }

    public function create(): View
    {
        return view('admin.parametric.create', [
            'title' => 'Crear Departamentos',
            'routePrefix' => 'admin.departments',
            'fields' => $this->getFields()
        ]);
    }

    public function store(StoreDepartmentRequest $request): RedirectResponse
    {
        $validated = $request->validate($this->getValidationRules());
        // Custom request logic applies if $request is not just a base Request.
        if (method_exists($request, 'validated') && 'StoreDepartmentRequest' !== 'Request') {
             $validated = $request->validated();
        }
        
        Department::create($validated);
        
        return redirect()->route('admin.departments.index')
            ->with('success', 'Registro creado exitosamente.');
    }

    public function edit(Department $department): View
    {
        return view('admin.parametric.edit', [
            'item' => $department,
            'title' => 'Editar Departamentos',
            'routePrefix' => 'admin.departments',
            'fields' => $this->getFields()
        ]);
    }

    public function update(UpdateDepartmentRequest $request, Department $department): RedirectResponse
    {
        $validated = $request->validate($this->getValidationRules());
        if (method_exists($request, 'validated') && 'UpdateDepartmentRequest' !== 'Request') {
             $validated = $request->validated();
        }
        
        $department->update($validated);
        
        return redirect()->route('admin.departments.index')
            ->with('success', 'Registro actualizado exitosamente.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        try {
            $department->delete();
            return redirect()->route('admin.departments.index')
                ->with('success', 'Registro eliminado exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('admin.departments.index')
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
    // Ensure the parameter name $department matches route.
}