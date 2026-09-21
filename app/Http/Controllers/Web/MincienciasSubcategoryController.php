<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MincienciasSubcategory;
use App\Models\MincienciasTypology;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MincienciasSubcategoryController extends Controller
{
    public function index(): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('catalogos.leer');

        return redirect()->route('admin.minciencias-typologies.index');
    }

    public function create(): View
    {
        $this->authorize('catalogos.crear');

        $typologies = MincienciasTypology::where('training_center_id', Auth::user()->training_center_id)->orderBy('nombre')->get();

        return view('admin.minciencias_subcategories.create', compact('typologies'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('catalogos.crear');

        $centerId = Auth::user()->training_center_id;
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'minciencias_typology_id' => ['required', Rule::exists('minciencias_typologies', 'id')->where('training_center_id', $centerId)],
            'descripcion' => ['nullable', 'string', 'max:500'],
        ]);
        $validated['training_center_id'] = $centerId;
        MincienciasSubcategory::create($validated);

        return redirect()->route('admin.minciencias-typologies.index')
            ->with('success', 'Subcategoría creada correctamente.');
    }

    public function edit(MincienciasSubcategory $minciencias_subcategory): View
    {
        $this->authorize('catalogos.editar');

        $this->checkCentroFormacion($minciencias_subcategory);

        $typologies = MincienciasTypology::where('training_center_id', Auth::user()->training_center_id)->orderBy('nombre')->get();

        return view('admin.minciencias_subcategories.edit', compact('minciencias_subcategory', 'typologies'));
    }

    public function update(Request $request, MincienciasSubcategory $minciencias_subcategory): RedirectResponse
    {
        $this->authorize('catalogos.editar');

        $this->checkCentroFormacion($minciencias_subcategory);

        $centerId = Auth::user()->training_center_id;
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'minciencias_typology_id' => ['required', Rule::exists('minciencias_typologies', 'id')->where('training_center_id', $centerId)],
            'descripcion' => ['nullable', 'string', 'max:500'],
        ]);
        $minciencias_subcategory->update($validated);

        return redirect()->route('admin.minciencias-typologies.index')
            ->with('success', 'Subcategoría actualizada correctamente.');
    }

    public function destroy(MincienciasSubcategory $minciencias_subcategory): RedirectResponse
    {
        $this->authorize('catalogos.eliminar');

        $this->checkCentroFormacion($minciencias_subcategory);

        try {
            $minciencias_subcategory->delete();

            return redirect()->route('admin.minciencias-typologies.index')
                ->with('success', 'Subcategoría eliminada correctamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('admin.minciencias-typologies.index')
                ->with('delete_error_subcategory', 'No se puede eliminar porque está asociada a otros registros.');
        }
    }

    private function checkCentroFormacion(MincienciasSubcategory $item): void
    {
        if ((int) $item->training_center_id !== (int) Auth::user()->training_center_id) {
            abort(403, 'No tienes permiso para gestionar catálogos de otros centros de formación.');
        }
    }
}
