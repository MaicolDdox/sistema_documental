<?php

namespace App\Http\Controllers\Coinvestigador;

use App\Concerns\StreamsPublicStorageFiles;
use App\Enums\EstadoRevisionEnum;
use App\Http\Controllers\Controller;
use App\Models\InvestigationType;
use App\Models\MincienciasProduct;
use App\Models\MincienciasProductFile;
use App\Models\ProjectModality;
use App\Models\ResearchLine;
use App\Models\TechnologicalLine;
use App\Models\ThematicArea;
use App\Models\TrainingCenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Producto Minciencias 100% personal del co-investigador: no se vincula a
 * semillero, proyecto ni líder de proyecto. Cada acción valida que el
 * producto pertenezca al usuario autenticado (ver ensurePropietario).
 */
class MincienciasProductoController extends Controller
{
    use StreamsPublicStorageFiles;

    public function index(): View
    {
        $productos = MincienciasProduct::withCount('files')
            ->with(['researchLine', 'trainingCenter'])
            ->where('user_id', Auth::id())
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('co_investigador.productos.index', compact('productos'));
    }

    public function create(): View
    {
        return view('co_investigador.productos.create', $this->datosFormulario());
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'archivos.*' => 'nullable|file|max:10240',
        ]);

        $validated = $this->validarProducto($request);
        $validated['user_id'] = Auth::id();
        $validated['estado_revision'] = EstadoRevisionEnum::Pendiente;

        $producto = MincienciasProduct::create($validated);

        foreach ($request->file('archivos', []) as $archivo) {
            if (! $archivo) {
                continue;
            }

            $path = $archivo->store('minciencias-productos', 'public');

            MincienciasProductFile::create([
                'minciencias_product_id' => $producto->id,
                'archivo' => $path,
                'uploaded_by' => Auth::id(),
            ]);
        }

        return redirect()->route('co-investigador.productos.show', $producto)
            ->with('success', 'Producto Minciencias creado correctamente.');
    }

    public function show(MincienciasProduct $producto): View
    {
        $this->ensurePropietario($producto);

        $producto->load([
            'files.uploadedBy',
            'trainingCenter',
            'revisadoPor.person',
            'researchLine',
            'technologicalLine',
            'thematicArea',
            'projectModality',
            'investigationType',
        ]);

        return view('co_investigador.productos.show', compact('producto'));
    }

    public function edit(MincienciasProduct $producto): View
    {
        $this->ensurePropietario($producto);

        return view('co_investigador.productos.edit', array_merge(
            ['producto' => $producto],
            $this->datosFormulario()
        ));
    }

    public function update(Request $request, MincienciasProduct $producto): RedirectResponse
    {
        $this->ensurePropietario($producto);

        $validated = $this->validarProducto($request);
        $producto->update($validated);

        return redirect()->route('co-investigador.productos.show', $producto)
            ->with('success', 'Producto Minciencias actualizado correctamente.');
    }

    public function destroy(MincienciasProduct $producto): RedirectResponse
    {
        $this->ensurePropietario($producto);

        foreach ($producto->files as $archivo) {
            if ($archivo->archivo) {
                Storage::disk('public')->delete($archivo->archivo);
            }
        }

        // El cascade de FK se encarga de borrar los registros de
        // minciencias_product_files asociados.
        $producto->delete();

        return redirect()->route('co-investigador.productos.index')
            ->with('success', 'Producto Minciencias eliminado correctamente.');
    }

    public function storeArchivo(Request $request, MincienciasProduct $producto): RedirectResponse
    {
        $this->ensurePropietario($producto);

        $validated = $request->validate([
            'descripcion' => 'nullable|string',
            'archivo' => 'required|file|max:10240',
        ]);

        $path = $request->file('archivo')->store('minciencias-productos', 'public');

        MincienciasProductFile::create([
            'minciencias_product_id' => $producto->id,
            'descripcion' => $validated['descripcion'] ?? null,
            'archivo' => $path,
            'uploaded_by' => Auth::id(),
        ]);

        return redirect()->route('co-investigador.productos.show', $producto)
            ->with('success', 'Archivo subido correctamente.');
    }

    public function destroyArchivo(MincienciasProductFile $archivo): RedirectResponse
    {
        if ((int) $archivo->product->user_id !== Auth::id()) {
            abort(403, 'No puedes eliminar archivos de un producto que no te pertenece.');
        }

        if ($archivo->archivo) {
            Storage::disk('public')->delete($archivo->archivo);
        }
        $archivo->delete();

        return redirect()->route('co-investigador.productos.show', $archivo->minciencias_product_id)
            ->with('success', 'Archivo eliminado correctamente.');
    }

    public function descargarArchivo(MincienciasProductFile $archivo): StreamedResponse
    {
        $this->ensurePropietarioArchivo($archivo);

        return $this->descargarArchivoPublico($archivo->archivo, $archivo->descripcion);
    }

    private function ensurePropietarioArchivo(MincienciasProductFile $archivo): void
    {
        if ((int) $archivo->product->user_id !== Auth::id()) {
            abort(403, 'No puedes ver este archivo.');
        }
    }

    private function ensurePropietario(MincienciasProduct $producto): void
    {
        if ((int) $producto->user_id !== Auth::id()) {
            abort(403, 'Este producto Minciencias no te pertenece.');
        }
    }

    private function validarProducto(Request $request): array
    {
        return $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'training_center_id' => ['required', 'exists:training_centers,id'],
            'research_line_id' => ['required', 'exists:research_lines,id'],
            'technological_line_id' => ['nullable', 'exists:technological_lines,id'],
            'thematic_area_id' => ['nullable', 'exists:thematic_areas,id'],
            'project_modality_id' => ['nullable', 'exists:project_modalities,id'],
            'investigation_type_id' => ['nullable', 'exists:investigation_types,id'],
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
        ]);
    }

    private function datosFormulario(): array
    {
        return [
            'trainingCenters' => TrainingCenter::activos()->orderBy('nombre')->get(),
            'lineasInvestigacion' => ResearchLine::orderBy('nombre')->get(),
            'lineasTecnologicas' => TechnologicalLine::orderBy('nombre')->get(),
            'areasTematicas' => ThematicArea::orderBy('nombre')->get(),
            'modalidades' => ProjectModality::orderBy('nombre')->get(),
            'tiposInvestigacion' => InvestigationType::orderBy('nombre')->get(),
        ];
    }
}
