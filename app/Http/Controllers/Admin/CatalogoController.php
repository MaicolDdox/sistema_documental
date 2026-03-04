<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Catalogo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;

class CatalogoController extends Controller
{
    /**
     * Display a listing of the catalogs.
     * Permission: catalogos.leer
     */
    public function index(Request $request)
    {
        $this->authorize('catalogos.leer');

        $query = Catalogo::query();

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        $catalogos = $query->paginate(15)->withQueryString();
        
        // Obtener tipos únicos para el filtro
        $tipos = Catalogo::select('tipo')->distinct()->pluck('tipo');

        return view('admin.catalogos.index', compact('catalogos', 'tipos'));
    }

    /**
     * Show the form for creating a new catalog.
     * Permission: catalogos.crear
     */
    public function create()
    {
        $this->authorize('catalogos.crear');
        return view('admin.catalogos.create');
    }

    /**
     * Store a newly created catalog in storage.
     * Permission: catalogos.crear
     */
    public function store(Request $request)
    {
        $this->authorize('catalogos.crear');

        $validated = $request->validate([
            'tipo'        => 'required|string|max:50',
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'activo'      => 'boolean',
        ]);

        Catalogo::create([
            'tipo'        => $validated['tipo'],
            'nombre'      => $validated['nombre'],
            'descripcion' => $validated['descripcion'],
            'activo'      => $request->has('activo'),
        ]);

        return redirect()->route('admin.catalogos.index')
                         ->with('success', 'Catálogo creado correctamente.');
    }

    /**
     * Show the form for editing the specified catalog.
     * Permission: catalogos.editar
     */
    public function edit($id)
    {
        $this->authorize('catalogos.editar');

        $catalogo = Catalogo::findOrFail($id);

        return view('admin.catalogos.edit', compact('catalogo'));
    }

    /**
     * Update the specified catalog in storage.
     * Permission: catalogos.editar
     */
    public function update(Request $request, $id)
    {
        $this->authorize('catalogos.editar');

        $catalogo = Catalogo::findOrFail($id);

        $validated = $request->validate([
            'tipo'        => 'required|string|max:50',
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'activo'      => 'boolean',
        ]);

        $catalogo->update([
            'tipo'        => $validated['tipo'],
            'nombre'      => $validated['nombre'],
            'descripcion' => $validated['descripcion'],
            'activo'      => $request->has('activo'),
        ]);

        return redirect()->route('admin.catalogos.index')
                         ->with('success', 'Catálogo actualizado correctamente.');
    }

    /**
     * Remove the specified catalog from storage.
     * Permission: catalogos.eliminar
     */
    public function destroy($id)
    {
        $this->authorize('catalogos.eliminar');

        $catalogo = Catalogo::findOrFail($id);

        try {
            $catalogo->delete();
            return redirect()->route('admin.catalogos.index')
                             ->with('success', 'Catálogo eliminado correctamente.');
        } catch (QueryException $e) {
            // Verificar si el error es por restricción de llave foránea (1451 en MySQL)
            if ($e->errorInfo[1] == 1451) {
                return redirect()->route('admin.catalogos.index')
                                 ->with('error', 'No se puede eliminar el catálogo porque está referenciado o en uso en otra parte del sistema.');
            }
            
            return redirect()->route('admin.catalogos.index')
                             ->with('error', 'Ocurrió un error al intentar eliminar el catálogo.');
        }
    }
}
