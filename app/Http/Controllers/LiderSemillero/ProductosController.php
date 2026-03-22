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
use App\Services\Investigador\ProductoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class ProductosController extends Controller
{
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
            $projectIds = DB::table('project_seedlings')
                ->where('seedling_id', $semillero->id)
                ->pluck('project_id');

            $productos = Product::with(['project', 'productAuthors.projectAuthor.user.person', 'groupProducts', 'assignedInvestigator.person'])
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

        return redirect()->route('lider-sem.productos')
            ->with('success', 'Producto registrado correctamente. Estado de revisión: pendiente. Cuando esté aprobado, asígnalo a un investigador asociado de tu grupo para que lo formalice ante el Director.');
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

        if ($producto->groupProducts()->exists()) {
            return redirect()->route('lider-sem.productos')
                ->with('error', 'Este producto ya está vinculado al grupo de investigación.');
        }

        if ($producto->estado_revision !== EstadoRevisionEnum::Aprobado) {
            return redirect()->route('lider-sem.productos')
                ->with('error', 'Solo puedes asignar productos aprobados.');
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
        $validated = $validator->validated();

        $invId = (int) $validated['assigned_investigator_user_id'];
        $investigador = User::with(['roles', 'researchGroups'])->findOrFail($invId);

        if (! $investigador->hasRole('investigador_asociado')) {
            return redirect()->route('lider-sem.productos')
                ->with('error', 'El usuario seleccionado no tiene rol de investigador asociado.');
        }

        $idsGruposInv = $investigador->researchGroups->pluck('id')->map(fn ($id) => (int) $id)->all();
        if (! in_array((int) $semillero->research_group_id, $idsGruposInv, true)) {
            return redirect()->route('lider-sem.productos')
                ->with('error', 'El investigador debe pertenecer al mismo grupo de investigación vinculado a tu semillero.');
        }

        if (Auth::user()->training_center_id
            && (int) $investigador->training_center_id !== (int) Auth::user()->training_center_id) {
            return redirect()->route('lider-sem.productos')
                ->with('error', 'El investigador debe pertenecer a tu mismo centro de formación.');
        }

        $producto->update([
            'assigned_investigator_user_id' => $invId,
        ]);

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
