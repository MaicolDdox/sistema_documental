<?php

namespace App\Http\Controllers\LiderProyecto;

use App\Http\Controllers\Controller;
use App\Models\ProjectLearner;
use App\Models\TrainingProgram;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AprendizController extends Controller
{
    use LiderProyectoContext;

    public function index(): View
    {
        $proyecto = $this->miProyecto();
        $aprendices = $proyecto->learners()->with('trainingProgram')->latest()->get();
        $trainingPrograms = TrainingProgram::orderBy('nombre')->get();

        return view('lider_proyecto.aprendices.index', compact('proyecto', 'aprendices', 'trainingPrograms'));
    }

    public function store(Request $request): RedirectResponse
    {
        $proyecto = $this->miProyecto();

        $validated = $this->validarAprendiz($request);

        $proyecto->learners()->create(array_merge($validated, [
            'created_by_user_id' => Auth::id(),
        ]));

        return redirect()->route('lider-proyecto.aprendices.index')->with('success', 'Aprendiz registrado correctamente.');
    }

    public function update(Request $request, ProjectLearner $aprendiz): RedirectResponse
    {
        $this->ensureAprendizDelProyecto($aprendiz);

        $validated = $this->validarAprendiz($request);
        $aprendiz->update($validated);

        return redirect()->route('lider-proyecto.aprendices.index')->with('success', 'Datos del aprendiz actualizados.');
    }

    public function destroy(ProjectLearner $aprendiz): RedirectResponse
    {
        $this->ensureAprendizDelProyecto($aprendiz);
        $aprendiz->delete();

        return redirect()->route('lider-proyecto.aprendices.index')->with('success', 'Aprendiz eliminado del proyecto.');
    }

    private function validarAprendiz(Request $request): array
    {
        return $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'numero_documento' => 'required|string|max:20',
            'ficha' => 'required|string|max:50',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'training_program_id' => 'required|exists:training_programs,id',
        ]);
    }

    private function ensureAprendizDelProyecto(ProjectLearner $aprendiz): void
    {
        $proyecto = $this->miProyecto();
        if ((int) $aprendiz->project_id !== (int) $proyecto->id) {
            abort(403, 'Este aprendiz no pertenece a tu proyecto.');
        }
    }
}
