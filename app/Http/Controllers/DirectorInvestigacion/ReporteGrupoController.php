<?php

namespace App\Http\Controllers\DirectorInvestigacion;

use App\Http\Controllers\Controller;
use App\Models\GroupProduct;
use App\Models\ResearchGroupUser;
use App\Services\Director\ReporteService;
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

        return view('director_investigacion.reportes.index', [
            'grupoId'               => $grupoId,
            'actividadGeneral'      => $this->service->actividadGeneral($grupoId),
            'aprobadosVsRechazados' => $this->service->aprobadosVsRechazados($grupoId, $filtros),
            'porInvestigador'       => $this->service->productosPorInvestigador($grupoId, $filtros),
            'porAnio'               => $this->service->productosPorAnio($grupoId, $filtros),
        ]);
    }

    /**
     * Descarga reporte como CSV.
     * tipo:    general | por_investigador | por_anio
     * periodo: semanal | mensual | anual | (vacío = todos)
     */
    public function exportar(Request $request): Response
    {
        $request->validate([
            'tipo'    => ['required', 'in:general,por_investigador,por_anio'],
            'periodo' => ['nullable', 'in:semanal,mensual,anual'],
        ]);

        $grupoId = $this->getGrupoId();
        $tipo    = $request->tipo;
        $periodo = $request->input('periodo', '');

        // Construir rango de fechas según periodo
        [$desde, $hasta] = $this->rangoFechas($periodo);

        // Filtros para el service (año)
        $filtros = $request->only(['estado_revision', 'anio']);

        // Obtener IDs de investigadores del grupo
        $userIds = ResearchGroupUser::where('research_group_id', $grupoId)->pluck('user_id');

        // Query base con filtro de periodo
        $baseQuery = GroupProduct::with(['author.person', 'product.project', 'mincienciasTypology'])
            ->whereIn('author_id', $userIds)
            ->when($desde, fn($q) => $q->whereBetween('created_at', [$desde, $hasta]))
            ->when($request->filled('estado_revision'), fn($q) => $q->where('estado_revision', $request->estado_revision));

        $rows = [];

        if ($tipo === 'por_investigador') {
            $rows[] = ['Investigador', 'Total Productos', 'Periodo'];
            $agrupado = $baseQuery->get()->groupBy('author_id');
            foreach ($agrupado as $authorId => $productos) {
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
            // Reporte general detallado
            $rows[] = ['Investigador', 'Título del Producto', 'Año Publicación', 'Estado Revisión', 'Tipología', 'Periodo'];
            foreach ($baseQuery->get() as $p) {
                $rows[] = [
                    $p->author?->person?->nombre_completo ?? $p->author?->email ?? 'Desconocido',
                    $p->titulo,
                    $p->anio_publicacion ?? '',
                    $p->estado_revision?->value ?? '',
                    $p->mincienciasTypology?->nombre ?? 'N/A',
                    $this->etiquetaPeriodo($periodo),
                ];
            }
        }

        // Generar CSV con BOM para Excel
        $output = fopen('php://temp', 'r+');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        foreach ($rows as $row) {
            fputcsv($output, $row, ';');
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        $sufijoPeriodo = $periodo ? "_{$periodo}" : '';
        $filename = "reporte_grupo_{$tipo}{$sufijoPeriodo}_" . now()->format('Ymd_His') . '.csv';

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
        ]);
    }

    /**
     * Retorna [Carbon $desde, Carbon $hasta] según el periodo.
     * Si no hay periodo, retorna [null, null].
     */
    private function rangoFechas(string $periodo): array
    {
        return match ($periodo) {
            'semanal'  => [Carbon::now()->startOfWeek(),  Carbon::now()->endOfWeek()],
            'mensual'  => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'anual'    => [Carbon::now()->startOfYear(),  Carbon::now()->endOfYear()],
            default    => [null, null],
        };
    }

    private function etiquetaPeriodo(string $periodo): string
    {
        return match ($periodo) {
            'semanal' => 'Semana ' . now()->weekOfYear . ' (' . now()->startOfWeek()->format('d/m') . ' - ' . now()->endOfWeek()->format('d/m/Y') . ')',
            'mensual' => ucfirst(now()->translatedFormat('F Y')),
            'anual'   => (string) now()->year,
            default   => 'Todos los registros',
        };
    }
}
