<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\StreamsPublicStorageFiles;
use App\Http\Controllers\Controller;
use App\Models\MincienciasProduct;
use App\Models\MincienciasProductFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Vista de solo lectura de productos Minciencias para administrador_sistema
 * (reforma GDI/SDI) — la aprobación/rechazo pasó a director_grupo_investigacion,
 * acotada a los co_investigador_gdi de su propio grupo. El admin conserva
 * listar/ver_detalle a nivel de su centro (BUG-20260813-029 sigue aplicando
 * al alcance por training_center_id).
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
