<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreThematicAreaRequest;
use App\Http\Requests\UpdateThematicAreaRequest;
use App\Models\ThematicArea;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ThematicAreaController extends Controller
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

        $items = ThematicArea::where('training_center_id', Auth::user()->training_center_id)->paginate(10);

        return view('admin.parametric.index', [
            'items' => $items,
            'title' => 'Áreas Temáticas',
            'routePrefix' => 'admin.thematic-areas',
            'fields' => $this->getFields(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('catalogos.crear');

        return view('admin.parametric.create', [
            'title' => 'Crear Áreas Temáticas',
            'routePrefix' => 'admin.thematic-areas',
            'fields' => $this->getFields(),
        ]);
    }

    public function store(StoreThematicAreaRequest $request): RedirectResponse
    {
        $this->authorize('catalogos.crear');

        $validated = $request->validate($this->getValidationRules());
        // Custom request logic applies if $request is not just a base Request.
        if (method_exists($request, 'validated') && 'StoreThematicAreaRequest' !== 'Request') {
            $validated = $request->validated();
        }

        $validated['training_center_id'] = Auth::user()->training_center_id;
        ThematicArea::create($validated);

        if ($request->input('_from_simples')) {
            return redirect()->route('admin.catalogos.simples')->with('success', 'Área temática creada correctamente.');
        }

        return redirect()->route('admin.thematic-areas.index')
            ->with('success', 'Registro creado exitosamente.');
    }

    public function edit(ThematicArea $thematicarea): View
    {
        $this->authorize('catalogos.editar');

        $this->checkCentroFormacion($thematicarea);

        return view('admin.parametric.edit', [
            'item' => $thematicarea,
            'title' => 'Editar Áreas Temáticas',
            'routePrefix' => 'admin.thematic-areas',
            'fields' => $this->getFields(),
        ]);
    }

    public function update(UpdateThematicAreaRequest $request, ThematicArea $thematicarea): RedirectResponse
    {
        $this->authorize('catalogos.editar');

        $this->checkCentroFormacion($thematicarea);

        $validated = $request->validate($this->getValidationRules());
        if (method_exists($request, 'validated') && 'UpdateThematicAreaRequest' !== 'Request') {
            $validated = $request->validated();
        }

        $thematicarea->update($validated);

        if ($request->has('_from_simples')) {
            return redirect()->route('admin.catalogos.simples')
                ->with('success', 'Registro actualizado exitosamente.');
        }

        return redirect()->route('admin.thematic-areas.index')
            ->with('success', 'Registro actualizado exitosamente.');
    }

    public function destroy(ThematicArea $thematicarea): RedirectResponse
    {
        $this->authorize('catalogos.eliminar');

        $this->checkCentroFormacion($thematicarea);

        try {
            $thematicarea->delete();

            return redirect()->route('admin.thematic-areas.index')
                ->with('success', 'Registro eliminado exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('admin.thematic-areas.index')
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

    private function checkCentroFormacion(ThematicArea $item): void
    {
        if ((int) $item->training_center_id !== (int) Auth::user()->training_center_id) {
            abort(403, 'No tienes permiso para gestionar catálogos de otros centros de formación.');
        }
    }

    // Note: Laravel resolves model bindings.
    // Ensure the parameter name $thematicarea matches route.
}
