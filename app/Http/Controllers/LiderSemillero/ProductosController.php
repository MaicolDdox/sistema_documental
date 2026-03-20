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
use App\Services\Investigador\ProductoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductosController extends Controller
{
    /**
     * Lista los productos del semillero del líder. Solo productos de su semillero; no puede aprobar los propios.
     */
    public function index(): View
    {
        $semillero = Auth::user()->ledSeedlings()->first();
        $productos = collect();
        $proyectosParaRegistro = collect();

        if ($semillero) {
            $projectIds = DB::table('project_seedlings')
                ->where('seedling_id', $semillero->id)
                ->pluck('project_id');

            $productos = Product::with(['project', 'productAuthors.projectAuthor.user.person', 'groupProducts'])
                ->whereIn('project_id', $projectIds)
                ->orderBy('updated_at', 'desc')
                ->get()
                ->map(function ($prod) {
                    $autorPrincipal = $prod->productAuthors->first()?->projectAuthor?->user;
                    $prod->es_mio = $autorPrincipal && $autorPrincipal->id === Auth::id();
                    $prod->autor = $autorPrincipal;
                    $prod->ya_en_grupo = $prod->groupProducts->isNotEmpty();
                    return $prod;
                });

            // Proyectos del semillero donde el usuario es autor (para registrar producto)
            $proyectosParaRegistro = Project::whereIn('id', $projectIds)
                ->whereHas('projectAuthors', function ($q) {
                    $q->where('user_id', Auth::id());
                })
                ->orderBy('nombre')
                ->get(['id', 'nombre']);
        }

        return view('lider_semillero.productos.index', [
            'semillero' => $semillero,
            'productos' => $productos,
            'proyectosParaRegistro' => $proyectosParaRegistro,
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

        $projectIds = DB::table('project_seedlings')
            ->where('seedling_id', $semillero->id)
            ->pluck('project_id');
        $esAutorEnProyecto = DB::table('project_authors')
            ->where('project_id', $request->input('project_id'))
            ->where('user_id', Auth::id())
            ->exists();
        if (!in_array((int) $request->input('project_id'), $projectIds->toArray()) || !$esAutorEnProyecto) {
            abort(403, 'Debes ser autor en un proyecto de tu semillero para registrar un producto.');
        }

        $tieneRepositorio = $request->boolean('tiene_repositorio');
        $rules = [
            'titulo' => 'required|string|max:500',
            'project_id' => 'required|exists:projects,id',
            'tiene_repositorio' => 'required|boolean',
            'autores' => 'required|array',
            'autores.*' => 'integer|exists:project_authors,id',
        ];
        if ($tieneRepositorio) {
            $rules['url_repositorio'] = 'required|url|max:500';
        } else {
            $rules['evidencia'] = 'required|file|max:10240'; // 10MB
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
        }

        $product = Product::create([
            'project_id' => $validated['project_id'],
            'nombre' => $validated['titulo'],
            'archivo' => $archivoProducto,
            'url_repositorio' => $tieneRepositorio ? $validated['url_repositorio'] : null,
            'estado' => EstadoEnum::Activo,
            'estado_revision' => EstadoRevisionEnum::Pendiente,
        ]);

        // Vincular autores seleccionados al producto
        $autorIds = $validated['autores'] ?? [];
        foreach ($autorIds as $projectAuthorId) {
            ProductAuthor::firstOrCreate([
                'product_id'        => $product->id,
                'project_author_id' => (int) $projectAuthorId,
            ]);
        }

        $groupProductData = [
            'author_id' => Auth::id(),
            'product_id' => $product->id,
            'tipo_proyecto_origen' => TipoProyectoOrigenEnum::Semilleros,
            'codigo_proyecto_origen' => '0',
            'titulo' => $validated['titulo'],
            'anio_publicacion' => (int) now()->format('Y'),
            'nombre_programa_formacion_impacto' => 'N/A',
            'minciencias_typology_id' => $validated['minciencias_typology_id'],
            'minciencias_subcategory_id' => $subcategory->id,
            'knowledge_grand_area_id' => $knowledgeGrand->id,
            'knowledge_area_id' => $knowledgeArea->id,
            'tiene_repositorio' => $tieneRepositorio,
            'url_repositorio' => $tieneRepositorio ? $validated['url_repositorio'] : null,
            'evidencia' => !$tieneRepositorio ? $archivoProducto : null,
            'autoriza_datos' => false,
            'estado_revision' => EstadoRevisionEnum::Pendiente,
        ];
        GroupProduct::create($groupProductData);

        return redirect()->route('lider-sem.productos')
            ->with('success', 'Producto registrado correctamente. Estado de revisión: pendiente.');
    }

    /**
     * Formulario para registrar un producto directamente para el grupo de investigación
     * asociado al semillero del líder.
     */
    public function createGrupo(): View
    {
        $semillero = Auth::user()->ledSeedlings()->with('researchGroup')->first();
        if (!$semillero || !$semillero->researchGroup) {
            abort(403, 'Tu semillero no tiene un grupo de investigación asociado.');
        }

        $proyectos = $semillero->projects()
            ->orderBy('nombre')
            ->get();

        $tipologias        = MincienciasTypology::orderBy('nombre')->get();
        $subcategorias     = MincienciasSubcategory::orderBy('nombre')->get();
        $grandesAreas      = KnowledgeGrandArea::orderBy('nombre')->get();
        $areasConocimiento = KnowledgeArea::orderBy('nombre')->get();

        return view('lider_semillero.productos.create_grupo', [
            'proyectos'          => $proyectos,
            'tipologias'         => $tipologias,
            'subcategorias'      => $subcategorias,
            'grandesAreas'       => $grandesAreas,
            'areasConocimiento'  => $areasConocimiento,
            'semillero'          => $semillero,
            'grupo'              => $semillero->researchGroup,
        ]);
    }

    /**
     * Registra el producto en el grupo de investigación usando el mismo servicio
     * que emplean los investigadores.
     */
    public function storeGrupo(Request $request, ProductoService $service): RedirectResponse
    {
        $semillero = Auth::user()->ledSeedlings()->with('researchGroup')->first();
        if (!$semillero || !$semillero->researchGroup) {
            abort(403, 'Tu semillero no tiene un grupo de investigación asociado.');
        }

        $grupoId = $semillero->researchGroup->id;

        $validated = $request->validate([
            'project_id'                       => ['required', 'exists:projects,id'],
            'titulo'                           => ['required', 'string', 'max:500'],
            'descripccion'                     => ['nullable', 'string'],
            'tipo_proyecto_origen'             => ['required', 'string'],
            'campo_otro'                       => ['nullable', 'string', 'max:255'],
            'codigo_proyecto_origen'           => ['nullable', 'string', 'max:100'],
            'anio_publicacion'                 => ['required', 'integer', 'min:2000', 'max:' . (now()->year + 2)],
            'nombre_programa_formacion_impacto'=> ['nullable', 'string', 'max:300'],
            'minciencias_typology_id'          => ['nullable', 'exists:minciencias_typologies,id'],
            'minciencias_subcategory_id'       => ['nullable', 'exists:minciencias_subcategories,id'],
            'knowledge_grand_area_id'          => ['nullable', 'exists:knowledge_grand_areas,id'],
            'knowledge_area_id'                => ['nullable', 'exists:knowledge_areas,id'],
            'tiene_repositorio'                => ['boolean'],
            'url_repositorio'                  => ['nullable', 'url', 'max:500'],
            'autoriza_datos'                   => ['boolean'],
        ]);

        // Campos que el servicio espera pero aquí no usamos
        $validated['product_base_id'] = null;
        $validated['autores'] = [];

        try {
            $service->registrar($validated, Auth::id(), (int) $grupoId);
        } catch (\RuntimeException $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()
            ->route('lider-sem.productos')
            ->with('success', 'Producto registrado en el grupo de investigación. Estado pendiente para revisión del Director.');
    }

    /**
     * Toma un producto ya registrado por el asesor y aprobado,
     * y lo sube al grupo de investigación asociado al semillero del líder.
     */
    public function subirAGrupo(Product $producto, ProductoService $service): RedirectResponse
    {
        $semillero = Auth::user()->ledSeedlings()->with('researchGroup')->first();
        if (!$semillero || !$semillero->researchGroup) {
            return redirect()->route('lider-sem.productos')
                ->with('error', 'Tu semillero no tiene un grupo de investigación asociado.');
        }

        // Verificar que el producto pertenece al semillero del líder
        if (!$this->perteneceAlSemillero($producto, $semillero->id)) {
            abort(403, 'El producto no pertenece a tu semillero.');
        }

        // Si ya tiene un registro en el grupo, no duplicar
        if ($producto->groupProducts()->exists()) {
            return redirect()->route('lider-sem.productos')
                ->with('error', 'Este producto ya está vinculado al grupo de investigación.');
        }

        // Solo productos aprobados pasan al grupo
        if ($producto->estado_revision !== EstadoRevisionEnum::Aprobado) {
            return redirect()->route('lider-sem.productos')
                ->with('error', 'Solo puedes subir al grupo productos aprobados.');
        }

        $grupoId = $semillero->researchGroup->id;

        // Construimos datos mínimos para el servicio a partir del producto existente
        $validated = [
            'project_id'                       => $producto->project_id,
            'product_base_id'                  => $producto->id,
            'titulo'                           => $producto->nombre,
            'descripccion'                     => null,
            'tipo_proyecto_origen'             => TipoProyectoOrigenEnum::Semilleros->value,
            'campo_otro'                       => null,
            'codigo_proyecto_origen'           => null,
            'anio_publicacion'                 => (int) now()->year,
            'nombre_programa_formacion_impacto'=> null,
            'minciencias_typology_id'          => null,
            'minciencias_subcategory_id'       => null,
            'knowledge_grand_area_id'          => null,
            'knowledge_area_id'                => null,
            'tiene_repositorio'                => (bool) $producto->url_repositorio,
            'url_repositorio'                  => $producto->url_repositorio,
            'autoriza_datos'                   => false,
            'autores'                          => [],
        ];

        try {
            $service->registrar($validated, Auth::id(), (int) $grupoId);
        } catch (\RuntimeException $e) {
            return redirect()->route('lider-sem.productos')
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('lider-sem.productos')
            ->with('success', 'Producto enviado al grupo de investigación para revisión del Director.');
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
        $pertenece = DB::table('project_seedlings')
            ->where('project_id', $project_id)
            ->where('seedling_id', $semillero->id)
            ->exists();

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

        // Sincronizar también con el registro en group_products (tablero del investigador)
        $groupProduct = GroupProduct::where('product_id', $producto->id)->first();
        if ($groupProduct) {
            $groupProduct->estado_revision = EstadoRevisionEnum::Aprobado;
            $groupProduct->observaciones_revision = $request->input('observaciones');
            $groupProduct->save();
        }

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

        // Sincronizar también con el registro en group_products (tablero del investigador)
        $groupProduct = GroupProduct::where('product_id', $producto->id)->first();
        if ($groupProduct) {
            $groupProduct->estado_revision = EstadoRevisionEnum::Rechazado;
            $groupProduct->observaciones_revision = $validated['observaciones'];
            $groupProduct->save();
        }

        return redirect()->route('lider-sem.productos')
            ->with('success', 'Producto rechazado.');
    }

    private function perteneceAlSemillero(Product $producto, int $seedlingId): bool
    {
        if (!$producto->project_id) {
            return false;
        }
        return DB::table('project_seedlings')
            ->where('project_id', $producto->project_id)
            ->where('seedling_id', $seedlingId)
            ->exists();
    }
}
