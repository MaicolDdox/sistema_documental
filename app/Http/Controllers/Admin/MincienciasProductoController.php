<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\StreamsPublicStorageFiles;
use App\Enums\EstadoRevisionEnum;
use App\Http\Controllers\Controller;
use App\Models\MincienciasProduct;
use App\Models\MincienciasProductFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Aprobación de productos Minciencias por el administrador_sistema
 * (BUG-20260813-029). Solo ve/aprueba los productos vinculados a su propio
 * centro de formación (training_center_id elegido por el co-investigador
 * al crear el producto) — nunca los de otros centros.
 */
class MincienciasProductoController extends Controller
{
    use StreamsPublicStorageFiles;

    public function index(): View
    {
        $this->authorize('minciencias.listar');

        $centerId = Auth::user()->training_center_id;

        $productos = MincienciasProduct::with(['user.person', 'researchLine'])
            ->withCount('files')
            ->when(
                $centerId,
                fn ($q) => $q->where('training_center_id', $centerId),
                fn ($q) => $q->whereRaw('0 = 1')
            )
            ->orderByRaw("CASE WHEN estado_revision = 'pendiente' THEN 0 ELSE 1 END")
            ->latest()
            ->get();

        return view('admin.minciencias.index', compact('productos'));
    }

    public function show(MincienciasProduct $producto): View
    {
        $this->authorize('minciencias.ver_detalle');
        $this->ensureDelCentro($producto);

        $producto->load([
            'user.person',
            'files.uploadedBy',
            'revisadoPor.person',
            'researchLine',
            'technologicalLine',
            'thematicArea',
            'projectModality',
            'investigationType',
        ]);

        return view('admin.minciencias.show', compact('producto'));
    }

    public function aprobar(MincienciasProduct $producto): RedirectResponse
    {
        $this->authorize('minciencias.aprobar');
        $this->ensureDelCentro($producto);

        $producto->update([
            'estado_revision' => EstadoRevisionEnum::Aprobado,
            'observacion_admin' => null,
            'revisado_por' => Auth::id(),
            'revisado_at' => now(),
        ]);

        return redirect()->route('admin.minciencias.index')->with('success', 'Producto Minciencias aprobado.');
    }

    public function rechazar(Request $request, MincienciasProduct $producto): RedirectResponse
    {
        $this->authorize('minciencias.rechazar');
        $this->ensureDelCentro($producto);

        $validated = $request->validate([
            'observaciones' => 'required|string|max:2000',
        ], [
            'observaciones.required' => 'Las observaciones son obligatorias al rechazar.',
        ]);

        $producto->update([
            'estado_revision' => EstadoRevisionEnum::Rechazado,
            'observacion_admin' => $validated['observaciones'],
            'revisado_por' => Auth::id(),
            'revisado_at' => now(),
        ]);

        return redirect()->route('admin.minciencias.index')->with('success', 'Producto Minciencias rechazado.');
    }

    public function descargarArchivo(MincienciasProductFile $archivo): StreamedResponse
    {
        $this->authorize('minciencias.ver_detalle');
        $this->ensureDelCentro($archivo->product);

        return $this->descargarArchivoPublico($archivo->archivo, $archivo->descripcion);
    }

    private function ensureDelCentro(MincienciasProduct $producto): void
    {
        $centerId = Auth::user()->training_center_id;
        if ((int) $producto->training_center_id !== (int) $centerId) {
            abort(403, 'Este producto Minciencias no pertenece a tu centro de formación.');
        }
    }
}
