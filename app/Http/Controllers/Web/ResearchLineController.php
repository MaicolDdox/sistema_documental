<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreResearchLineRequest;
use App\Http\Requests\UpdateResearchLineRequest;
use App\Models\ResearchLine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ResearchLineController extends Controller
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

        $items = ResearchLine::where('training_center_id', Auth::user()->training_center_id)->paginate(10);

        return view('admin.parametric.index', [
            'items' => $items,
            'title' => 'Líneas de Investigación',
            'routePrefix' => 'admin.research-lines',
            'fields' => $this->getFields(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('catalogos.crear');

        return view('admin.parametric.create', [
            'title' => 'Crear Líneas de Investigación',
            'routePrefix' => 'admin.research-lines',
            'fields' => $this->getFields(),
        ]);
    }

    public function store(StoreResearchLineRequest $request): RedirectResponse
    {
        $this->authorize('catalogos.crear');

        $validated = $request->validate($this->getValidationRules());
        // Custom request logic applies if $request is not just a base Request.
        if (method_exists($request, 'validated') && 'StoreResearchLineRequest' !== 'Request') {
            $validated = $request->validated();
        }

        $validated['training_center_id'] = Auth::user()->training_center_id;
        ResearchLine::create($validated);

        if ($request->input('_from_simples')) {
            return redirect()->route('admin.catalogos.simples')->with('success', 'Línea de investigación creada correctamente.');
        }

        return redirect()->route('admin.research-lines.index')
            ->with('success', 'Registro creado exitosamente.');
    }

    public function edit(ResearchLine $researchline): View
    {
        $this->authorize('catalogos.editar');

        $this->checkCentroFormacion($researchline);

        return view('admin.parametric.edit', [
            'item' => $researchline,
            'title' => 'Editar Líneas de Investigación',
            'routePrefix' => 'admin.research-lines',
            'fields' => $this->getFields(),
        ]);
    }

    public function update(UpdateResearchLineRequest $request, ResearchLine $researchline): RedirectResponse
    {
        $this->authorize('catalogos.editar');

        $this->checkCentroFormacion($researchline);

        $validated = $request->validate($this->getValidationRules());
        if (method_exists($request, 'validated') && 'UpdateResearchLineRequest' !== 'Request') {
            $validated = $request->validated();
        }

        $researchline->update($validated);

        if ($request->has('_from_simples')) {
            return redirect()->route('admin.catalogos.simples')
                ->with('success', 'Registro actualizado exitosamente.');
        }

        return redirect()->route('admin.research-lines.index')
            ->with('success', 'Registro actualizado exitosamente.');
    }

    public function destroy(ResearchLine $researchline): RedirectResponse
    {
        $this->authorize('catalogos.eliminar');

        $this->checkCentroFormacion($researchline);

        try {
            $researchline->delete();

            return redirect()->route('admin.research-lines.index')
                ->with('success', 'Registro eliminado exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('admin.research-lines.index')
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

    private function checkCentroFormacion(ResearchLine $item): void
    {
        if ((int) $item->training_center_id !== (int) Auth::user()->training_center_id) {
            abort(403, 'No tienes permiso para gestionar catálogos de otros centros de formación.');
        }
    }

    // Note: Laravel resolves model bindings.
    // Ensure the parameter name $researchline matches route.
}
