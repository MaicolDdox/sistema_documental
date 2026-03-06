<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeGrandArea;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class KnowledgeGrandAreaController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('admin.knowledge-areas.index');
    }

    public function create(): View
    {
        return view('admin.knowledge_grand_areas.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'descripccion' => ['nullable', 'string', 'max:500'],
        ]);
        KnowledgeGrandArea::create($validated);
        return redirect()->route('admin.knowledge-areas.index')
            ->with('success', 'Gran área de conocimiento creada correctamente.');
    }

    public function edit(KnowledgeGrandArea $knowledge_grand_area): View
    {
        return view('admin.knowledge_grand_areas.edit', compact('knowledge_grand_area'));
    }

    public function update(Request $request, KnowledgeGrandArea $knowledge_grand_area): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'descripccion' => ['nullable', 'string', 'max:500'],
        ]);
        $knowledge_grand_area->update($validated);
        return redirect()->route('admin.knowledge-areas.index')
            ->with('success', 'Gran área de conocimiento actualizada correctamente.');
    }

    public function destroy(KnowledgeGrandArea $knowledge_grand_area): RedirectResponse
    {
        $count = $knowledge_grand_area->knowledgeAreas()->count();
        if ($count > 0) {
            return redirect()->route('admin.knowledge-areas.index')
                ->with('delete_error_grand', "No se puede eliminar: hay {$count} área(s) vinculada(s). Elimine o reasigne las áreas primero.");
        }
        try {
            $knowledge_grand_area->delete();
            return redirect()->route('admin.knowledge-areas.index')
                ->with('success', 'Gran área de conocimiento eliminada correctamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('admin.knowledge-areas.index')
                ->with('delete_error_grand', 'No se puede eliminar porque está asociada a otros registros.');
        }
    }
}