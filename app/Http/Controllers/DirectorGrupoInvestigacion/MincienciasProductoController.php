<?php

namespace App\Http\Controllers\DirectorGrupoInvestigacion;

use App\Concerns\StreamsPublicStorageFiles;
use App\Enums\EstadoRevisionEnum;
use App\Http\Controllers\Controller;
use App\Models\GrupoInvestigacion;
use App\Models\MincienciasProduct;
use App\Models\MincienciasProductFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Aprobación de productos Minciencias por director_grupo_investigacion —
 * reforma GDI/SDI. Solo ve/aprueba los productos de los co_investigador_gdi
 * de SU propio grupo (a diferencia de Admin\MincienciasProductoController,
 * que filtraba por training_center_id — aquí el alcance es más estrecho).
 */
class MincienciasProductoController extends Controller
{
    use StreamsPublicStorageFiles;

    public function index(): View
    {
        $this->authorize('minciencias.listar');

        $grupo = GrupoInvestigacion::where('director_id', Auth::id())->firstOrFail();

        $productos = MincienciasProduct::with(['user.person', 'researchLine'])
            ->withCount('files')
            ->where('grupo_investigacion_id', $grupo->id)
            ->orderByRaw("CASE WHEN estado_revision = 'pendiente' THEN 0 ELSE 1 END")
            ->latest()
            ->get();

        return view('director_grupo_investigacion.minciencias.index', compact('productos'));
    }

    public function show(MincienciasProduct $producto): View
    {
        $this->authorize('minciencias.ver_detalle');
        $this->ensureDelGrupo($producto);

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

        return view('director_grupo_investigacion.minciencias.show', compact('producto'));
    }

    public function aprobar(MincienciasProduct $producto): RedirectResponse
    {
        $this->authorize('minciencias.aprobar');
        $this->ensureDelGrupo($producto);

        $producto->update([
            'estado_revision' => EstadoRevisionEnum::Aprobado,
            'observacion_admin' => null,
            'revisado_por' => Auth::id(),
            'revisado_at' => now(),
        ]);

        return redirect()->route('director-grupo-investigacion.minciencias.index')->with('success', 'Producto Minciencias aprobado.');
    }

    public function rechazar(Request $request, MincienciasProduct $producto): RedirectResponse
    {
        $this->authorize('minciencias.rechazar');
        $this->ensureDelGrupo($producto);

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

        return redirect()->route('director-grupo-investigacion.minciencias.index')->with('success', 'Producto Minciencias rechazado.');
    }

    public function descargarArchivo(MincienciasProductFile $archivo): StreamedResponse
    {
        $this->authorize('minciencias.ver_detalle');
        $this->ensureDelGrupo($archivo->product);

        return $this->descargarArchivoPublico($archivo->archivo, $archivo->descripcion);
    }

    private function ensureDelGrupo(MincienciasProduct $producto): void
    {
        $miGrupo = GrupoInvestigacion::where('director_id', Auth::id())->first();

        if ($miGrupo === null || (int) $producto->grupo_investigacion_id !== (int) $miGrupo->id) {
            abort(403, 'Este producto Minciencias no pertenece a tu grupo de investigación.');
        }
    }
}
