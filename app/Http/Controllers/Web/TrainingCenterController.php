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

        return view('admin.training_centers.index', compact('centers'));
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

    public function destroy(TrainingCenter $training_center): RedirectResponse
    {
        try {
            $training_center->delete();
            return redirect()->route('admin.training-centers.index')
                ->with('success', 'Centro de formación eliminado correctamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('admin.training-centers.index')
                ->with('error', 'No se puede eliminar porque está asociado a otros registros.');
        }
    }
}