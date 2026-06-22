<?php

namespace App\Http\Controllers\AsesorSemillero;

use App\Http\Controllers\Controller;
use App\Http\Requests\AsesorSemillero\StoreEvidenciaRequest;
use App\Models\Product;
use App\Models\ProductEvidence;
use App\Models\Project;
use App\Models\ProjectEvidence;
use App\Models\Seedling;
use App\Support\AsesorSemilleroContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class EvidenciaController extends Controller
{
    /**
     * Permiso: evidencias.listar
     * Lista las evidencias de un proyecto.
     */
    public function listarEvidenciasProyecto(int $proyecto_id): View
    {
        $semillero = AsesorSemilleroContext::semilleroVinculadoAlProyectoParaAsesor($proyecto_id);
        $proyecto  = $this->findProyecto($proyecto_id, $semillero);

        $evidencias = ProjectEvidence::where('project_id', $proyecto_id)
            ->with('uploadedBy.person')
            ->latest()
            ->get();

        return view('asesor_semillero.evidencias.proyecto', compact('proyecto', 'evidencias', 'semillero'));
    }

    /**
     * Permiso: evidencias.listar
     * Lista las evidencias de un producto.
     */
    public function listarEvidenciasProducto(int $producto_id): View
    {
        $producto = Product::findOrFail($producto_id);
        $semillero = AsesorSemilleroContext::semilleroVinculadoAlProyectoParaAsesor($producto->project_id);
        $producto  = $this->findProducto($producto_id, $semillero);

        $evidencias = ProductEvidence::where('product_id', $producto_id)
            ->with('uploadedBy.person')
            ->latest()
            ->get();

        return view('asesor_semillero.evidencias.producto', compact('producto', 'evidencias', 'semillero'));
    }

    /**
     * Permiso: evidencias.subir_proyecto
     * Sube una evidencia para un proyecto.
     */
    public function subirEvidenciaProyecto(StoreEvidenciaRequest $request, int $proyecto_id): RedirectResponse
    {
        $semillero = AsesorSemilleroContext::semilleroVinculadoAlProyectoParaAsesor($proyecto_id);
        $proyecto  = $this->findProyecto($proyecto_id, $semillero);
        $validated = $request->validated();

        $path = $request->file('archivo')->store('evidencias/proyectos', 'public');
        if ($path === false) {
            return redirect()->back()->with('error', 'No se pudo guardar el archivo. Verifica los permisos de almacenamiento.');
        }
        ProjectEvidence::create([
            'project_id'  => $proyecto->id,
            'nombre'      => $validated['nombre'],
            'archivo'     => $path,
            'url_archivo' => Storage::disk('public')->url($path),
            'descripcion' => $validated['descripcion'] ?? '',
            'uploaded_by' => Auth::id(),
        ]);

        return redirect()->route('asesor.evidencias.proyecto.index', $proyecto_id)
            ->with('success', 'Evidencia subida correctamente.');
    }

    /**
     * Permiso: evidencias.subir_producto
     * Sube una evidencia para un producto.
     */
    public function subirEvidenciaProducto(StoreEvidenciaRequest $request, int $producto_id): RedirectResponse
    {
        $productoPre = Product::findOrFail($producto_id);
        $semillero = AsesorSemilleroContext::semilleroVinculadoAlProyectoParaAsesor($productoPre->project_id);
        $this->findProducto($producto_id, $semillero);
        $validated = $request->validated();

        $path = $request->file('archivo')->store('evidencias/productos', 'public');
        if ($path === false) {
            return redirect()->back()->with('error', 'No se pudo guardar el archivo. Verifica los permisos de almacenamiento.');
        }

        ProductEvidence::create([
            'product_id'  => $producto_id,
            'nombre'      => $validated['nombre'],
            'archivo'     => $path,
            'url_archivo' => Storage::disk('public')->url($path),
            'descripcion' => $validated['descripcion'] ?? '',
            'uploaded_by' => Auth::id(),
        ]);

        return redirect()->route('asesor.evidencias.producto.index', $producto_id)
            ->with('success', 'Evidencia subida correctamente.');
    }

    /**
     * Permiso: evidencias.listar
     * Descarga una evidencia de proyecto. Verifica que pertenezca al semillero del asesor.
     */
    public function descargarEvidenciaProyecto(int $proyecto_id, int $evidencia_id): \Symfony\Component\HttpFoundation\StreamedResponse|RedirectResponse
    {
        $semillero = AsesorSemilleroContext::semilleroVinculadoAlProyectoParaAsesor($proyecto_id);
        $this->findProyecto($proyecto_id, $semillero);

        $evidencia = ProjectEvidence::where('project_id', $proyecto_id)->findOrFail($evidencia_id);

        if (! Storage::disk('public')->exists($evidencia->archivo)) {
            return back()->withErrors(['error' => 'El archivo no se encontró en el servidor.']);
        }

        return Storage::disk('public')->download(
            $evidencia->archivo,
            $evidencia->nombre ?? basename($evidencia->archivo)
        );
    }

    /**
     * Permiso: evidencias.listar
     * Descarga una evidencia de producto. Verifica que pertenezca al semillero del asesor.
     */
    public function descargarEvidenciaProducto(int $producto_id, int $evidencia_id): \Symfony\Component\HttpFoundation\StreamedResponse|RedirectResponse
    {
        $productoPre = Product::findOrFail($producto_id);
        $semillero   = AsesorSemilleroContext::semilleroVinculadoAlProyectoParaAsesor($productoPre->project_id);
        $this->findProducto($producto_id, $semillero);

        $evidencia = ProductEvidence::where('product_id', $producto_id)->findOrFail($evidencia_id);

        if (! Storage::disk('public')->exists($evidencia->archivo)) {
            return back()->withErrors(['error' => 'El archivo no se encontró en el servidor.']);
        }

        return Storage::disk('public')->download(
            $evidencia->archivo,
            $evidencia->nombre ?? basename($evidencia->archivo)
        );
    }

    /**
     * Permiso: evidencias.eliminar_propia
     * Elimina una evidencia solo si fue subida por el usuario autenticado.
     * Detecta automáticamente si es de proyecto o de producto.
     */
    public function destroy(int $id): RedirectResponse
    {
        // Intentar encontrar en project_evidences primero
        $evidencia = ProjectEvidence::find($id);
        $tipo      = 'proyecto';
        $entityId  = $evidencia?->project_id;

        if (!$evidencia) {
            $evidencia = ProductEvidence::findOrFail($id);
            $tipo      = 'producto';
            $entityId  = $evidencia->product_id;
        }

        // Verificar autoría
        if ($evidencia->uploaded_by !== Auth::id()) {
            abort(403, 'Solo puedes eliminar evidencias que tú hayas subido.');
        }

        // Eliminar archivo físico del storage
        if ($evidencia->archivo && Storage::disk('public')->exists($evidencia->archivo)) {
            Storage::disk('public')->delete($evidencia->archivo);
        }

        $evidencia->delete();

        $route = $tipo === 'proyecto'
            ? route('asesor.evidencias.proyecto.index', $entityId)
            : route('asesor.evidencias.producto.index', $entityId);

        return redirect($route)->with('success', 'Evidencia eliminada correctamente.');
    }

    /**
     * Verifica que el proyecto pertenezca al semillero del asesor.
     */
    private function findProyecto(int $projectId, ?Seedling $semillero): Project
    {
        if (! $semillero) {
            abort(403, 'No tienes un semillero asignado.');
        }

        if (! $semillero->projectIds()->contains($projectId)) {
            abort(403, 'Este proyecto no pertenece a tu semillero.');
        }

        return Project::findOrFail($projectId);
    }

    /**
     * Verifica que el producto pertenezca al semillero del asesor.
     */
    private function findProducto(int $productId, ?Seedling $semillero): Product
    {
        if (! $semillero) {
            abort(403, 'No tienes un semillero asignado.');
        }

        $product = Product::findOrFail($productId);

        if (! $semillero->projectIds()->contains($product->project_id)) {
            abort(403, 'Este producto no pertenece a tu semillero.');
        }

        return $product;
    }
}
