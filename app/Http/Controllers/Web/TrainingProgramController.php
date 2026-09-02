<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Enums\EstadoEnum;
use App\Models\TrainingProgram;
use App\Models\TrainingProgramType;
use App\Http\Requests\StoreTrainingProgramRequest;
use App\Http\Requests\UpdateTrainingProgramRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TrainingProgramController extends Controller
{
    public function index(Request $request): View
    {
        $query = TrainingProgram::with(['trainingProgramType'])->orderBy('nombre');

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('nombre', 'like', "%{$term}%")
                  ->orWhereHas('trainingProgramType', fn($q) => $q->where('nombre', 'like', "%{$term}%"));
            });
        }

        $programs = $query->paginate(15)->withQueryString();
        $types = TrainingProgramType::orderBy('nombre')->get();

        return view('admin.training_programs.index', compact('programs', 'types'));
    }

    public function create(): View
    {
        $types = TrainingProgramType::orderBy('nombre')->get();
        return view('admin.training_programs.create', compact('types'));
    }

    public function store(StoreTrainingProgramRequest $request): RedirectResponse
    {
        $type = TrainingProgramType::firstOrCreate(
            ['nombre' => trim($request->input('tipo'))],
            ['descripcion' => '']
        );
        $data = $request->validated();
        unset($data['tipo']);
        $data['training_program_type_id'] = $type->id;
        TrainingProgram::create($data);
        return redirect()->route('admin.training-programs.index')
            ->with('success', 'Programa de formación creado correctamente.');
    }

    public function edit(TrainingProgram $training_program): View
    {
        $types = TrainingProgramType::orderBy('nombre')->get();
        return view('admin.training_programs.edit', compact('training_program', 'types'));
    }

    public function update(UpdateTrainingProgramRequest $request, TrainingProgram $training_program): RedirectResponse
    {
        $type = TrainingProgramType::firstOrCreate(
            ['nombre' => trim($request->input('tipo'))],
            ['descripcion' => '']
        );
        $data = $request->validated();
        unset($data['tipo']);
        $data['training_program_type_id'] = $type->id;
        $training_program->update($data);
        return redirect()->route('admin.training-programs.index')
            ->with('success', 'Programa de formación actualizado correctamente.');
    }

    public function toggle(TrainingProgram $training_program): RedirectResponse
    {
        $training_program->estado = $training_program->estado === EstadoEnum::Activo
            ? EstadoEnum::Inactivo
            : EstadoEnum::Activo;
        $training_program->save();
        $label = $training_program->estado === EstadoEnum::Activo ? 'activado' : 'desactivado';
        return redirect()->route('admin.training-programs.index')
            ->with('success', "Programa de formación {$label} correctamente.");
    }

    public function destroy(TrainingProgram $training_program): RedirectResponse
    {
        if ($training_program->estado === EstadoEnum::Activo) {
            return redirect()->route('admin.training-programs.index')
                ->with('delete_error', 'Este programa está activo. Desactívelo desde el botón de acciones en la fila y vuelva a intentar eliminarlo.');
        }
        $peopleCount = $training_program->people()->count();
        if ($peopleCount > 0) {
            return redirect()->route('admin.training-programs.index')
                ->with('delete_error', "No se puede eliminar: hay {$peopleCount} persona(s) vinculada(s) a este programa. Reasigne o elimine esas vinculaciones primero.");
        }
        try {
            $training_program->delete();
            return redirect()->route('admin.training-programs.index')
                ->with('success', 'Programa de formación eliminado correctamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('admin.training-programs.index')
                ->with('delete_error', 'No se puede eliminar porque está asociado a otros registros.');
        }
    }
}