<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ExternalAdvisor;
use App\Http\Requests\StoreExternalAdvisorRequest;
use App\Http\Requests\UpdateExternalAdvisorRequest;
use App\Support\TrainingCenterAccess;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ExternalAdvisorController extends Controller
{
    public function index(Request $request): View
    {
        $query = ExternalAdvisor::with('user')->orderBy('nombre_completo');

        if (TrainingCenterAccess::scopedToTrainingCenter($request->user())) {
            $cid = (int) $request->user()->training_center_id;
            $query->where(function ($q) use ($cid) {
                $q->whereHas('user', fn ($u) => $u->where('training_center_id', $cid))
                    ->orWhereHas('seedlings.researchGroup', fn ($r) => $r->where('training_center_id', $cid));
            });
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('nombre_completo', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%")
                  ->orWhere('institucion', 'like', "%{$term}%");
            });
        }

        $advisors = $query->paginate(15)->withQueryString();

        return view('admin.external_advisors.index', compact('advisors'));
    }

    public function create(): View
    {
        return view('admin.external_advisors.create');
    }

    public function store(StoreExternalAdvisorRequest $request): RedirectResponse
    {
        ExternalAdvisor::create($request->validated());
        return redirect()->route('admin.external-advisors.index')->with('success', 'Asesor externo creado correctamente.');
    }

    public function show(ExternalAdvisor $externalAdvisor): View
    {
        $this->authorizeExternalAdvisorForUserCenter($externalAdvisor);
        $externalAdvisor->load('user', 'seedlings');
        return view('admin.external_advisors.show', compact('externalAdvisor'));
    }

    public function edit(ExternalAdvisor $externalAdvisor): View
    {
        $this->authorizeExternalAdvisorForUserCenter($externalAdvisor);
        return view('admin.external_advisors.edit', compact('externalAdvisor'));
    }

    public function update(UpdateExternalAdvisorRequest $request, ExternalAdvisor $externalAdvisor): RedirectResponse
    {
        $this->authorizeExternalAdvisorForUserCenter($externalAdvisor);
        $externalAdvisor->update($request->validated());
        return redirect()->route('admin.external-advisors.index')->with('success', 'Asesor externo actualizado correctamente.');
    }

    public function destroy(ExternalAdvisor $externalAdvisor): RedirectResponse
    {
        $this->authorizeExternalAdvisorForUserCenter($externalAdvisor);
        try {
            $externalAdvisor->delete();
            return redirect()->route('admin.external-advisors.index')->with('success', 'Asesor externo eliminado correctamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('admin.external-advisors.index')
                ->with('delete_error', 'No se puede eliminar porque está asociado a semilleros u otros registros.');
        }
    }

    private function authorizeExternalAdvisorForUserCenter(ExternalAdvisor $externalAdvisor): void
    {
        $user = auth()->user();
        if (! $user || TrainingCenterAccess::isSuperAdmin($user)) {
            return;
        }
        if (! TrainingCenterAccess::scopedToTrainingCenter($user)) {
            return;
        }

        $cid = (int) $user->training_center_id;
        $externalAdvisor->loadMissing('user');

        if ($externalAdvisor->user && (int) $externalAdvisor->user->training_center_id === $cid) {
            return;
        }

        if ($externalAdvisor->seedlings()->whereHas('researchGroup', fn ($q) => $q->where('training_center_id', $cid))->exists()) {
            return;
        }

        abort(403);
    }
}
