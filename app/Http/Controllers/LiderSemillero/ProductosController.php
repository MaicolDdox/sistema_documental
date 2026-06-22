<?php

namespace App\Http\Controllers\LiderSemillero;

use App\Enums\EstadoEnum;
use App\Enums\EstadoRevisionEnum;
use App\Enums\TipoProyectoOrigenEnum;
use App\Http\Controllers\Controller;
use App\Models\GroupProduct;
use App\Models\KnowledgeArea;
use App\Models\KnowledgeGrandArea;
use App\Models\MincienciasSubcategory;
use App\Models\MincienciasTypology;
use App\Models\Product;
use App\Models\ProductAuthor;
use App\Models\Project;
use App\Models\ProjectAuthor;
use App\Models\User;
use App\Services\LiderSemillero\ProductoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class ProductosController extends Controller
{
    public function __construct(private readonly ProductoService $productoService) {}
    /**
     * Lista los productos del semillero del líder. Solo productos de su semillero; no puede aprobar los propios.
     */
    public function index(): View
    {
        $semillero = Auth::user()->ledSeedlings()->with('researchGroup')->first();
        $productos = collect();
        $proyectosParaRegistro = collect();
        $investigadoresGrupo = collect();

        if ($semillero) {
            $projectIds = $semillero->projectIds();

            $productos = Product::with([
                    'project',
                    'productAuthors.projectAuthor.user.person',
                    'productEvidences.uploadedBy.person',
                    'groupProducts',
                    'assignedInvestigator.person',
                ])
                ->whereIn('project_id', $projectIds)
                ->orderBy('updated_at', 'desc')
                ->get()
                ->map(fn ($prod) => $this->productoService->anotarMetadatos($prod, Auth::id()));

            if ($semillero->research_group_id) {
                $investigadoresGrupo = User::query()
                    ->whereHas('roles', fn ($q) => $q->where('name', 'investigador_asociado'))
                    ->whereHas('researchGroups', fn ($q) => $q->where('research_groups.id', $semillero->research_group_id))
                    ->when(
                        Auth::user()->training_center_id,
                        fn ($q, $tc) => $q->where('training_center_id', $tc)
                    )
                    ->with('person')
                    ->orderBy('email')
                    ->get();
            }

            // Todos los proyectos del semillero (el líder puede registrar productos en cualquiera)
            $proyectosParaRegistro = Project::whereIn('id', $projectIds)
                ->orderBy('nombre')
                ->get(['id', 'nombre']);
        }

        return view('lider_semillero.productos.index', [
            'semillero' => $semillero,
            'productos' => $productos,
            'proyectosParaRegistro' => $proyectosParaRegistro,
            'investigadoresGrupo' => $investigadoresGrupo,
        ]);
    }

    /**
     * Muestra el detalle de un producto para revisión por parte del líder.
     */
    public function show(GroupProduct $groupProduct): View
    {
        $semillero = Auth::user()->ledSeedlings()->first();
        if (! $semillero || ! $this->perteneceAlSemillero($groupProduct, $semillero->id)) {
            abort(403, 'No puedes ver este producto.');
        }

        $groupProduct->load(['author.person', 'product.project', 'mincienciasTypology']);
        $groupProduct->es_mio = $groupProduct->author_id === Auth::id();

        return view('lider_semillero.productos.show', [
            'semillero' => $semillero,
            'producto' => $groupProduct,
        ]);
    }

    /**
     * Registra un nuevo producto (estado_revision = pendiente). El usuario debe ser autor en proyecto_autores del proyecto.
     */
    public function store(Request $request): RedirectResponse
    {
        $semillero = Auth::user()->ledSeedlings()->first();
        if (!$semillero) {
            return redirect()->route('lider-sem.productos')
                ->with('error', 'No tienes un semillero asignado como líder.');
        }

        $projectIds = $semillero->projectIds();
        if (!in_array((int) $request->input('project_id'), $projectIds->toArray())) {
            abort(403, 'El proyecto no pertenece a tu semillero.');
        }

        $tieneRepositorio = $request->boolean('tiene_repositorio');
        $rules = [
            'titulo'           => 'required|string|max:500',
            'project_id'       => 'required|exists:projects,id',
            'tiene_repositorio'=> 'required|boolean',
        ];
        if ($tieneRepositorio) {
            $rules['url_repositorio'] = 'required|url|max:500';
        } else {
            $rules['evidencia'] = 'required|file|mimes:pdf,doc,docx,zip|max:10240';
        }

        $validated = $request->validate($rules, [
            'titulo.required' => 'El título del producto es obligatorio.',
            'project_id.required' => 'El proyecto origen es obligatorio.',
            'url_repositorio.required' => 'La URL del repositorio es obligatoria cuando tiene repositorio en línea.',
            'evidencia.required' => 'El archivo del producto es obligatorio cuando no tiene repositorio en línea.',
        ]);

        $archivoProducto = '';
        if ($tieneRepositorio) {
            $archivoProducto = $validated['url_repositorio'];
        } else {
            $archivoProducto = $request->file('evidencia')->store('productos/evidencias', 'public');
            if ($archivoProducto === false) {
                return redirect()->back()->with('error', 'No se pudo guardar el archivo. Verifica los permisos de almacenamiento.');
            }
        }

        $product = Product::create([
            'project_id' => $validated['project_id'],
            'nombre' => $validated['titulo'],
            'archivo' => $archivoProducto,
            'url_repositorio' => $tieneRepositorio ? $validated['url_repositorio'] : null,
            'estado' => EstadoEnum::Activo,
            'estado_revision' => EstadoRevisionEnum::Pendiente,
        ]);

        return redirect()->route('lider-sem.productos')
            ->with('success', 'Producto registrado correctamente. Cuando lo apruebes, podrás enviarlo a un investigador asociado de tu grupo.');
    }


    /**
     * Versión del método de asignación que recibe el product_id en el cuerpo del request
     * en lugar de en la URL, para facilitar el uso desde formularios con Alpine.js.
     */
    public function asignarInvestigadorForm(Request $request): RedirectResponse
    {
        $request->validate(['product_id' => 'required|integer|exists:products,id']);
        $producto = Product::findOrFail($request->input('product_id'));
        return $this->asignarInvestigadorGrupo($request, $producto);
    }

    /**
     * Asigna un producto aprobado a un investigador asociado del mismo grupo de investigación del semillero;
     * el investigador lo formalizará desde su bandeja (subida al grupo).
     */
    public function asignarInvestigadorGrupo(Request $request, Product $producto): RedirectResponse
    {
        $semillero = Auth::user()->ledSeedlings()->with('researchGroup')->first();
        if (! $semillero || ! $semillero->research_group_id) {
            return redirect()->route('lider-sem.productos')
                ->with('error', 'Tu semillero no tiene un grupo de investigación asociado.');
        }

        if (! $this->perteneceAlSemillero($producto, $semillero->id)) {
            abort(403, 'El producto no pertenece a tu semillero.');
        }

        $validator = Validator::make($request->all(), [
            'assigned_investigator_user_id' => ['required', 'exists:users,id'],
        ], [
            'assigned_investigator_user_id.required' => 'Debes elegir un investigador asociado.',
        ]);
        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('open_asignar_product_id', $producto->id);
        }

        $invId = (int) $validator->validated()['assigned_investigator_user_id'];

        try {
            $this->productoService->asignarAInvestigador($producto, $invId, $semillero, Auth::user());
        } catch (\DomainException $e) {
            return redirect()->route('lider-sem.productos')->with('error', $e->getMessage());
        }

        return redirect()
            ->route('lider-sem.productos')
            ->with('success', 'Producto enviado al investigador asociado. Podrá formalizarlo y subirlo al grupo desde su bandeja.');
    }

    /**
     * Devuelve autores (project_authors) de un proyecto para el líder.
     */
    public function apiAutoresPorProyecto(int $project_id): \Illuminate\Http\JsonResponse
    {
        $semillero = Auth::user()->ledSeedlings()->first();
        if (!$semillero) {
            return response()->json([]);
        }

        // Verificar que el proyecto pertenece al semillero del líder
        $pertenece = $semillero->projectIds()->contains($project_id);

        if (!$pertenece) {
            return response()->json([]);
        }

        $autores = ProjectAuthor::with('user.person')
            ->where('project_id', $project_id)
            ->where('activo', true)
            ->get()
            ->map(function (ProjectAuthor $pa) {
                $p = $pa->user?->person;
                $nombre = $p
                    ? trim($p->primer_nombre.' '.$p->segundo_nombre.' '.$p->primer_apellido.' '.$p->segundo_apellido)
                    : ($pa->user?->email ?? 'Autor');

                return [
                    'id' => $pa->id,
                    'nombre' => $nombre,
                ];
            });

        return response()->json($autores);
    }

    /**
     * Aprueba un producto. Solo si pertenece al semillero del líder y no es autor del producto.
     * Observaciones opcionales.
     */
    public function aprobar(Request $request, Product $producto): RedirectResponse
    {
        $semillero = Auth::user()->ledSeedlings()->first();
        if (!$semillero) {
            abort(403, 'No tienes un semillero asignado como líder.');
        }

        $autorPrincipal = $producto->productAuthors->first()?->projectAuthor?->user;
        if ($autorPrincipal && $autorPrincipal->id === Auth::id()) {
            return redirect()->route('lider-sem.productos')
                ->with('error', 'No puedes aprobar tus propios productos.');
        }

        $producto->estado_revision = EstadoRevisionEnum::Aprobado;
        $producto->observacion_revision = $request->input('observaciones');
        $producto->save();

        // La sincronización con group_products la maneja el observer en GroupProduct::booted().
        // Si se desea propagar también al revés (Product → GroupProduct), los servicios
        // del Director (RevisionProductoService) son la fuente de verdad para ese flujo.

        return redirect()->route('lider-sem.productos')
            ->with('success', 'Producto aprobado correctamente.');
    }

    /**
     * Rechaza un producto. Solo si pertenece al semillero del líder y no es autor del producto.
     * Observaciones obligatorias.
     */
    public function rechazar(Request $request, Product $producto): RedirectResponse
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'observaciones' => 'required|string|max:2000',
        ], [
            'observaciones.required' => 'Las observaciones son obligatorias al rechazar.',
        ]);
        if ($validator->fails()) {
            return redirect()->route('lider-sem.productos')
                ->withErrors($validator)
                ->with('rechazar_id', $producto->id)
                ->withInput();
        }
        $validated = $validator->validated();

        $semillero = Auth::user()->ledSeedlings()->first();
        if (!$semillero) {
            abort(403, 'No tienes un semillero asignado como líder.');
        }

        $autorPrincipal = $producto->productAuthors->first()?->projectAuthor?->user;
        if ($autorPrincipal && $autorPrincipal->id === Auth::id()) {
            return redirect()->route('lider-sem.productos')
                ->with('error', 'No puedes rechazar tus propios productos.');
        }

        $producto->estado_revision = EstadoRevisionEnum::Rechazado;
        $producto->observacion_revision = $validated['observaciones'];
        $producto->save();

        // La sincronización con group_products la maneja el observer en GroupProduct::booted().

        return redirect()->route('lider-sem.productos')
            ->with('success', 'Producto rechazado.');
    }

    private function perteneceAlSemillero(Product $producto, int $seedlingId): bool
    {
        if (! $producto->project_id) {
            return false;
        }
        $semillero = \App\Models\Seedling::find($seedlingId);

        return $semillero && $semillero->projectIds()->contains($producto->project_id);
    }
}
