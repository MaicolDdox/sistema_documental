<?php

namespace App\Http\Controllers\AsesorSemillero;

use App\Enums\EstadoEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\AsesorSemillero\StoreProductoRequest;
use App\Models\ExternalAdvisor;
use App\Models\Product;
use App\Models\ProductAuthor;
use App\Models\Project;
use App\Models\ProjectAuthor;
use App\Models\Seedling;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductoController extends Controller
{
    // ──────────────────────────────────────────────────
    // HELPERS
    // ──────────────────────────────────────────────────

    /** Todos los semilleros del asesor autenticado. */
    private function getSemillerosDelAsesor(): \Illuminate\Database\Eloquent\Collection
    {
        $advisor = ExternalAdvisor::where('user_id', Auth::id())->first();
        if (!$advisor) return collect();

        return Seedling::whereHas('seedlingAdvisors', function ($q) use ($advisor) {
            $q->where('external_advisor_id', $advisor->id)
              ->where('activo', true);
        })->get(['id', 'nombre']);
    }

    /** Primer semillero del asesor (backwards compat). */
    private function getSemilleroDelAsesor(): ?Seedling
    {
        return $this->getSemillerosDelAsesor()->first();
    }

    private function getProyectosDelSemillero(int $seedlingId): \Illuminate\Database\Eloquent\Collection
    {
        $projectIds = DB::table('project_seedlings')
            ->where('seedling_id', $seedlingId)
            ->pluck('project_id');

        return Project::whereIn('id', $projectIds)->orderBy('nombre')->get(['id', 'nombre']);
    }

    private function getAllProjectIdsDelAsesor(): \Illuminate\Support\Collection
    {
        $semilleroIds = $this->getSemillerosDelAsesor()->pluck('id');

        return DB::table('project_seedlings')
            ->whereIn('seedling_id', $semilleroIds)
            ->pluck('project_id')
            ->unique();
    }

    // ──────────────────────────────────────────────────
    // AJAX ENDPOINTS
    // ──────────────────────────────────────────────────

    /** GET /asesor-semillero/api/semillero/{seedling_id}/proyectos */
    public function apiProyectosPorSemillero(int $seedling_id): JsonResponse
    {
        // Verificar que el asesor pertenece a este semillero
        $semilleros = $this->getSemillerosDelAsesor();
        if (!$semilleros->contains('id', $seedling_id)) {
            return response()->json([]);
        }

        $proyectos = $this->getProyectosDelSemillero($seedling_id);
        return response()->json($proyectos);
    }

    /** GET /asesor-semillero/api/proyecto/{project_id}/autores */
    public function apiAutoresPorProyecto(int $project_id): JsonResponse
    {
        $autores = ProjectAuthor::with('user.person')
            ->where('project_id', $project_id)
            ->where('activo', true)
            ->get()
            ->map(fn($pa) => [
                'id'     => $pa->id,
                'nombre' => trim(
                    ($pa->user?->person?->primer_nombre ?? '') . ' ' .
                    ($pa->user?->person?->segundo_nombre ?? '') . ' ' .
                    ($pa->user?->person?->primer_apellido ?? '') . ' ' .
                    ($pa->user?->person?->segundo_apellido ?? '')
                ),
            ]);

        return response()->json($autores);
    }

    // ──────────────────────────────────────────────────
    // INDEX
    // ──────────────────────────────────────────────────

    /** Permiso: productos.listar */
    public function index(): View
    {
        $productIds  = collect();
        $productos   = collect();
        $proyectos   = collect();
        $semilleros  = $this->getSemillerosDelAsesor();

        if ($semilleros->isNotEmpty()) {
            $semilleroIds = $semilleros->pluck('id');
            $projectIds   = DB::table('project_seedlings')
                ->whereIn('seedling_id', $semilleroIds)
                ->pluck('project_id');

            $query = Product::with(['project', 'productAuthors.projectAuthor.user.person'])
                ->whereIn('project_id', $projectIds)
                ->orderByDesc('updated_at');

            if (request()->filled('proyecto')) {
                $query->where('project_id', request('proyecto'));
            }

            $productos  = $query->paginate(10)->withQueryString();

            // Todos los proyectos de todos los semilleros del asesor
            $proyectos = Project::whereIn('id', $projectIds)->orderBy('nombre')->get(['id', 'nombre']);
        }

        return view('asesor_semillero.productos.index', compact('semilleros', 'productos', 'proyectos'));
    }

    // ──────────────────────────────────────────────────
    // CREATE
    // ──────────────────────────────────────────────────

    /** Permiso: productos.registrar */
    public function create(?int $proyecto_id = null): View
    {
        $semilleros = $this->getSemillerosDelAsesor();

        // Precargar autores si ya hay proyecto seleccionado
        $autoresProyecto = collect();
        $semilleroSeleccionado = null;
        if ($proyecto_id) {
            $autoresProyecto = ProjectAuthor::with('user.person')
                ->where('project_id', $proyecto_id)
                ->where('activo', true)
                ->get();
            // Detectar semillero del proyecto
            $sa = DB::table('project_seedlings')->where('project_id', $proyecto_id)->first();
            $semilleroSeleccionado = $sa?->seedling_id;
        }

        return view('asesor_semillero.productos.create', compact(
            'semilleros', 'proyecto_id', 'autoresProyecto', 'semilleroSeleccionado'
        ));
    }

    // ──────────────────────────────────────────────────
    // STORE
    // ──────────────────────────────────────────────────

    /** Permiso: productos.registrar */
    public function store(StoreProductoRequest $request): RedirectResponse
    {
        $validated      = $request->validated();
        $allProjectIds  = $this->getAllProjectIdsDelAsesor();

        if ($allProjectIds->isEmpty()) {
            return redirect()->back()->with('error', 'No tienes semilleros asignados.');
        }

        if (!$allProjectIds->contains((int) $validated['project_id'])) {
            abort(403, 'El proyecto no pertenece a tus semilleros.');
        }

        DB::transaction(function () use ($validated, $request) {
            $archivoPath    = null;
            $urlRepositorio = null;

            $archivoNombre = null;
            if ($request->hasFile('archivo')) {
                $file = $request->file('archivo');
                $archivoPath   = $file->store('productos', 'public');
                $archivoNombre = $file->getClientOriginalName();
            }
            if (filled($validated['url_repositorio'] ?? null)) {
                $urlRepositorio = $validated['url_repositorio'];
            }

            $product = Product::create([
                'project_id'      => $validated['project_id'],
                'nombre'          => $validated['nombre'],
                'archivo'         => $archivoPath,
                'archivo_nombre'  => $archivoNombre,
                'url_repositorio' => $urlRepositorio,
                'estado'          => EstadoEnum::Activo,
            ]);

            // Autores seleccionados manualmente en el form
            $autorIds = $validated['autores'] ?? [];
            foreach ($autorIds as $projectAuthorId) {
                ProductAuthor::firstOrCreate([
                    'product_id'        => $product->id,
                    'project_author_id' => (int) $projectAuthorId,
                ]);
            }
        });

        return redirect()->route('asesor.productos.index')
            ->with('success', 'Producto registrado correctamente.');
    }

    // ──────────────────────────────────────────────────
    // SHOW
    // ──────────────────────────────────────────────────

    /** Permiso: productos.ver_detalle */
    public function show(int $id): View
    {
        $product = $this->findProductoDelAsesor($id);
        $autores = ProductAuthor::with('projectAuthor.user.person')
            ->where('product_id', $id)->get();

        // Obtener el semillero del proyecto
        $sa = DB::table('project_seedlings')
            ->join('seedlings', 'seedlings.id', '=', 'project_seedlings.seedling_id')
            ->where('project_seedlings.project_id', $product->project_id)
            ->select('seedlings.nombre as nombre')
            ->first();
        $semilleroNombre = $sa?->nombre;

        return view('asesor_semillero.productos.show', compact('product', 'autores', 'semilleroNombre'));
    }

    // ──────────────────────────────────────────────────
    // EDIT
    // ──────────────────────────────────────────────────

    /** Permiso: productos.editar */
    public function edit(int $id): View
    {
        $product    = $this->findProductoDelAsesor($id);
        $semilleros = $this->getSemillerosDelAsesor();

        $sa = DB::table('project_seedlings')->where('project_id', $product->project_id)->first();
        $semilleroSeleccionado = $sa?->seedling_id;

        // Todos los proyectos del asesor (cualquier semillero) para el select principal
        $proyectos = Project::whereIn('id', $this->getAllProjectIdsDelAsesor())
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        $autoresProyecto = ProjectAuthor::with('user.person')
            ->where('project_id', $product->project_id)
            ->where('activo', true)
            ->get();

        $autoresSeleccionados = ProductAuthor::where('product_id', $id)
            ->pluck('project_author_id')
            ->toArray();

        return view('asesor_semillero.productos.edit', compact(
            'product', 'semilleros', 'semilleroSeleccionado',
            'proyectos', 'autoresProyecto', 'autoresSeleccionados'
        ));
    }

    // ──────────────────────────────────────────────────
    // UPDATE
    // ──────────────────────────────────────────────────

    /** Permiso: productos.editar */
    public function update(StoreProductoRequest $request, int $id): RedirectResponse
    {
        $product     = $this->findProductoDelAsesor($id);
        $validated   = $request->validated();

        DB::transaction(function () use ($product, $validated, $request) {
            $archivoPath    = $product->archivo;
            $archivoNombre  = $product->archivo_nombre;
            $urlRepositorio = $product->url_repositorio;

            if ($request->hasFile('archivo')) {
                if ($product->archivo && Storage::disk('public')->exists($product->archivo)) {
                    Storage::disk('public')->delete($product->archivo);
                }
                $file           = $request->file('archivo');
                $archivoPath    = $file->store('productos', 'public');
                $archivoNombre  = $file->getClientOriginalName();
            }
            if (filled($validated['url_repositorio'] ?? null)) {
                $urlRepositorio = $validated['url_repositorio'];
            }

            $product->update([
                'nombre'          => $validated['nombre'],
                'archivo'         => $archivoPath,
                'archivo_nombre'  => $archivoNombre,
                'url_repositorio' => $urlRepositorio,
            ]);

            // Sincronizar autores
            $autorIds = $validated['autores'] ?? [];
            ProductAuthor::where('product_id', $product->id)->delete();
            foreach ($autorIds as $projectAuthorId) {
                ProductAuthor::firstOrCreate([
                    'product_id'        => $product->id,
                    'project_author_id' => (int) $projectAuthorId,
                ]);
            }
        });

        return redirect()->route('asesor.productos.show', $id)
            ->with('success', 'Producto actualizado correctamente.');
    }

    // ──────────────────────────────────────────────────
    // DOWNLOAD
    // ──────────────────────────────────────────────────

    /** Descarga el archivo del producto con su nombre original */
    public function download(int $id): \Symfony\Component\HttpFoundation\StreamedResponse|RedirectResponse
    {
        $product = $this->findProductoDelAsesor($id);

        if (!$product->archivo || !Storage::disk('public')->exists($product->archivo)) {
            return redirect()->back()->with('error', 'El archivo no existe en el servidor.');
        }

        $downloadName = $product->archivo_nombre ?? basename($product->archivo);
        return Storage::disk('public')->download($product->archivo, $downloadName);
    }

    // ──────────────────────────────────────────────────
    // HELPER PRIVADO
    // ──────────────────────────────────────────────────

    private function findProductoDelAsesor(int $productId): Product
    {
        $allProjectIds = $this->getAllProjectIdsDelAsesor();

        $product = Product::with(['project', 'productAuthors.projectAuthor.user.person', 'productEvidences'])
            ->findOrFail($productId);

        if (!$allProjectIds->contains($product->project_id)) {
            abort(403, 'Este producto no pertenece a tus semilleros.');
        }

        return $product;
    }
}
