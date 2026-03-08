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
use App\Models\Project;
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
        $tipologias = MincienciasTypology::orderBy('nombre')->get();

        if ($semillero) {
            $projectIds = DB::table('project_seedlings')
                ->where('seedling_id', $semillero->id)
                ->pluck('project_id');
            $productIds = DB::table('products')->whereIn('project_id', $projectIds)->pluck('id');

            $productos = GroupProduct::with(['author.person', 'product.project', 'mincienciasTypology'])
                ->whereIn('product_id', $productIds)
                ->orderBy('updated_at', 'desc')
                ->get()
                ->map(function ($gp) {
                    $gp->es_mio = $gp->author_id === Auth::id();
                    return $gp;
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
            'tipologias' => $tipologias,
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
            'minciencias_typology_id' => 'required|exists:minciencias_typologies,id',
            'project_id' => 'required|exists:projects,id',
            'tiene_repositorio' => 'required|boolean',
        ];
        if ($tieneRepositorio) {
            $rules['url_repositorio'] = 'required|url|max:500';
        } else {
            $rules['evidencia'] = 'required|file|max:10240'; // 10MB
        }

        $validated = $request->validate($rules, [
            'titulo.required' => 'El título del producto es obligatorio.',
            'minciencias_typology_id.required' => 'El tipo de producto es obligatorio.',
            'project_id.required' => 'El proyecto origen es obligatorio.',
            'url_repositorio.required' => 'La URL del repositorio es obligatoria cuando tiene repositorio en línea.',
            'evidencia.required' => 'El archivo del producto es obligatorio cuando no tiene repositorio en línea.',
        ]);

        $typology = MincienciasTypology::find($validated['minciencias_typology_id']);
        $subcategory = MincienciasSubcategory::where('minciencias_typology_id', $typology->id)->first();
        $knowledgeGrand = KnowledgeGrandArea::first();
        $knowledgeArea = KnowledgeArea::first();
        if (!$subcategory || !$knowledgeGrand || !$knowledgeArea) {
            return redirect()->route('lider-sem.productos')
                ->with('error', 'Faltan catálogos de configuración. Contacta al administrador.');
        }

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
            'estado' => EstadoEnum::Activo,
        ]);

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
     * Aprueba un producto. Solo si pertenece al semillero del líder y no es autor del producto.
     * Observaciones opcionales.
     */
    public function aprobar(Request $request, GroupProduct $groupProduct): RedirectResponse
    {
        $semillero = Auth::user()->ledSeedlings()->first();
        if (!$semillero || !$this->perteneceAlSemillero($groupProduct, $semillero->id)) {
            abort(403, 'No puedes aprobar este producto.');
        }
        if ($groupProduct->author_id === Auth::id()) {
            return redirect()->route('lider-sem.productos')
                ->with('error', 'No puedes aprobar tus propios productos.');
        }

        $groupProduct->estado_revision = EstadoRevisionEnum::Aprobado;
        $groupProduct->observaciones_revision = $request->input('observaciones');
        $groupProduct->save();

        return redirect()->route('lider-sem.productos')
            ->with('success', 'Producto aprobado correctamente.');
    }

    /**
     * Rechaza un producto. Solo si pertenece al semillero del líder y no es autor del producto.
     * Observaciones obligatorias.
     */
    public function rechazar(Request $request, GroupProduct $groupProduct): RedirectResponse
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'observaciones' => 'required|string|max:2000',
        ], [
            'observaciones.required' => 'Las observaciones son obligatorias al rechazar.',
        ]);
        if ($validator->fails()) {
            return redirect()->route('lider-sem.productos')
                ->withErrors($validator)
                ->with('rechazar_id', $groupProduct->id)
                ->withInput();
        }
        $validated = $validator->validated();

        $semillero = Auth::user()->ledSeedlings()->first();
        if (!$semillero || !$this->perteneceAlSemillero($groupProduct, $semillero->id)) {
            abort(403, 'No puedes rechazar este producto.');
        }
        if ($groupProduct->author_id === Auth::id()) {
            return redirect()->route('lider-sem.productos')
                ->with('error', 'No puedes rechazar tus propios productos.');
        }

        $groupProduct->estado_revision = EstadoRevisionEnum::Rechazado;
        $groupProduct->observaciones_revision = $validated['observaciones'];
        $groupProduct->save();

        return redirect()->route('lider-sem.productos')
            ->with('success', 'Producto rechazado.');
    }

    private function perteneceAlSemillero(GroupProduct $groupProduct, int $seedlingId): bool
    {
        $projectId = $groupProduct->product?->project_id;
        if (!$projectId) {
            return false;
        }
        return DB::table('project_seedlings')
            ->where('project_id', $projectId)
            ->where('seedling_id', $seedlingId)
            ->exists();
    }
}
