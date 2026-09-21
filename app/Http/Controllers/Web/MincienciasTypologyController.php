<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MincienciasSubcategory;
use App\Models\MincienciasTypology;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MincienciasTypologyController extends Controller
{
    public function index(): View
    {
        $this->authorize('catalogos.leer');

        $centerId = Auth::user()->training_center_id;
        $typologies = MincienciasTypology::withCount('subcategories')->where('training_center_id', $centerId)->orderBy('nombre')->get();
        $subcategories = MincienciasSubcategory::with('mincienciasTypology')->where('training_center_id', $centerId)->orderBy('nombre')->get();

        return view('admin.minciencias_typologies.index', compact('typologies', 'subcategories'));
    }

    public function create(): View
    {
        $this->authorize('catalogos.crear');

        return view('admin.minciencias_typologies.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('catalogos.crear');

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'codigo' => ['required', 'string', 'max:50'],
            'descripcion' => ['nullable', 'string', 'max:500'],
        ]);
        $validated['training_center_id'] = Auth::user()->training_center_id;
        MincienciasTypology::create($validated);

        return redirect()->route('admin.minciencias-typologies.index')
            ->with('success', 'Tipología creada correctamente.');
    }

    public function edit(MincienciasTypology $minciencias_typology): View
    {
        $this->authorize('catalogos.editar');

        $this->checkCentroFormacion($minciencias_typology);

        return view('admin.minciencias_typologies.edit', compact('minciencias_typology'));
    }

    public function update(Request $request, MincienciasTypology $minciencias_typology): RedirectResponse
    {
        $this->authorize('catalogos.editar');

        $this->checkCentroFormacion($minciencias_typology);

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'codigo' => ['required', 'string', 'max:50'],
            'descripcion' => ['nullable', 'string', 'max:500'],
        ]);
        $minciencias_typology->update($validated);

        return redirect()->route('admin.minciencias-typologies.index')
            ->with('success', 'Tipología actualizada correctamente.');
    }

    public function destroy(MincienciasTypology $minciencias_typology): RedirectResponse
    {
        $this->authorize('catalogos.eliminar');

        $this->checkCentroFormacion($minciencias_typology);

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

    private function checkCentroFormacion(MincienciasTypology $item): void
    {
        if ((int) $item->training_center_id !== (int) Auth::user()->training_center_id) {
            abort(403, 'No tienes permiso para gestionar catálogos de otros centros de formación.');
        }
    }
}
