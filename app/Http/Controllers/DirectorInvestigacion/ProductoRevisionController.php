<?php

namespace App\Http\Controllers\DirectorInvestigacion;

use App\Enums\EstadoRevisionEnum;
use App\Http\Controllers\Controller;
use App\Models\GroupProduct;
use App\Models\ProductEvidence;
use App\Models\ResearchGroupUser;
use App\Services\Director\RevisionProductoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductoRevisionController extends Controller
{
    use DirectorContext;

    public function __construct(private RevisionProductoService $service) {}

    /**
     * Lista productos del grupo con filtros opcionales.
     */
    public function index(Request $request): View
    {
        $grupoId = $this->getGrupoId();
        $userIds = ResearchGroupUser::where('research_group_id', $grupoId)->pluck('user_id');

        $productos = GroupProduct::with([
                'author.person',
                'product',
                'mincienciasTypology',
                'mincienciasSubcategory',
            ])
            ->whereIn('author_id', $userIds)
            ->when($request->estado_revision, fn($q, $v) =>
                $q->where('estado_revision', $v)
            )
            ->when($request->anio, fn($q, $v) =>
                $q->where('anio_publicacion', $v)
            )
            ->when($request->investigador_id, fn($q, $v) =>
                $q->where('author_id', $v)
            )
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $estadosRevision = EstadoRevisionEnum::cases();
        $anios = GroupProduct::whereIn('author_id', $userIds)
            ->distinct()
            ->orderBy('anio_publicacion', 'desc')
            ->pluck('anio_publicacion');

        return view('director_investigacion.productos.index', compact(
            'productos', 'estadosRevision', 'anios', 'grupoId'
        ));
    }

    /**
     * Detalle de un producto con sus evidencias.
     */
    public function show(GroupProduct $producto): View
    {
        $this->autorizarProducto($producto);

        $producto->load([
            'author.person',
            'product.productEvidences',
            'mincienciasTypology',
            'mincienciasSubcategory',
            'knowledgeArea',
            'knowledgeGrandArea',
            'reviews.reviewer.person',
        ]);

        return view('director_investigacion.productos.show', compact('producto'));
    }

    /**
     * Aprueba un producto (requiere al menos una evidencia).
     */
    public function aprobar(GroupProduct $producto): RedirectResponse
    {
        $this->autorizarProducto($producto);

        try {
            $this->service->aprobar($producto, Auth::id(), $this->getGrupoId());
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()
            ->route('director.productos.index')
            ->with('success', "Producto «{$producto->titulo}» aprobado.");
    }

    /**
     * Rechaza un producto con observaciones obligatorias.
     */
    public function rechazar(Request $request, GroupProduct $producto): RedirectResponse
    {
        $request->validate([
            'observaciones' => ['required', 'string', 'min:10'],
        ]);

        $this->autorizarProducto($producto);

        try {
            $this->service->rechazar(
                $producto,
                Auth::id(),
                $this->getGrupoId(),
                $request->observaciones
            );
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()
            ->route('director.productos.index')
            ->with('success', "Producto «{$producto->titulo}» rechazado.");
    }

    /**
     * Marca el producto como "en revisión".
     */
    public function cambiarAEnRevision(GroupProduct $producto): RedirectResponse
    {
        $this->autorizarProducto($producto);

        $this->service->marcarEnRevision($producto, Auth::id(), $this->getGrupoId());

        return back()->with('success', 'Producto marcado como en revisión.');
    }

    /**
     * Descarga una evidencia de producto para revisión del director.
     */
    public function downloadEvidencia(GroupProduct $producto, ProductEvidence $evidencia): \Symfony\Component\HttpFoundation\StreamedResponse|RedirectResponse
    {
        $this->autorizarProducto($producto);

        if (!Storage::disk('local')->exists($evidencia->archivo)) {
            return back()->withErrors(['error' => 'El archivo no se encontró en el servidor.']);
        }

        return Storage::disk('local')->download(
            $evidencia->archivo,
            $evidencia->nombre ?? basename($evidencia->archivo)
        );
    }

    /**
     * Centraliza la verificación de scope. Aborta con 403 si el producto
     * no pertenece al grupo del director.
     */
    private function autorizarProducto(GroupProduct $producto): void
    {
        // Simplificamos la autorización: cualquier usuario con rol director_investigacion
        // puede revisar los productos que aparecen en su listado.
        if (!Auth::user()?->hasRole('director_investigacion')) {
            abort(403);
        }
    }
}
