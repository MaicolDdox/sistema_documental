<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEntityPositionRequest;
use App\Http\Requests\UpdateEntityPositionRequest;
use App\Models\EntityPosition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EntityPositionController extends Controller
{
    private function getFields()
    {
        return [
            'nombre' => ['type' => 'text', 'options' => []],
            'descripcion' => ['type' => 'text', 'options' => []],

        ];
    }

    public function index(): View
    {
        $this->authorize('catalogos.leer');

        $items = EntityPosition::where('training_center_id', Auth::user()->training_center_id)->paginate(10);

        return view('admin.parametric.index', [
            'items' => $items,
            'title' => 'Cargo / Posición',
            'routePrefix' => 'admin.entity-positions',
            'fields' => $this->getFields(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('catalogos.crear');

        return view('admin.parametric.create', [
            'title' => 'Crear Cargo / Posición',
            'routePrefix' => 'admin.entity-positions',
            'fields' => $this->getFields(),
        ]);
    }

    public function store(StoreEntityPositionRequest $request): RedirectResponse
    {
        $this->authorize('catalogos.crear');

        $validated = $request->validate($this->getValidationRules());
        // Custom request logic applies if $request is not just a base Request.
        if (method_exists($request, 'validated') && 'StoreEntityPositionRequest' !== 'Request') {
            $validated = $request->validated();
        }

        $validated['training_center_id'] = Auth::user()->training_center_id;
        EntityPosition::create($validated);

        if ($request->input('_from_simples')) {
            return redirect()->route('admin.catalogos.simples')->with('success', 'Cargo / Posición creado correctamente.');
        }

        return redirect()->route('admin.entity-positions.index')
            ->with('success', 'Registro creado exitosamente.');
    }

    public function edit(EntityPosition $entityposition): View
    {
        $this->authorize('catalogos.editar');

        $this->checkCentroFormacion($entityposition);

        return view('admin.parametric.edit', [
            'item' => $entityposition,
            'title' => 'Editar Cargo / Posición',
            'routePrefix' => 'admin.entity-positions',
            'routeParam' => 'entity_position',
            'fields' => $this->getFields(),
        ]);
    }

    public function update(UpdateEntityPositionRequest $request, EntityPosition $entityposition): RedirectResponse
    {
        $this->authorize('catalogos.editar');

        $this->checkCentroFormacion($entityposition);

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
        $this->authorize('catalogos.eliminar');

        $this->checkCentroFormacion($entityposition);

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
        foreach (array_keys($this->getFields()) as $f) {
            $rules[$f] = 'required';
        }

        return $rules;
    }

    private function checkCentroFormacion(EntityPosition $item): void
    {
        if ((int) $item->training_center_id !== (int) Auth::user()->training_center_id) {
            abort(403, 'No tienes permiso para gestionar catálogos de otros centros de formación.');
        }
    }

    // Note: Laravel resolves model bindings.
    // Ensure the parameter name $entityposition matches route.
}
