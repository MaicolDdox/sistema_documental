<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\TrainingProgramType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TrainingProgramTypeController extends Controller
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

        $items = TrainingProgramType::where('training_center_id', Auth::user()->training_center_id)->paginate(10);

        return view('admin.parametric.index', [
            'items' => $items,
            'title' => 'Tipos de Programas',
            'routePrefix' => 'admin.training-program-types',
            'fields' => $this->getFields(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('catalogos.crear');

        return view('admin.parametric.create', [
            'title' => 'Crear Tipos de Programas',
            'routePrefix' => 'admin.training-program-types',
            'fields' => $this->getFields(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('catalogos.crear');

        $validated = $request->validate($this->getValidationRules());
        // Custom request logic applies if $request is not just a base Request.
        if (method_exists($request, 'validated') && 'Request' !== 'Request') {
            $validated = $request->validated();
        }

        $validated['training_center_id'] = Auth::user()->training_center_id;
        TrainingProgramType::create($validated);

        return redirect()->route('admin.training-program-types.index')
            ->with('success', 'Registro creado exitosamente.');
    }

    public function edit(TrainingProgramType $trainingprogramtype): View
    {
        $this->authorize('catalogos.editar');

        $this->checkCentroFormacion($trainingprogramtype);

        return view('admin.parametric.edit', [
            'item' => $trainingprogramtype,
            'title' => 'Editar Tipos de Programas',
            'routePrefix' => 'admin.training-program-types',
            'fields' => $this->getFields(),
        ]);
    }

    public function update(Request $request, TrainingProgramType $trainingprogramtype): RedirectResponse
    {
        $this->authorize('catalogos.editar');

        $this->checkCentroFormacion($trainingprogramtype);

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
        $this->authorize('catalogos.eliminar');

        $this->checkCentroFormacion($trainingprogramtype);

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
        foreach (array_keys($this->getFields()) as $f) {
            $rules[$f] = 'required';
        }

        return $rules;
    }

    private function checkCentroFormacion(TrainingProgramType $item): void
    {
        if ((int) $item->training_center_id !== (int) Auth::user()->training_center_id) {
            abort(403, 'No tienes permiso para gestionar catálogos de otros centros de formación.');
        }
    }

    // Note: Laravel resolves model bindings.
    // Ensure the parameter name $trainingprogramtype matches route.
}
