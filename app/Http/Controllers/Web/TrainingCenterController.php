<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTrainingCenterRequest;
use App\Http\Requests\UpdateTrainingCenterRequest;
use App\Models\City;
use App\Models\Department;
use App\Models\TrainingCenter;
use App\Support\TrainingCenterAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TrainingCenterController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($this->isSuperAdmin(), 403);

        $query = TrainingCenter::with(['department', 'city'])->orderBy('nombre');

        if (TrainingCenterAccess::scopedToTrainingCenter($request->user())) {
            $query->where('id', (int) $request->user()->training_center_id);
        } elseif (TrainingCenterAccess::isCentroAdmin($request->user())) {
            $query->where('id', 0);
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('nombre', 'like', "%{$term}%")
                    ->orWhereHas('department', fn ($q) => $q->where('nombre', 'like', "%{$term}%"))
                    ->orWhereHas('city', fn ($q) => $q->where('nombre', 'like', "%{$term}%"));
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
        abort_unless($this->isSuperAdmin(), 403);

        $training_center->load(['department', 'city']);

        return response()->json($training_center);
    }

    public function toggle(TrainingCenter $training_center): RedirectResponse
    {
        abort_unless($this->isSuperAdmin(), 403);

        $training_center->update(['activo' => ! $training_center->activo]);
        $estado = $training_center->activo ? 'activado' : 'desactivado';

        return redirect()->route('admin.training-centers.index')
            ->with('success', "Centro de formación {$estado} correctamente.");
    }

    public function create(): View
    {
        abort_unless($this->isSuperAdmin(), 403);

        $departments = Department::orderBy('nombre')->get();
        $cities = City::with('department')->orderBy('nombre')->get();

        return view('admin.training_centers.create', compact('departments', 'cities'));
    }

    public function store(StoreTrainingCenterRequest $request): RedirectResponse
    {
        abort_unless($this->isSuperAdmin(), 403);

        TrainingCenter::create($request->validated());

        return redirect()->route('admin.training-centers.index')
            ->with('success', 'Centro de formación creado correctamente.');
    }

    public function edit(TrainingCenter $training_center): View
    {
        abort_unless($this->isSuperAdmin(), 403);

        $departments = Department::orderBy('nombre')->get();
        $cities = City::with('department')->orderBy('nombre')->get();

        return view('admin.training_centers.edit', compact('training_center', 'departments', 'cities'));
    }

    public function update(UpdateTrainingCenterRequest $request, TrainingCenter $training_center): RedirectResponse
    {
        abort_unless($this->isSuperAdmin(), 403);

        $training_center->update($request->validated());

        return redirect()->route('admin.training-centers.index')
            ->with('success', 'Centro de formación actualizado correctamente.');
    }

    public function destroy(Request $request, TrainingCenter $training_center)
    {
        abort_unless($this->isSuperAdmin(), 403);

        $razones = [];

        if ($training_center->activo ?? true) {
            $razones[] = 'está activo — desactívelo desde el botón de acciones en la fila';
        }
        if ($training_center->users()->exists()) {
            $razones[] = 'tiene usuarios asignados — reasigne o elimine esas vinculaciones';
        }
        if (\App\Models\Seedling::where('training_center_id', $training_center->id)->exists()) {
            $razones[] = 'tiene semilleros vinculados — desvincule los semilleros primero';
        }

        if (! empty($razones)) {
            $mensaje = 'No se puede eliminar este centro de formación porque '.implode('; ', $razones).'.';
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

    private function isSuperAdmin(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return (bool) $user?->hasRole('super_administrador');
    }
}
