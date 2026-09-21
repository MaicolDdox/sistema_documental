<?php

namespace App\Http\Controllers\CoinvestigadorGdi;

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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Producto Minciencias de co_investigador_gdi — reforma GDI/SDI. A diferencia
 * del co_investigador original, training_center_id y grupo_investigacion_id
 * se heredan del usuario autenticado (ambos ya son fijos en su cuenta), no
 * se eligen en el formulario.
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

        return view('co_investigador_gdi.productos.index', compact('productos'));
    }

    public function create(): View
    {
        return view('co_investigador_gdi.productos.create', $this->datosFormulario());
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'archivos.*' => 'nullable|file|max:10240',
        ]);

        $validated = $this->validarProducto($request);
        $validated['user_id'] = Auth::id();
        $validated['training_center_id'] = Auth::user()->training_center_id;
        $validated['grupo_investigacion_id'] = Auth::user()->grupo_investigacion_id;
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

        return redirect()->route('co-investigador-gdi.productos.show', $producto)
            ->with('success', 'Producto Minciencias creado correctamente.');
    }

    public function show(MincienciasProduct $producto): View
    {
        $this->ensurePropietario($producto);

        $producto->load([
            'files.uploadedBy',
            'trainingCenter',
            'grupoInvestigacion',
            'revisadoPor.person',
            'researchLine',
            'technologicalLine',
            'thematicArea',
            'projectModality',
            'investigationType',
        ]);

        return view('co_investigador_gdi.productos.show', compact('producto'));
    }

    public function edit(MincienciasProduct $producto): View
    {
        $this->ensurePropietario($producto);

        return view('co_investigador_gdi.productos.edit', array_merge(
            ['producto' => $producto],
            $this->datosFormulario()
        ));
    }

    public function update(Request $request, MincienciasProduct $producto): RedirectResponse
    {
        $this->ensurePropietario($producto);

        $validated = $this->validarProducto($request);
        $producto->update($validated);

        return redirect()->route('co-investigador-gdi.productos.show', $producto)
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

        $producto->delete();

        return redirect()->route('co-investigador-gdi.productos.index')
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

        return redirect()->route('co-investigador-gdi.productos.show', $producto)
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

        return redirect()->route('co-investigador-gdi.productos.show', $archivo->minciencias_product_id)
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
        $centerId = Auth::user()->training_center_id;

        return $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'research_line_id' => ['required', Rule::exists('research_lines', 'id')->where('training_center_id', $centerId)],
            'technological_line_id' => ['nullable', Rule::exists('technological_lines', 'id')->where('training_center_id', $centerId)],
            'thematic_area_id' => ['nullable', Rule::exists('thematic_areas', 'id')->where('training_center_id', $centerId)],
            'project_modality_id' => ['nullable', Rule::exists('project_modalities', 'id')->where('training_center_id', $centerId)],
            'investigation_type_id' => ['nullable', Rule::exists('investigation_types', 'id')->where('training_center_id', $centerId)],
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
        ]);
    }

    private function datosFormulario(): array
    {
        $centerId = Auth::user()->training_center_id;

        return [
            'lineasInvestigacion' => ResearchLine::where('training_center_id', $centerId)->orderBy('nombre')->get(),
            'lineasTecnologicas' => TechnologicalLine::where('training_center_id', $centerId)->orderBy('nombre')->get(),
            'areasTematicas' => ThematicArea::where('training_center_id', $centerId)->orderBy('nombre')->get(),
            'modalidades' => ProjectModality::where('training_center_id', $centerId)->orderBy('nombre')->get(),
            'tiposInvestigacion' => InvestigationType::where('training_center_id', $centerId)->orderBy('nombre')->get(),
        ];
    }
}
