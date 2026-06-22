<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MincienciasSubcategory;
use App\Models\MincienciasTypology;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MincienciasSubcategoryController extends Controller
{
    public function index(): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('admin.minciencias-typologies.index');
    }

    public function create(): View
    {
        $typologies = MincienciasTypology::orderBy('nombre')->get();
        return view('admin.minciencias_subcategories.create', compact('typologies'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'minciencias_typology_id' => ['required', 'exists:minciencias_typologies,id'],
            'descripcion' => ['nullable', 'string', 'max:500'],
        ]);
        MincienciasSubcategory::create($validated);
        return redirect()->route('admin.minciencias-typologies.index')
            ->with('success', 'Subcategoría creada correctamente.');
    }

    public function edit(MincienciasSubcategory $minciencias_subcategory): View
    {
        $typologies = MincienciasTypology::orderBy('nombre')->get();
        return view('admin.minciencias_subcategories.edit', compact('minciencias_subcategory', 'typologies'));
    }

    public function update(Request $request, MincienciasSubcategory $minciencias_subcategory): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'minciencias_typology_id' => ['required', 'exists:minciencias_typologies,id'],
            'descripcion' => ['nullable', 'string', 'max:500'],
        ]);
        $minciencias_subcategory->update($validated);
        return redirect()->route('admin.minciencias-typologies.index')
            ->with('success', 'Subcategoría actualizada correctamente.');
    }

    public function destroy(MincienciasSubcategory $minciencias_subcategory): RedirectResponse
    {
        try {
            $minciencias_subcategory->delete();
            return redirect()->route('admin.minciencias-typologies.index')
                ->with('success', 'Subcategoría eliminada correctamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('admin.minciencias-typologies.index')
                ->with('delete_error_subcategory', 'No se puede eliminar porque está asociada a otros registros.');
        }
    }
}
