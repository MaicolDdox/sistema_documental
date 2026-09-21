<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTechnologicalLineRequest;
use App\Http\Requests\UpdateTechnologicalLineRequest;
use App\Models\TechnologicalLine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TechnologicalLineController extends Controller
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

        $items = TechnologicalLine::where('training_center_id', Auth::user()->training_center_id)->paginate(10);

        return view('admin.parametric.index', [
            'items' => $items,
            'title' => 'Líneas Tecnológicas',
            'routePrefix' => 'admin.technological-lines',
            'fields' => $this->getFields(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('catalogos.crear');

        return view('admin.parametric.create', [
            'title' => 'Crear Líneas Tecnológicas',
            'routePrefix' => 'admin.technological-lines',
            'fields' => $this->getFields(),
        ]);
    }

    public function store(StoreTechnologicalLineRequest $request): RedirectResponse
    {
        $this->authorize('catalogos.crear');

        $validated = $request->validate($this->getValidationRules());
        // Custom request logic applies if $request is not just a base Request.
        if (method_exists($request, 'validated') && 'StoreTechnologicalLineRequest' !== 'Request') {
            $validated = $request->validated();
        }

        $validated['training_center_id'] = Auth::user()->training_center_id;
        TechnologicalLine::create($validated);

        if ($request->input('_from_simples')) {
            return redirect()->route('admin.catalogos.simples')->with('success', 'Línea tecnológica creada correctamente.');
        }

        return redirect()->route('admin.technological-lines.index')
            ->with('success', 'Registro creado exitosamente.');
    }

    public function edit(TechnologicalLine $technologicalline): View
    {
        $this->authorize('catalogos.editar');

        $this->checkCentroFormacion($technologicalline);

        return view('admin.parametric.edit', [
            'item' => $technologicalline,
            'title' => 'Editar Líneas Tecnológicas',
            'routePrefix' => 'admin.technological-lines',
            'fields' => $this->getFields(),
        ]);
    }

    public function update(UpdateTechnologicalLineRequest $request, TechnologicalLine $technologicalline): RedirectResponse
    {
        $this->authorize('catalogos.editar');

        $this->checkCentroFormacion($technologicalline);

        $validated = $request->validate($this->getValidationRules());
        if (method_exists($request, 'validated') && 'UpdateTechnologicalLineRequest' !== 'Request') {
            $validated = $request->validated();
        }

        $technologicalline->update($validated);

        if ($request->has('_from_simples')) {
            return redirect()->route('admin.catalogos.simples')
                ->with('success', 'Registro actualizado exitosamente.');
        }

        return redirect()->route('admin.technological-lines.index')
            ->with('success', 'Registro actualizado exitosamente.');
    }

    public function destroy(TechnologicalLine $technologicalline): RedirectResponse
    {
        $this->authorize('catalogos.eliminar');

        $this->checkCentroFormacion($technologicalline);

        try {
            $technologicalline->delete();

            return redirect()->route('admin.technological-lines.index')
                ->with('success', 'Registro eliminado exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('admin.technological-lines.index')
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

    private function checkCentroFormacion(TechnologicalLine $item): void
    {
        if ((int) $item->training_center_id !== (int) Auth::user()->training_center_id) {
            abort(403, 'No tienes permiso para gestionar catálogos de otros centros de formación.');
        }
    }

    // Note: Laravel resolves model bindings.
    // Ensure the parameter name $technologicalline matches route.
}
