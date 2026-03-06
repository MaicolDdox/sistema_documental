<?php

namespace App\Http\Controllers\DirectorSemilleros;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Seedling;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteSemilleroController extends Controller
{
    /**
     * Semilleros del centro del usuario (director).
     */
    protected function semillerosQuery()
    {
        $centerId = Auth::user()->training_center_id;
        return Seedling::with(['leader.person', 'researchGroup'])
            ->whereHas('researchGroup', fn ($q) => $q->where('training_center_id', $centerId))
            ->orderBy('nombre');
    }

    public function index()
    {
        if (!Auth::user()->canAny([
            'reportes.semilleros_con_metricas',
            'reportes.aprendices_por_semillero',
            'reportes.proyectos_por_estado'
        ])) {
            abort(403, 'No tienes permisos para ver reportes.');
        }

        $semilleros = $this->semillerosQuery()->get(['id', 'nombre', 'codigo']);
        return view('director_semilleros.reportes.index', compact('semilleros'));
    }

    public function exportar(Request $request)
    {
        $this->authorize('reportes.exportar_pdf_excel');

        $validated = $request->validate([
            'tipo_reporte' => 'required|string|in:Semilleros con Métricas,Aprendices por Semillero,Proyectos por Estado',
            'formato'      => 'required|in:pdf,excel',
            'semillero_id' => ['nullable', 'integer', Rule::in($this->semillerosQuery()->pluck('id')->all())],
            'fecha_desde'  => 'nullable|date',
            'fecha_hasta'  => 'nullable|date|after_or_equal:fecha_desde',
        ]);

        $query = $this->semillerosQuery();
        if (!empty($validated['semillero_id'])) {
            $query->where('id', $validated['semillero_id']);
        }

        $fechaDesde = $validated['fecha_desde'] ?? null;
        $fechaHasta = $validated['fecha_hasta'] ?? null;

        $tipo = $validated['tipo_reporte'];
        $formato = $validated['formato'];

        if ($formato === 'pdf') {
            return $this->exportarPdf($tipo, $query->get(), $fechaDesde, $fechaHasta);
        }
        return $this->exportarExcel($tipo, $query->get(), $fechaDesde, $fechaHasta);
    }

    protected function exportarPdf(string $tipo, $semilleros, ?string $fechaDesde, ?string $fechaHasta)
    {
        $data = $this->buildDatosReporte($tipo, $semilleros, $fechaDesde, $fechaHasta);
        $vista = match ($tipo) {
            'Semilleros con Métricas'   => 'director_semilleros.reportes.pdf.semilleros_metricas',
            'Aprendices por Semillero'  => 'director_semilleros.reportes.pdf.aprendices_por_semillero',
            'Proyectos por Estado'      => 'director_semilleros.reportes.pdf.proyectos_por_estado',
            default => abort(400, 'Tipo de reporte no válido'),
        };

        $pdf = Pdf::loadView($vista, $data);
        $nombre = $this->nombreArchivo($tipo, 'pdf');
        return $pdf->download($nombre);
    }

    protected function exportarExcel(string $tipo, $semilleros, ?string $fechaDesde, ?string $fechaHasta)
    {
        $data = $this->buildDatosReporte($tipo, $semilleros, $fechaDesde, $fechaHasta);
        $nombre = $this->nombreArchivo($tipo, 'csv');

        return new StreamedResponse(function () use ($tipo, $data, $nombre) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM for Excel

            if ($tipo === 'Semilleros con Métricas') {
                fputcsv($out, ['Semillero', 'Código', 'Integrantes', 'Proyectos activos', 'Productos']);
                foreach ($data['filas'] as $f) {
                    fputcsv($out, [$f['nombre'], $f['codigo'], $f['integrantes'], $f['proyectos'], $f['productos']]);
                }
            } elseif ($tipo === 'Aprendices por Semillero') {
                fputcsv($out, ['Semillero', 'Documento', 'Nombre', 'Email']);
                foreach ($data['filas'] as $f) {
                    fputcsv($out, [$f['semillero'], $f['documento'], $f['nombre'], $f['email']]);
                }
            } else {
                fputcsv($out, ['Semillero', 'Proyecto', 'Estado', 'Fecha inicio', 'Fecha fin']);
                foreach ($data['filas'] as $f) {
                    fputcsv($out, [$f['semillero'], $f['proyecto'], $f['estado'], $f['fecha_inicio'], $f['fecha_fin']]);
                }
            }

            fclose($out);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition'  => 'attachment; filename="'.$nombre.'"',
        ]);
    }

    protected function buildDatosReporte(string $tipo, $semilleros, ?string $fechaDesde, ?string $fechaHasta): array
    {
        $titulo = $tipo;
        $filas = [];

        if ($tipo === 'Semilleros con Métricas') {
            foreach ($semilleros as $s) {
                $filas[] = [
                    'nombre'      => $s->nombre,
                    'codigo'      => $s->codigo ?? '—',
                    'integrantes' => $s->members()->count(),
                    'proyectos'   => $s->projects()->where('estado', 'activo')->count(),
                    'productos'   => $s->projects()->where('estado', 'activo')->withCount('products')->get()->sum('products_count'),
                ];
            }
            return ['titulo' => $titulo, 'filas' => $filas, 'semilleros' => $semilleros];
        }

        if ($tipo === 'Aprendices por Semillero') {
            foreach ($semilleros as $s) {
                foreach ($s->members()->with('person')->get() as $user) {
                    $filas[] = [
                        'semillero'  => $s->nombre,
                        'documento'  => $user->numero_documento ?? '',
                        'nombre'     => $user->person?->nombre_completo ?? $user->email,
                        'email'      => $user->email ?? '',
                    ];
                }
            }
            return ['titulo' => $titulo, 'filas' => $filas, 'semilleros' => $semilleros];
        }

        // Proyectos por Estado
        $proyectosQuery = Project::query()
            ->whereHas('seedlings', fn ($q) => $q->whereIn('seedlings.id', $semilleros->pluck('id')))
            ->with(['seedlings:id,nombre', 'projectCreator.person']);
        if ($fechaDesde) {
            $proyectosQuery->whereDate('fecha_inicio', '>=', $fechaDesde);
        }
        if ($fechaHasta) {
            $proyectosQuery->whereDate('fecha_fin', '<=', $fechaHasta);
        }
        $proyectos = $proyectosQuery->orderBy('estado')->orderBy('nombre')->get();
        foreach ($proyectos as $p) {
            $semilleroNombres = $p->seedlings->pluck('nombre')->implode(', ');
            $filas[] = [
                'semillero'     => $semilleroNombres,
                'proyecto'      => $p->nombre,
                'estado'        => $p->estado->value ?? $p->estado,
                'fecha_inicio'  => $p->fecha_inicio?->format('d/m/Y') ?? '—',
                'fecha_fin'     => $p->fecha_fin?->format('d/m/Y') ?? '—',
            ];
        }
        return ['titulo' => $titulo, 'filas' => $filas, 'semilleros' => $semilleros, 'proyectos' => $proyectos];
    }

    protected function nombreArchivo(string $tipo, string $ext): string
    {
        $slug = str_replace(' ', '_', $tipo);
        $fecha = now()->format('Y-m-d_H-i');
        return "reporte_{$slug}_{$fecha}.{$ext}";
    }
}
