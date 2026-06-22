<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ResearchGroup;
use App\Models\User;
use App\Support\TrainingCenterAccess;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ResearchGroupController extends Controller
{
    public function index(): View
    {
        // Cargamos todos los usuarios relacionados y luego, en la vista,
        // escogemos como responsable el que tenga rol 'director' o 'investigador_lider'.
        $q = ResearchGroup::with(['trainingCenter', 'users'])->orderBy('nombre');
        if (TrainingCenterAccess::scopedToTrainingCenter(Auth::user())) {
            $q->where('training_center_id', (int) Auth::user()->training_center_id);
        } elseif (TrainingCenterAccess::isCentroAdmin(Auth::user())) {
            $q->whereRaw('0 = 1');
        }
        $grupos = $q->paginate(15);

        return view('admin.research_groups.index', compact('grupos'));
    }

    public function create(): View
    {
        $centros = TrainingCenterAccess::centersForSelect(Auth::user());
        $usuarios = $this->usuariosPotenciales();

        return view('admin.research_groups.form', [
            'grupo'    => new ResearchGroup(),
            'centros'  => $centros,
            'usuarios' => $usuarios,
            'responsable' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        if (TrainingCenterAccess::scopedToTrainingCenter(Auth::user())) {
            $data['training_center_id'] = Auth::user()->training_center_id;
        }
        $embedded = $request->boolean('embedded');

        $grupo = ResearchGroup::create($data);

        $responsableId = $request->input('responsable_id');
        if ($responsableId) {
            // Registrar responsable como director del grupo
            $grupo->users()->sync([
                $responsableId => ['rol' => 'director'],
            ]);
        }

        $route = $embedded
            ? route('admin.research-groups.index', ['embedded' => 1])
            : route('admin.research-groups.index');

        return redirect()->to($route)
            ->with('success', 'Grupo de investigación creado correctamente.');
    }

    public function edit(ResearchGroup $researchGroup): View
    {
        if (TrainingCenterAccess::scopedToTrainingCenter(Auth::user())) {
            abort_unless(
                (int) $researchGroup->training_center_id === (int) Auth::user()->training_center_id,
                403
            );
        }

        $centros = TrainingCenterAccess::centersForSelect(Auth::user());
        $usuarios = $this->usuariosPotenciales();
        $responsableActual = $researchGroup->users()
            ->wherePivot('rol', 'director')
            ->first();

        return view('admin.research_groups.form', [
            'grupo'       => $researchGroup,
            'centros'     => $centros,
            'usuarios'    => $usuarios,
            'responsable' => $responsableActual,
        ]);
    }

    public function update(Request $request, ResearchGroup $researchGroup): RedirectResponse
    {
        if (TrainingCenterAccess::scopedToTrainingCenter(Auth::user())) {
            abort_unless(
                (int) $researchGroup->training_center_id === (int) Auth::user()->training_center_id,
                403
            );
        }

        $data = $this->validateData($request);
        if (TrainingCenterAccess::scopedToTrainingCenter(Auth::user())) {
            $data['training_center_id'] = Auth::user()->training_center_id;
        }
        $embedded = $request->boolean('embedded');
        $researchGroup->update($data);

        $responsableId = $request->input('responsable_id');
        if ($responsableId) {
            $researchGroup->users()->sync([
                $responsableId => ['rol' => 'director'],
            ]);
        } else {
            $researchGroup->users()->wherePivot('rol', 'director')->detach();
        }

        $route = $embedded
            ? route('admin.research-groups.index', ['embedded' => 1])
            : route('admin.research-groups.index');

        return redirect()->to($route)
            ->with('success', 'Grupo de investigación actualizado correctamente.');
    }

    public function destroy(ResearchGroup $researchGroup): RedirectResponse
    {
        if (TrainingCenterAccess::scopedToTrainingCenter(Auth::user())) {
            abort_unless(
                (int) $researchGroup->training_center_id === (int) Auth::user()->training_center_id,
                403
            );
        }

        $researchGroup->delete();

        return redirect()->route('admin.research-groups.index')
            ->with('success', 'Grupo de investigación eliminado.');
    }

    private function validateData(Request $request): array
    {
        $centerRule = ['required', 'exists:training_centers,id'];
        $allowed = TrainingCenterAccess::allowedCenterIdsForSave(Auth::user());
        if ($allowed !== null) {
            $centerRule[] = Rule::in($allowed);
        }

        return $request->validate([
            'training_center_id' => $centerRule,
            'nombre'             => ['required', 'string', 'max:255'],
            'codigo'             => ['nullable', 'string', 'max:100'],
            'descripcion'       => ['nullable', 'string'],
            'estado'             => ['required', 'string'],
        ]);
    }

    private function usuariosPotenciales()
    {
        $q = User::orderBy('email')
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', [
                    'director_investigacion',
                    'investigador_asociado',
                    'investigador',
                ]);
            });

        if (TrainingCenterAccess::scopedToTrainingCenter(Auth::user())) {
            $q->where('training_center_id', Auth::user()->training_center_id);
        }

        return $q->get();
    }
}

