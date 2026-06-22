<?php

namespace App\Http\Controllers\InvestigadorAsociado;

use App\Enums\EstadoRevisionEnum;
use App\Http\Controllers\Controller;
use App\Models\GroupProduct;
use App\Models\Project;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ReporteController extends Controller
{
    use InvestigadorContext;

    /**
     * Panel principal de reportes.
     */
    public function index(): View
    {
        $userId = auth()->id();

        $metricas = [
            'total_proyectos'      => Project::where('project_creator_id', $userId)->count(),
            'total_productos'      => GroupProduct::where('author_id', $userId)->count(),
            'productos_aprobados'  => GroupProduct::where('author_id', $userId)->where('estado_revision', EstadoRevisionEnum::Aprobado)->count(),
            'productos_rechazados' => GroupProduct::where('author_id', $userId)->where('estado_revision', EstadoRevisionEnum::Rechazado)->count(),
            'productos_pendientes' => GroupProduct::where('author_id', $userId)->where('estado_revision', EstadoRevisionEnum::Pendiente)->count(),
            'productos_revision'   => GroupProduct::where('author_id', $userId)->where('estado_revision', EstadoRevisionEnum::EnRevision)->count(),
        ];

        $porAnio = GroupProduct::where('author_id', $userId)
            ->whereNotNull('anio_publicacion')
            ->selectRaw('anio_publicacion as anio, count(*) as total')
            ->groupBy('anio_publicacion')
            ->orderBy('anio_publicacion', 'desc')
            ->get();

        $productos = GroupProduct::with(['product.project', 'mincienciasTypology', 'knowledgeArea'])
            ->where('author_id', $userId)
            ->orderBy('anio_publicacion', 'desc')
            ->get();

        return view('investigador.reportes.index', compact('metricas', 'porAnio', 'productos'));
    }

    /**
     * Vista de productos por estado.
     */
    public function productosPorEstado(): View
    {
        $userId   = auth()->id();
        $productos = GroupProduct::with(['product.project'])
            ->where('author_id', $userId)
            ->get();

        $data = [
            'pendiente'   => $productos->where('estado_revision', EstadoRevisionEnum::Pendiente)->count(),
            'en_revision' => $productos->where('estado_revision', EstadoRevisionEnum::EnRevision)->count(),
            'aprobado'    => $productos->where('estado_revision', EstadoRevisionEnum::Aprobado)->count(),
            'rechazado'   => $productos->where('estado_revision', EstadoRevisionEnum::Rechazado)->count(),
        ];

        return view('investigador.reportes.productos_estado', compact('data', 'productos'));
    }

    /**
     * Descarga CSV de productos.
     * ?tipo=aprobados|todos
     * ?periodo=semanal|mensual|anual  (opcional)
     */
    public function exportarCsv(Request $request): Response
    {
        $request->validate([
            'tipo'    => ['nullable', 'in:aprobados,todos,internos,semilleros'],
            'periodo' => ['nullable', 'in:semanal,mensual,anual,personalizado'],
            'desde'   => ['nullable', 'date'],
            'hasta'   => ['nullable', 'date'],
        ]);

        $userId  = auth()->id();
        $tipo    = $request->input('tipo', 'aprobados');
        $periodo = $request->input('periodo') ?? '';

        [$desde, $hasta] = $this->rangoFechas($periodo, $request);
        $etiqueta        = $this->etiquetaPeriodo($periodo, $request);

        $query = GroupProduct::with(['product.project', 'mincienciasTypology', 'knowledgeArea'])
            ->where('author_id', $userId)
            ->orderBy('anio_publicacion', 'desc')
            ->when($tipo === 'aprobados', fn($q) => $q->where('estado_revision', EstadoRevisionEnum::Aprobado))
            ->when($tipo === 'internos', fn($q) => $q->where('tipo_proyecto_origen', '!=', 'SEMILLEROS'))
            ->when($tipo === 'semilleros', fn($q) => $q->where('tipo_proyecto_origen', 'SEMILLEROS'))
            ->when($desde, fn($q) => $q->whereBetween('created_at', [$desde, $hasta]));

        $productos = $query->get();

        // Cabeceras CSV
        $rows = [
            ['Título del Producto', 'Proyecto', 'Año Publicación', 'Estado', 'Tipología Minciencias', 'Área del Conocimiento', 'Periodo'],
        ];

        foreach ($productos as $p) {
            $rows[] = [
                $p->titulo,
                $p->product?->project?->nombre ?? 'N/A',
                $p->anio_publicacion ?? '',
                $p->estado_revision?->value ?? $p->estado_revision ?? '',
                $p->mincienciasTypology?->nombre ?? 'N/A',
                $p->knowledgeArea?->nombre ?? 'N/A',
                $etiqueta,
            ];
        }

        // Generar CSV con BOM (UTF-8) para Excel
        $bom = "\xEF\xBB\xBF";
        $buffer = fopen('php://memory', 'r+');
        fwrite($buffer, $bom);
        foreach ($rows as $row) {
            fputcsv($buffer, $row, ';');
        }
        rewind($buffer);
        $csv = stream_get_contents($buffer);
        fclose($buffer);

        $sufix    = $periodo ? "_{$periodo}" : '';
        $filename = "mis_productos_{$tipo}{$sufix}_" . now()->format('Ymd_His') . '.csv';

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'no-cache, no-store',
        ]);
    }

    /**
     * Descarga PDF real del reporte del investigador.
     */
    public function exportarPdf(Request $request)
    {
        $request->validate([
            'tipo'    => ['nullable', 'in:aprobados,todos,internos,semilleros'],
            'periodo' => ['nullable', 'in:semanal,mensual,anual,personalizado'],
            'desde'   => ['nullable', 'date'],
            'hasta'   => ['nullable', 'date'],
        ]);

        $userId  = auth()->id();
        $tipo    = $request->input('tipo', 'todos');
        $periodo = $request->input('periodo') ?? '';

        [$desde, $hasta] = $this->rangoFechas($periodo, $request);
        $etiqueta        = $this->etiquetaPeriodo($periodo, $request);

        $metricas = [
            'total_proyectos'      => Project::where('project_creator_id', $userId)->count(),
            'total_productos'      => GroupProduct::where('author_id', $userId)->count(),
            'productos_aprobados'  => GroupProduct::where('author_id', $userId)->where('estado_revision', EstadoRevisionEnum::Aprobado)->count(),
            'productos_rechazados' => GroupProduct::where('author_id', $userId)->where('estado_revision', EstadoRevisionEnum::Rechazado)->count(),
            'productos_pendientes' => GroupProduct::where('author_id', $userId)->where('estado_revision', EstadoRevisionEnum::Pendiente)->count(),
            'productos_revision'   => GroupProduct::where('author_id', $userId)->where('estado_revision', EstadoRevisionEnum::EnRevision)->count(),
        ];

        $productos = GroupProduct::with(['product.project', 'mincienciasTypology', 'knowledgeArea'])
            ->where('author_id', $userId)
            ->orderBy('anio_publicacion', 'desc')
            ->when($tipo === 'aprobados', fn($q) => $q->where('estado_revision', EstadoRevisionEnum::Aprobado))
            ->when($tipo === 'internos', fn($q) => $q->where('tipo_proyecto_origen', '!=', 'SEMILLEROS'))
            ->when($tipo === 'semilleros', fn($q) => $q->where('tipo_proyecto_origen', 'SEMILLEROS'))
            ->when($desde, fn($q) => $q->whereBetween('created_at', [$desde, $hasta]))
            ->get();

        $pdf = Pdf::loadView('investigador.reportes.pdf', compact('metricas', 'productos', 'tipo', 'etiqueta'))
            ->setPaper('A4', 'portrait');

        $filename = 'mis_productos_' . $tipo . '_' . now()->format('Ymd_His') . '.pdf';
        return $pdf->download($filename);
    }

    // ──────────────────────────────────────────────────────────────────────────

    private function rangoFechas(?string $periodo, Request $request = null): array
    {
        if ($periodo === 'personalizado' && $request && $request->filled(['desde', 'hasta'])) {
            return [
                Carbon::parse($request->desde)->startOfDay(),
                Carbon::parse($request->hasta)->endOfDay()
            ];
        }

        return match ($periodo ?? '') {
            'semanal' => [Carbon::now()->startOfWeek(),  Carbon::now()->endOfWeek()],
            'mensual' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'anual'   => [Carbon::now()->startOfYear(),  Carbon::now()->endOfYear()],
            default   => [null, null],
        };
    }

    private function etiquetaPeriodo(?string $periodo, Request $request = null): string
    {
        if ($periodo === 'personalizado' && $request && $request->filled(['desde', 'hasta'])) {
            return 'Del ' . Carbon::parse($request->desde)->format('d/m/Y') . ' al ' . Carbon::parse($request->hasta)->format('d/m/Y');
        }

        return match ($periodo ?? '') {
            'semanal' => 'Semana ' . now()->weekOfYear . ' (' . now()->startOfWeek()->format('d/m') . ' - ' . now()->endOfWeek()->format('d/m/Y') . ')',
            'mensual' => ucfirst(now()->translatedFormat('F Y')),
            'anual'   => (string) now()->year,
            default   => 'Todos los registros',
        };
    }
}
