<?php

namespace App\Http\Controllers\InvestigadorAsociado;

use App\Http\Controllers\Controller;
use App\Models\GroupProduct;
use App\Models\KnowledgeArea;
use App\Models\KnowledgeGrandArea;
use App\Models\MincienciasSubcategory;
use App\Models\MincienciasTypology;
use App\Models\Project;
use App\Services\Investigador\ProductoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProductoController extends Controller
{
    use InvestigadorContext;

    public function __construct(private ProductoService $service) {}

    /**
     * Lista los GroupProducts del investigador con estado de revisión.
     */
    public function index(Request $request): View
    {
        $productos = GroupProduct::with([
                'product.project',
                'mincienciasTypology',
                'mincienciasSubcategory',
            ])
            ->where('author_id', Auth::id())
            ->when($request->estado_revision, fn($q, $v) =>
                $q->where('estado_revision', $v)
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('investigador.productos.index', compact('productos'));
    }

    /**
     * Muestra productos aprobados por el semillero pendientes de formalización.
     */
    public function bandejaSemilleros(): View
    {
        $proyectosIds = \App\Models\ProjectAuthor::where('user_id', Auth::id())->pluck('project_id');
        $uid = Auth::id();

        $productos = \App\Models\Product::with(['project', 'productAuthors.projectAuthor.user.person', 'assignedInvestigator.person'])
            ->where('estado_revision', \App\Enums\EstadoRevisionEnum::Aprobado)
            ->whereDoesntHave('groupProducts')
            ->where(function ($q) use ($proyectosIds, $uid) {
                $q->where('assigned_investigator_user_id', $uid)
                    ->orWhere(function ($q2) use ($proyectosIds) {
                        $q2->whereNull('assigned_investigator_user_id')
                            ->whereIn('project_id', $proyectosIds);
                    });
            })
            ->latest()
            ->paginate(15);

        return view('investigador.productos.bandeja', compact('productos'));
    }

    /**
     * Prepara el formulario para formalizar un producto que viene de semillero.
     */
    public function formalizarSemillero(\App\Models\Product $producto): View
    {
        $soyAutor = \App\Models\ProjectAuthor::where('user_id', Auth::id())
            ->where('project_id', $producto->project_id)->exists();
        if ($producto->assigned_investigator_user_id !== null) {
            abort_unless(
                (int) $producto->assigned_investigator_user_id === (int) Auth::id(),
                403,
                'Este producto fue enviado a otro investigador asociado.'
            );
        } else {
            abort_unless($soyAutor, 403, 'No estás autorizado para formalizar este producto.');
        }

        $proyectos = Project::where('id', $producto->project_id)->get();

        $tipologias      = MincienciasTypology::orderBy('nombre')->get();
        $subcategorias   = MincienciasSubcategory::orderBy('nombre')->get();
        $grandesAreas    = KnowledgeGrandArea::orderBy('nombre')->get();
        $areasConocimiento = KnowledgeArea::orderBy('nombre')->get();

        return view('investigador.productos.form', [
            'groupProduct'     => null,
            'productBase'      => $producto,
            'proyectos'        => $proyectos,
            'tipologias'       => $tipologias,
            'subcategorias'    => $subcategorias,
            'grandesAreas'     => $grandesAreas,
            'areasConocimiento' => $areasConocimiento,
        ]);
    }

    /**
     * Formulario para registrar un nuevo producto del semillero.
     */
    public function create(Request $request): View
    {
        $proyectos = Project::where('project_creator_id', Auth::id())
            ->orderBy('nombre')
            ->get();
            
        $proyectoSeleccionado = $request->query('project_id');

        $tipologias      = MincienciasTypology::orderBy('nombre')->get();
        $subcategorias   = MincienciasSubcategory::orderBy('nombre')->get();
        $grandesAreas    = KnowledgeGrandArea::orderBy('nombre')->get();
        $areasConocimiento = KnowledgeArea::orderBy('nombre')->get();

        return view('investigador.productos.form', [
            'groupProduct'     => null,
            'productBase'      => null,
            'proyectos'        => $proyectos,
            'tipologias'       => $tipologias,
            'subcategorias'    => $subcategorias,
            'grandesAreas'     => $grandesAreas,
            'areasConocimiento' => $areasConocimiento,
            'proyectoSeleccionado' => $proyectoSeleccionado,
        ]);
    }

    /**
     * Registra el producto en el grupo.
     */
    public function store(Request $request): RedirectResponse
    {
        $grupoId = $this->getGrupoId();

        $validated = $request->validate([
            'project_id'                       => ['required', 'exists:projects,id'],
            'product_base_id'                  => ['nullable', 'exists:products,id'],
            'titulo'                           => ['required', 'string', 'max:500'],
            'descripccion'                     => ['nullable', 'string'],
            'tipo_proyecto_origen'             => ['required', 'string'],
            'campo_otro'                       => ['nullable', 'string', 'max:255'],
            'codigo_proyecto_origen'           => ['nullable', 'string', 'max:100'],
            'anio_publicacion'                 => ['required', 'integer', 'min:2000', 'max:' . now()->year + 2],
            'nombre_programa_formacion_impacto' => ['nullable', 'string', 'max:300'],
            'minciencias_typology_id'          => ['nullable', 'exists:minciencias_typologies,id'],
            'minciencias_subcategory_id'       => ['nullable', 'exists:minciencias_subcategories,id'],
            'knowledge_grand_area_id'          => ['nullable', 'exists:knowledge_grand_areas,id'],
            'knowledge_area_id'                => ['nullable', 'exists:knowledge_areas,id'],
            'tiene_repositorio'                => ['boolean'],
            'url_repositorio'                  => ['nullable', 'url', 'max:500'],
            'autoriza_datos'                   => ['boolean'],
            'autores'                          => ['nullable', 'array'],
            'autores.*'                        => ['exists:users,id'],
        ]);

        try {
            $groupProduct = $this->service->registrar($validated, Auth::id(), $grupoId);
        } catch (\RuntimeException $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()
            ->route('investigador.productos.show', $groupProduct)
            ->with('success', "Producto «{$groupProduct->titulo}» registrado en estado pendiente. El Director revisará pronto.");
    }

    /**
     * Detalle del producto con estado, observaciones e historial.
     */
    public function show(GroupProduct $producto): View
    {
        $this->authorize('view', $producto);

        $producto->load([
            'product.project',
            'product.productEvidences.uploadedBy',
            'product.productAuthors.user.person',
            'mincienciasTypology',
            'mincienciasSubcategory',
            'knowledgeArea',
            'knowledgeGrandArea',
            'reviews.reviewer.person',
        ]);

        return view('investigador.productos.show', compact('producto'));
    }

    /**
     * Formulario de corrección (solo para productos rechazados).
     */
    public function edit(GroupProduct $producto): View
    {
        $this->authorize('update', $producto);

        $tipologias        = MincienciasTypology::orderBy('nombre')->get();
        $subcategorias     = MincienciasSubcategory::orderBy('nombre')->get();
        $grandesAreas      = KnowledgeGrandArea::orderBy('nombre')->get();
        $areasConocimiento = KnowledgeArea::orderBy('nombre')->get();

        return view('investigador.productos.form', [
            'groupProduct'      => $producto->load('product'),
            'productBase'       => null,
            'proyectos'         => collect(), // No se cambia el proyecto al corregir
            'tipologias'        => $tipologias,
            'subcategorias'     => $subcategorias,
            'grandesAreas'      => $grandesAreas,
            'areasConocimiento' => $areasConocimiento,
        ]);
    }

    /**
     * Aplica la corrección al producto rechazado y lo vuelve a pendiente.
     */
    public function update(Request $request, GroupProduct $producto): RedirectResponse
    {
        $this->authorize('update', $producto);

        $validated = $request->validate([
            'titulo'                     => ['required', 'string', 'max:500'],
            'descripccion'               => ['nullable', 'string'],
            'anio_publicacion'           => ['required', 'integer', 'min:2000'],
            'minciencias_typology_id'    => ['nullable', 'exists:minciencias_typologies,id'],
            'minciencias_subcategory_id' => ['nullable', 'exists:minciencias_subcategories,id'],
            'knowledge_grand_area_id'    => ['nullable', 'exists:knowledge_grand_areas,id'],
            'knowledge_area_id'          => ['nullable', 'exists:knowledge_areas,id'],
            'tiene_repositorio'          => ['boolean'],
            'url_repositorio'            => ['nullable', 'url', 'max:500'],
        ]);

        try {
            $this->service->corregir($producto, $validated);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()
            ->route('investigador.productos.show', $producto)
            ->with('success', 'Producto corregido y enviado nuevamente al Director para revisión.');
    }

    /**
     * Elimina un producto en estado pendiente.
     */
    public function destroy(GroupProduct $producto): RedirectResponse
    {
        $this->authorize('delete', $producto);

        $titulo = $producto->titulo;
        $producto->product->delete(); // Elimina Product + cascade groupProducts
        // GroupProduct se eliminará por cascade FK o manualmente:
        $producto->delete();

        return redirect()
            ->route('investigador.productos.index')
            ->with('success', "Producto «{$titulo}» eliminado.");
    }
}
