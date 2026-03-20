<?php

namespace App\Http\Controllers\DirectorInvestigacion;

use App\Http\Controllers\Controller;
use App\Models\GroupProduct;
use App\Models\ResearchGroupUser;
use App\Services\Director\ReporteService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ReporteGrupoController extends Controller
{
    use DirectorContext;

    public function __construct(private ReporteService $service) {}

    /**
     * Vista principal de reportes con filtros.
     */
    public function index(Request $request): View
    {
        $grupoId = $this->getGrupoId();
        $filtros = $request->only(['estado_revision', 'anio', 'investigador_id']);

        $userIds = ResearchGroupUser::where('research_group_id', $grupoId)->pluck('user_id');

        $productosQuery = GroupProduct::with(['author.person', 'product.project', 'mincienciasTypology'])
            ->whereIn('author_id', $userIds)
            ->when($request->filled('estado_revision'), fn($q) => $q->where('estado_revision', $request->estado_revision))
            ->when($request->filled('anio'), fn($q) => $q->where('anio_publicacion', $request->anio))
            ->orderBy('anio_publicacion', 'desc');

        return view('director_investigacion.reportes.index', [
            'grupoId'               => $grupoId,
            'actividadGeneral'      => $this->service->actividadGeneral($grupoId),
            'aprobadosVsRechazados' => $this->service->aprobadosVsRechazados($grupoId, $filtros),
            'porInvestigador'       => $this->service->productosPorInvestigador($grupoId, $filtros),
            'porAnio'               => $this->service->productosPorAnio($grupoId, $filtros),
            'productos'             => $productosQuery->get(),
        ]);
    }

    /**
     * Descarga reporte como CSV.
     * ?tipo=general|por_investigador|por_anio
     * ?periodo=semanal|mensual|anual  (opcional)
     */
    public function exportarCsv(Request $request): Response
    {
        $request->validate([
            'tipo'    => ['required', 'in:general,por_investigador,por_anio'],
            'periodo' => ['nullable', 'in:semanal,mensual,anual'],
            'anio'    => ['nullable', 'integer', 'min:2000', 'max:2099'],
        ]);

        $grupoId = $this->getGrupoId();
        $tipo    = $request->tipo;
        $periodo = $request->input('periodo') ?? '';

        [$desde, $hasta] = $this->rangoFechas($periodo);

        $userIds = ResearchGroupUser::where('research_group_id', $grupoId)->pluck('user_id');

        $baseQuery = GroupProduct::with(['author.person', 'product.project', 'mincienciasTypology'])
            ->whereIn('author_id', $userIds)
            ->when($desde, fn($q) => $q->whereBetween('created_at', [$desde, $hasta]))
            ->when($request->filled('estado_revision'), fn($q) => $q->where('estado_revision', $request->estado_revision))
            ->when($request->filled('anio'), fn($q) => $q->where('anio_publicacion', $request->anio));

        $rows = [];

        if ($tipo === 'por_investigador') {
            $rows[] = ['Investigador', 'Total Productos', 'Periodo'];
            $agrupado = $baseQuery->get()->groupBy('author_id');
            foreach ($agrupado as $productos) {
                $nombre = $productos->first()->author?->person?->nombre_completo
                    ?? $productos->first()->author?->email
                    ?? 'Desconocido';
                $rows[] = [$nombre, $productos->count(), $this->etiquetaPeriodo($periodo)];
            }

        } elseif ($tipo === 'por_anio') {
            $rows[] = ['Año de Publicación', 'Total Productos'];
            $agrupado = $baseQuery->get()->groupBy('anio_publicacion');
            foreach ($agrupado->sortKeysDesc() as $anio => $productos) {
                $rows[] = [$anio ?: 'Sin año', $productos->count()];
            }

        } else {
            $rows[] = ['Investigador', 'Título del Producto', 'Año Publicación', 'Estado Revisión', 'Tipología', 'Periodo'];
            foreach ($baseQuery->get() as $p) {
                $rows[] = [
                    $p->author?->person?->nombre_completo ?? $p->author?->email ?? 'Desconocido',
                    $p->titulo,
                    $p->anio_publicacion ?? '',
                    $p->estado_revision?->value ?? $p->estado_revision ?? '',
                    $p->mincienciasTypology?->nombre ?? 'N/A',
                    $this->etiquetaPeriodo($periodo),
                ];
            }
        }

        // Generar CSV con BOM UTF-8 para Excel
        $bom    = "\xEF\xBB\xBF";
        $buffer = fopen('php://memory', 'r+');
        fwrite($buffer, $bom);
        foreach ($rows as $row) {
            fputcsv($buffer, $row, ';');
        }
        rewind($buffer);
        $csv = stream_get_contents($buffer);
        fclose($buffer);

        $sufijoPeriodo = $periodo ? "_{$periodo}" : '';
        $filename = "reporte_grupo_{$tipo}{$sufijoPeriodo}_" . now()->format('Ymd_His') . '.csv';

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'no-cache, no-store',
        ]);
    }

    /**
     * Descarga PDF real del reporte del grupo.
     */
    public function exportarPdf(Request $request)
    {
        $request->validate([
            'tipo'    => ['nullable', 'in:general,por_investigador,por_anio'],
            'periodo' => ['nullable', 'in:semanal,mensual,anual'],
            'anio'    => ['nullable', 'integer', 'min:2000', 'max:2099'],
        ]);

        $grupoId  = $this->getGrupoId();
        $tipo     = $request->input('tipo', 'general');
        $periodo  = $request->input('periodo') ?? '';

        [$desde, $hasta] = $this->rangoFechas($periodo);
        $etiqueta        = $this->etiquetaPeriodo($periodo);

        $actividadGeneral = $this->service->actividadGeneral($grupoId);

        $userIds = ResearchGroupUser::where('research_group_id', $grupoId)->pluck('user_id');

        $productos = GroupProduct::with(['author.person', 'product.project', 'mincienciasTypology'])
            ->whereIn('author_id', $userIds)
            ->when($desde, fn($q) => $q->whereBetween('created_at', [$desde, $hasta]))
            ->when($request->filled('anio'), fn($q) => $q->where('anio_publicacion', $request->anio))
            ->orderBy('anio_publicacion', 'desc')
            ->get();

        $pdf = Pdf::loadView('director_investigacion.reportes.pdf', compact(
            'tipo', 'etiqueta', 'actividadGeneral', 'productos'
        ))->setPaper('A4', 'landscape');

        $filename = 'reporte_grupo_' . $tipo . '_' . now()->format('Ymd_His') . '.pdf';
        return $pdf->download($filename);
    }

    // ──────────────────────────────────────────────────────────────────────────

    private function rangoFechas(?string $periodo): array
    {
        return match ($periodo ?? '') {
            'semanal' => [Carbon::now()->startOfWeek(),  Carbon::now()->endOfWeek()],
            'mensual' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'anual'   => [Carbon::now()->startOfYear(),  Carbon::now()->endOfYear()],
            default   => [null, null],
        };
    }

    private function etiquetaPeriodo(?string $periodo): string
    {
        return match ($periodo ?? '') {
            'semanal' => 'Semana ' . now()->weekOfYear . ' (' . now()->startOfWeek()->format('d/m') . ' - ' . now()->endOfWeek()->format('d/m/Y') . ')',
            'mensual' => ucfirst(now()->translatedFormat('F Y')),
            'anual'   => (string) now()->year,
            default   => 'Todos los registros',
        };
    }
}
