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
use App\Models\ProjectAuthor;
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

        if ($semillero) {
            $projectIds = DB::table('project_seedlings')
                ->where('seedling_id', $semillero->id)
                ->pluck('project_id');

            $productos = Product::with(['project', 'productAuthors.projectAuthor.user.person'])
                ->whereIn('project_id', $projectIds)
                ->orderBy('updated_at', 'desc')
                ->get()
                ->map(function ($prod) {
                    $autorPrincipal = $prod->productAuthors->first()?->projectAuthor?->user;
                    $prod->es_mio = $autorPrincipal && $autorPrincipal->id === Auth::id();
                    $prod->autor = $autorPrincipal;
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

        $projectAuthorUser = ProjectAuthor::where('project_id', $validated['project_id'])
            ->where('user_id', Auth::id())
            ->first();

        if ($projectAuthorUser) {
            ProductAuthor::create([
                'product_id' => $product->id,
                'project_author_id' => $projectAuthorUser->id,
            ]);
        }

        return redirect()->route('lider-sem.productos')
            ->with('success', 'Producto registrado correctamente. Estado de revisión: pendiente.');
    }

    /**
     * Aprueba un producto. Solo si pertenece al semillero del líder y no es autor del producto.
     * Observaciones opcionales.
     */
    public function aprobar(Request $request, Product $producto): RedirectResponse
    {
        $semillero = Auth::user()->ledSeedlings()->first();
        if (!$semillero || !$this->perteneceAlSemillero($producto, $semillero->id)) {
            abort(403, 'No puedes aprobar este producto.');
        }

        $autorPrincipal = $producto->productAuthors->first()?->projectAuthor?->user;
        if ($autorPrincipal && $autorPrincipal->id === Auth::id()) {
            return redirect()->route('lider-sem.productos')
                ->with('error', 'No puedes aprobar tus propios productos.');
        }

        $producto->estado_revision = EstadoRevisionEnum::Aprobado;
        $producto->observacion_revision = $request->input('observaciones');
        $producto->save();

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
        if (!$semillero || !$this->perteneceAlSemillero($producto, $semillero->id)) {
            abort(403, 'No puedes rechazar este producto.');
        }

        $autorPrincipal = $producto->productAuthors->first()?->projectAuthor?->user;
        if ($autorPrincipal && $autorPrincipal->id === Auth::id()) {
            return redirect()->route('lider-sem.productos')
                ->with('error', 'No puedes rechazar tus propios productos.');
        }

        $producto->estado_revision = EstadoRevisionEnum::Rechazado;
        $producto->observacion_revision = $validated['observaciones'];
        $producto->save();

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
