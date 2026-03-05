<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ExternalAdvisor;
use App\Http\Requests\StoreExternalAdvisorRequest;
use App\Http\Requests\UpdateExternalAdvisorRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ExternalAdvisorController extends Controller
{
    public function index(Request $request): View
    {
        $query = ExternalAdvisor::with('user')->orderBy('nombre_completo');

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
        $externalAdvisor->load('user', 'seedlings');
        return view('admin.external_advisors.show', compact('externalAdvisor'));
    }

    public function edit(ExternalAdvisor $externalAdvisor): View
    {
        return view('admin.external_advisors.edit', compact('externalAdvisor'));
    }

    public function update(UpdateExternalAdvisorRequest $request, ExternalAdvisor $externalAdvisor): RedirectResponse
    {
        $externalAdvisor->update($request->validated());
        return redirect()->route('admin.external-advisors.index')->with('success', 'Asesor externo actualizado correctamente.');
    }

    public function destroy(ExternalAdvisor $externalAdvisor): RedirectResponse
    {
        $externalAdvisor->delete();
        return redirect()->route('admin.external-advisors.index')->with('success', 'Asesor externo eliminado correctamente.');
    }
}
