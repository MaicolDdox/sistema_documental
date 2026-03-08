<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MincienciasTypology;
use App\Models\MincienciasSubcategory;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MincienciasTypologyController extends Controller
{
    public function index(): View
    {
        $typologies = MincienciasTypology::withCount('subcategories')->orderBy('nombre')->get();
        $subcategories = MincienciasSubcategory::with('mincienciasTypology')->orderBy('nombre')->get();
        return view('admin.minciencias_typologies.index', compact('typologies', 'subcategories'));
    }

    public function create(): View
    {
        return view('admin.minciencias_typologies.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'codigo' => ['required', 'string', 'max:50'],
            'descripccion' => ['nullable', 'string', 'max:500'],
        ]);
        MincienciasTypology::create($validated);
        return redirect()->route('admin.minciencias-typologies.index')
            ->with('success', 'Tipología creada correctamente.');
    }

    public function edit(MincienciasTypology $minciencias_typology): View
    {
        return view('admin.minciencias_typologies.edit', compact('minciencias_typology'));
    }

    public function update(Request $request, MincienciasTypology $minciencias_typology): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'codigo' => ['required', 'string', 'max:50'],
            'descripccion' => ['nullable', 'string', 'max:500'],
        ]);
        $minciencias_typology->update($validated);
        return redirect()->route('admin.minciencias-typologies.index')
            ->with('success', 'Tipología actualizada correctamente.');
    }

    public function destroy(MincienciasTypology $minciencias_typology): RedirectResponse
    {
        $count = $minciencias_typology->subcategories()->count();
        if ($count > 0) {
            return redirect()->route('admin.minciencias-typologies.index')
                ->with('delete_error_typology', "No se puede eliminar: hay {$count} subcategoría(s) vinculada(s). Elimine o reasigne las subcategorías primero.");
        }
        try {
            $minciencias_typology->delete();
            return redirect()->route('admin.minciencias-typologies.index')
                ->with('success', 'Tipología eliminada correctamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('admin.minciencias-typologies.index')
                ->with('delete_error_typology', 'No se puede eliminar porque está asociada a otros registros.');
        }
    }
}