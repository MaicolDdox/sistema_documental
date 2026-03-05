<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeArea;
use App\Models\KnowledgeGrandArea;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class KnowledgeAreaController extends Controller
{
    public function index(): View
    {
        $areas = KnowledgeArea::with('knowledgeGrandArea')->orderBy('nombre')->get();
        $grandAreas = KnowledgeGrandArea::withCount('knowledgeAreas')->orderBy('nombre')->get();
        return view('admin.knowledge_areas.index', compact('areas', 'grandAreas'));
    }

    public function create(): View
    {
        $grandAreas = KnowledgeGrandArea::orderBy('nombre')->get();
        return view('admin.knowledge_areas.create', compact('grandAreas'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'knowledge_grand_area_id' => ['required', 'exists:knowledge_grand_areas,id'],
            'descripccion' => ['nullable', 'string', 'max:500'],
        ]);
        KnowledgeArea::create($validated);
        return redirect()->route('admin.knowledge-areas.index')
            ->with('success', 'Área de conocimiento creada correctamente.');
    }

    public function edit(KnowledgeArea $knowledge_area): View
    {
        $grandAreas = KnowledgeGrandArea::orderBy('nombre')->get();
        return view('admin.knowledge_areas.edit', compact('knowledge_area', 'grandAreas'));
    }

    public function update(Request $request, KnowledgeArea $knowledge_area): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'knowledge_grand_area_id' => ['required', 'exists:knowledge_grand_areas,id'],
            'descripccion' => ['nullable', 'string', 'max:500'],
        ]);
        $knowledge_area->update($validated);
        return redirect()->route('admin.knowledge-areas.index')
            ->with('success', 'Área de conocimiento actualizada correctamente.');
    }

    public function destroy(KnowledgeArea $knowledge_area): RedirectResponse
    {
        try {
            $knowledge_area->delete();
            return redirect()->route('admin.knowledge-areas.index')
                ->with('success', 'Área de conocimiento eliminada correctamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('admin.knowledge-areas.index')
                ->with('error', 'No se puede eliminar porque está asociada a otros registros.');
        }
    }
}