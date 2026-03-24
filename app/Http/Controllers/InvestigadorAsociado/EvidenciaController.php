<?php

namespace App\Http\Controllers\InvestigadorAsociado;

use App\Http\Controllers\Controller;
use App\Models\GroupProduct;
use App\Models\Product;
use App\Models\ProductEvidence;
use App\Models\Project;
use App\Models\ProjectEvidence;
use App\Services\Investigador\EvidenciaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EvidenciaController extends Controller
{
    use InvestigadorContext;

    public function __construct(private EvidenciaService $service) {}

    /**
     * Sube una evidencia a un producto del grupo.
     * Autorización: el investigador debe ser el author_id del GroupProduct.
     */
    public function storeProducto(Request $request, GroupProduct $producto): RedirectResponse
    {
        $this->authorize('subirEvidencia', $producto);

        $request->validate([
            'archivos'   => ['required', 'array', 'min:1', 'max:10'], // Máx 10 a la vez
            'archivos.*' => ['required', 'file', 'max:10240'], // 10 MB c/u
            'descripccion' => ['nullable', 'string', 'max:300'],
        ]);

        try {
            foreach ($request->file('archivos') as $file) {
                $this->service->subirParaProducto(
                    $file,
                    $producto,
                    Auth::id()
                );
            }
        } catch (\RuntimeException $e) {
            return back()->withErrors(['archivos' => $e->getMessage()]);
        }

        return back()->with('success', 'Evidencia(s) cargada(s) correctamente.');
    }

    /**
     * Sube una evidencia a un proyecto del investigador.
     */
    public function storeProyecto(Request $request, Project $proyecto): RedirectResponse
    {
        $this->authorize('update', $proyecto);

        $request->validate([
            'archivos'   => ['required', 'array', 'min:1', 'max:10'], // Máx 10 a la vez
            'archivos.*' => ['required', 'file', 'max:10240'], // 10 MB c/u
            'descripccion' => ['nullable', 'string', 'max:300'],
        ]);

        try {
            foreach ($request->file('archivos') as $file) {
                $this->service->subirParaProyecto(
                    $file,
                    $proyecto,
                    Auth::id()
                );
            }
        } catch (\RuntimeException $e) {
            return back()->withErrors(['archivos' => $e->getMessage()]);
        }

        return back()->with('success', 'Evidencia(s) de proyecto cargada(s) correctamente.');
    }

    /**
     * Elimina una evidencia de producto.
     * Solo si el investigador es el uploader y el producto NO está aprobado.
     */
    public function destroyProducto(ProductEvidence $evidencia): RedirectResponse
    {
        // Verificar que el uploader es el investigador autenticado
        abort_unless($evidencia->uploaded_by === Auth::id(), 403);

        // No se pueden eliminar evidencias de productos aprobados
        $groupProduct = $evidencia->product->groupProducts()->where('author_id', Auth::id())->first();
        if ($groupProduct) {
            abort_if(
                $groupProduct->estado_revision->value === 'aprobado',
                403,
                'No puedes eliminar evidencias de un producto aprobado.'
            );
        }

        $this->service->eliminarEvidenciaProducto($evidencia);

        return back()->with('success', 'Evidencia eliminada.');
    }

    /**
     * Elimina una evidencia de proyecto.
     * Solo si el investigador es el uploader.
     */
    public function destroyProyecto(ProjectEvidence $evidencia): RedirectResponse
    {
        abort_unless($evidencia->uploaded_by === Auth::id(), 403);

        $this->service->eliminarEvidenciaProyecto($evidencia);

        return back()->with('success', 'Evidencia de proyecto eliminada.');
    }
}
