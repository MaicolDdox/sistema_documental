<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;
use App\Http\Requests\StoreCityRequest;
use App\Http\Requests\UpdateCityRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CityController extends Controller
{
    private function getFields() {
        return [
            'department_id' => ['type' => 'relation', 'options' => \App\Models\Department::all()],
            'nombre' => ['type' => 'text', 'options' => []],

        ];
    }

    public function index(): View
    {
        $items = City::paginate(10);
        return view('admin.parametric.index', [
            'items' => $items,
            'title' => 'Municipios',
            'routePrefix' => 'admin.cities',
            'fields' => $this->getFields()
        ]);
    }

    public function create(): View
    {
        return view('admin.parametric.create', [
            'title' => 'Crear Municipios',
            'routePrefix' => 'admin.cities',
            'fields' => $this->getFields()
        ]);
    }

    public function store(StoreCityRequest $request): RedirectResponse
    {
        $validated = $request->validate($this->getValidationRules());
        // Custom request logic applies if $request is not just a base Request.
        if (method_exists($request, 'validated') && 'StoreCityRequest' !== 'Request') {
             $validated = $request->validated();
        }
        
        City::create($validated);
        
        return redirect()->route('admin.cities.index')
            ->with('success', 'Registro creado exitosamente.');
    }

    public function edit(City $city): View
    {
        return view('admin.parametric.edit', [
            'item' => $city,
            'title' => 'Editar Municipios',
            'routePrefix' => 'admin.cities',
            'fields' => $this->getFields()
        ]);
    }

    public function update(UpdateCityRequest $request, City $city): RedirectResponse
    {
        $validated = $request->validate($this->getValidationRules());
        if (method_exists($request, 'validated') && 'UpdateCityRequest' !== 'Request') {
             $validated = $request->validated();
        }
        
        $city->update($validated);
        
        return redirect()->route('admin.cities.index')
            ->with('success', 'Registro actualizado exitosamente.');
    }

    public function destroy(City $city): RedirectResponse
    {
        try {
            $city->delete();
            return redirect()->route('admin.cities.index')
                ->with('success', 'Registro eliminado exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('admin.cities.index')
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
    // Ensure the parameter name $city matches route.
}