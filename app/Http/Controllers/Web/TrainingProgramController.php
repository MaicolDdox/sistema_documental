<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\TrainingProgram;
use App\Models\TrainingProgramType;
use App\Models\TrainingRecord;
use App\Http\Requests\StoreTrainingProgramRequest;
use App\Http\Requests\UpdateTrainingProgramRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TrainingProgramController extends Controller
{
    public function index(Request $request): View
    {
        $query = TrainingProgram::with(['trainingRecord', 'trainingProgramType'])->orderBy('nombre');

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('nombre', 'like', "%{$term}%")
                  ->orWhereHas('trainingProgramType', fn($q) => $q->where('nombre', 'like', "%{$term}%"));
                if (is_numeric($term)) {
                    $q->orWhereHas('trainingRecord', fn($q) => $q->where('codigo', (int) $term));
                }
            });
        }

        $programs = $query->paginate(15)->withQueryString();

        return view('admin.training_programs.index', compact('programs'));
    }

    public function create(): View
    {
        $records = TrainingRecord::orderBy('codigo')->get();
        $types = TrainingProgramType::orderBy('nombre')->get();
        return view('admin.training_programs.create', compact('records', 'types'));
    }

    public function store(StoreTrainingProgramRequest $request): RedirectResponse
    {
        TrainingProgram::create($request->validated());
        return redirect()->route('admin.training-programs.index')
            ->with('success', 'Programa de formación creado correctamente.');
    }

    public function edit(TrainingProgram $training_program): View
    {
        $records = TrainingRecord::orderBy('codigo')->get();
        $types = TrainingProgramType::orderBy('nombre')->get();
        return view('admin.training_programs.edit', compact('training_program', 'records', 'types'));
    }

    public function update(UpdateTrainingProgramRequest $request, TrainingProgram $training_program): RedirectResponse
    {
        $training_program->update($request->validated());
        return redirect()->route('admin.training-programs.index')
            ->with('success', 'Programa de formación actualizado correctamente.');
    }

    public function destroy(TrainingProgram $training_program): RedirectResponse
    {
        try {
            $training_program->delete();
            return redirect()->route('admin.training-programs.index')
                ->with('success', 'Programa de formación eliminado correctamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('admin.training-programs.index')
                ->with('error', 'No se puede eliminar porque está asociado a otros registros.');
        }
    }
}