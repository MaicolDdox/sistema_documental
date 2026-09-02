<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\EntityPosition;
use Illuminate\Http\Request;
use App\Http\Requests\StoreEntityPositionRequest;
use App\Http\Requests\UpdateEntityPositionRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EntityPositionController extends Controller
{
    private function getFields() {
        return [
            'nombre' => ['type' => 'text', 'options' => []],
            'descripcion' => ['type' => 'text', 'options' => []],

        ];
    }

    public function index(): View
    {
        $items = EntityPosition::paginate(10);
        return view('admin.parametric.index', [
            'items' => $items,
            'title' => 'Cargo / Posición',
            'routePrefix' => 'admin.entity-positions',
            'fields' => $this->getFields()
        ]);
    }

    public function create(): View
    {
        return view('admin.parametric.create', [
            'title' => 'Crear Cargo / Posición',
            'routePrefix' => 'admin.entity-positions',
            'fields' => $this->getFields()
        ]);
    }

    public function store(StoreEntityPositionRequest $request): RedirectResponse
    {
        $validated = $request->validate($this->getValidationRules());
        // Custom request logic applies if $request is not just a base Request.
        if (method_exists($request, 'validated') && 'StoreEntityPositionRequest' !== 'Request') {
             $validated = $request->validated();
        }
        
        EntityPosition::create($validated);

        if ($request->input('_from_simples')) {
            return redirect()->route('admin.catalogos.simples')->with('success', 'Cargo / Posición creado correctamente.');
        }
        return redirect()->route('admin.entity-positions.index')
            ->with('success', 'Registro creado exitosamente.');
    }

    public function edit(EntityPosition $entityposition): View
    {
        return view('admin.parametric.edit', [
            'item' => $entityposition,
            'title' => 'Editar Cargo / Posición',
            'routePrefix' => 'admin.entity-positions',
            'routeParam' => 'entity_position',
            'fields' => $this->getFields()
        ]);
    }

    public function update(UpdateEntityPositionRequest $request, EntityPosition $entityposition): RedirectResponse
    {
        $validated = $request->validate($this->getValidationRules());
        if (method_exists($request, 'validated') && 'UpdateEntityPositionRequest' !== 'Request') {
             $validated = $request->validated();
        }
        
        $entityposition->update($validated);

        if ($request->has('_from_simples')) {
            return redirect()->route('admin.catalogos.simples')
                ->with('success', 'Registro actualizado exitosamente.');
        }
        return redirect()->route('admin.entity-positions.index')
            ->with('success', 'Registro actualizado exitosamente.');
    }

    public function destroy(EntityPosition $entityposition): RedirectResponse
    {
        try {
            $entityposition->delete();
            return redirect()->route('admin.entity-positions.index')
                ->with('success', 'Registro eliminado exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('admin.entity-positions.index')
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
    // Ensure the parameter name $entityposition matches route.
}