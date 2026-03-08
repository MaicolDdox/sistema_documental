<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Department;
use App\Models\TrainingCenter;
use App\Http\Requests\StoreTrainingCenterRequest;
use App\Http\Requests\UpdateTrainingCenterRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TrainingCenterController extends Controller
{
    public function index(Request $request): View
    {
        $query = TrainingCenter::with(['department', 'city'])->orderBy('nombre');

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('nombre', 'like', "%{$term}%")
                  ->orWhereHas('department', fn($q) => $q->where('nombre', 'like', "%{$term}%"))
                  ->orWhereHas('city', fn($q) => $q->where('nombre', 'like', "%{$term}%"));
                if (is_numeric($term)) {
                    $q->orWhere('codigo', (int) $term);
                }
            });
        }

        $centers = $query->paginate(15)->withQueryString();
        $departments = Department::orderBy('nombre')->get();
        $cities = City::with('department')->orderBy('nombre')->get();

        return view('admin.training_centers.index', compact('centers', 'departments', 'cities'));
    }

    public function show(TrainingCenter $training_center)
    {
        $training_center->load(['department', 'city']);
        return response()->json($training_center);
    }

    public function toggle(TrainingCenter $training_center): RedirectResponse
    {
        $training_center->update(['activo' => !$training_center->activo]);
        $estado = $training_center->activo ? 'activado' : 'desactivado';
        return redirect()->route('admin.training-centers.index')
            ->with('success', "Centro de formación {$estado} correctamente.");
    }

    public function create(): View
    {
        $departments = Department::orderBy('nombre')->get();
        $cities = City::with('department')->orderBy('nombre')->get();
        return view('admin.training_centers.create', compact('departments', 'cities'));
    }

    public function store(StoreTrainingCenterRequest $request): RedirectResponse
    {
        TrainingCenter::create($request->validated());
        return redirect()->route('admin.training-centers.index')
            ->with('success', 'Centro de formación creado correctamente.');
    }

    public function edit(TrainingCenter $training_center): View
    {
        $departments = Department::orderBy('nombre')->get();
        $cities = City::with('department')->orderBy('nombre')->get();
        return view('admin.training_centers.edit', compact('training_center', 'departments', 'cities'));
    }

    public function update(UpdateTrainingCenterRequest $request, TrainingCenter $training_center): RedirectResponse
    {
        $training_center->update($request->validated());
        return redirect()->route('admin.training-centers.index')
            ->with('success', 'Centro de formación actualizado correctamente.');
    }

    public function destroy(Request $request, TrainingCenter $training_center)
    {
        $razones = [];

        if ($training_center->activo ?? true) {
            $razones[] = 'está activo — desactívelo desde el botón de acciones en la fila';
        }
        if ($training_center->users()->exists()) {
            $razones[] = 'tiene usuarios asignados — reasigne o elimine esas vinculaciones';
        }
        if ($training_center->researchGroups()->exists()) {
            $razones[] = 'tiene grupos de investigación vinculados — desvincule los grupos primero';
        }

        if (!empty($razones)) {
            $mensaje = 'No se puede eliminar este centro de formación porque ' . implode('; ', $razones) . '.';
            if ($request->wantsJson()) {
                return response()->json(['message' => $mensaje], 422);
            }
            return redirect()->route('admin.training-centers.index')
                ->with('delete_error', $mensaje);
        }

        try {
            $training_center->delete();
            return redirect()->route('admin.training-centers.index')
                ->with('success', 'Centro de formación eliminado correctamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            $mensaje = 'No se puede eliminar: el centro está asociado a otros registros en el sistema. Revise usuarios, grupos de investigación u otras relaciones antes de intentar de nuevo.';
            if ($request->wantsJson()) {
                return response()->json(['message' => $mensaje], 422);
            }
            return redirect()->route('admin.training-centers.index')
                ->with('delete_error', $mensaje);
        }
    }
}