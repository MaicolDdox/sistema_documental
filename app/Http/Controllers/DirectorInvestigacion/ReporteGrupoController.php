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
            ->when($request->filled('anio'),            fn($q) => $q->where('anio_publicacion', $request->anio))
            ->when($request->filled('investigador_id'), fn($q) => $q->where('author_id', $request->investigador_id))
            ->orderBy('anio_publicacion', 'desc');

        // Lista de investigadores del grupo para el filtro
        $miembros = ResearchGroupUser::with('user.person')
            ->where('research_group_id', $grupoId)
            ->get()
            ->map(fn($p) => [
                'id'     => $p->user_id,
                'nombre' => $p->user?->person?->nombre_completo ?? $p->user?->email ?? 'Desconocido',
            ]);

        return view('director_investigacion.reportes.index', [
            'tipo'                  => $request->input('tipo', 'general'),
            'grupoId'               => $grupoId,
            'actividadGeneral'      => $this->service->actividadGeneral($grupoId),
            'aprobadosVsRechazados' => $this->service->aprobadosVsRechazados($grupoId, $filtros),
            'porInvestigador'       => $this->service->productosPorInvestigador($grupoId, $filtros),
            'porAnio'               => $this->service->productosPorAnio($grupoId, $filtros),
            'productos'             => $productosQuery->get(),
            'proyectos'             => $this->service->resumenProyectos($grupoId),
            'macroproyectos'        => $this->service->resumenMacroproyectos($grupoId),
            'investigadoresDetalle' => $this->service->investigadoresDetallados($grupoId),
            'miembros'              => $miembros,
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
            'tipo'            => ['required', 'in:general,por_investigador,por_anio,por_proyecto,por_macroproyecto,investigadores_detalle'],
            'periodo'         => ['nullable', 'in:semanal,mensual,anual,personalizado'],
            'desde'           => ['nullable', 'date'],
            'hasta'           => ['nullable', 'date'],
            'anio'            => ['nullable', 'integer', 'min:2000', 'max:2099'],
            'estado_revision' => ['nullable', 'string'],
            'investigador_id' => ['nullable', 'integer'],
        ]);

        $grupoId = $this->getGrupoId();
        $tipo    = $request->tipo;
        $periodo = $request->input('periodo') ?? '';

        [$desde, $hasta] = $this->rangoFechas($periodo, $request);

        $userIds = ResearchGroupUser::where('research_group_id', $grupoId)->pluck('user_id');
        $rows = [];

        if ($tipo === 'por_proyecto') {
            $proyectos = $this->service->resumenProyectos($grupoId, $desde, $hasta)['lista'];
            $rows[] = ['Nombre del Proyecto', 'Línea de Investigación', 'Macroproyecto', 'Creador', 'Estado', 'Fecha Inicio', 'Fecha Fin'];
            foreach ($proyectos as $p) {
                $rows[] = [
                    $p->nombre,
                    $p->linea_investigacion,
                    $p->macroproyect?->nombre ?? 'N/A',
                    $p->creator?->person?->nombre_completo ?? $p->creator?->email ?? 'N/A',
                    ucfirst($p->estado),
                    $p->fecha_inicio?->format('d/m/Y') ?? 'N/A',
                    $p->fecha_fin?->format('d/m/Y') ?? 'N/A',
                ];
            }
        } elseif ($tipo === 'por_macroproyecto') {
            $macros = $this->service->resumenMacroproyectos($grupoId, $desde, $hasta)['lista'];
            $rows[] = ['Código', 'Nombre del Macroproyecto', 'Línea de Programación', 'Proyectos Vinculados'];
            foreach ($macros as $m) {
                $rows[] = [
                    $m->codigo,
                    $m->nombre,
                    $m->linea_programacion ?? 'N/A',
                    $m->proyectos_count ?? 0,
                ];
            }
        } elseif ($tipo === 'investigadores_detalle') {
            $invs = $this->service->investigadoresDetallados($grupoId, $desde, $hasta);
            $rows[] = ['Investigador', 'Email', 'Total Productos', 'Aprobados', 'En Revisión', 'Pendientes', 'Rechazados', 'Estado', 'CvLAC'];
            foreach ($invs as $inv) {
                $rows[] = [
                    $inv['nombre'],
                    $inv['email'],
                    $inv['total'],
                    $inv['aprobados'],
                    $inv['en_revision'],
                    $inv['pendientes'],
                    $inv['rechazados'],
                    ucfirst($inv['estado']),
                    $inv['cvlac'] ?? 'No registrado',
                ];
            }
        }

        // --- MANEJO DE PRODUCTOS (general, por_investigador, por_anio) ---
        if (empty($rows)) {
            $baseQuery = GroupProduct::with(['author.person', 'product.project', 'mincienciasTypology'])
                ->whereIn('author_id', $userIds)
                ->when($desde, fn($q) => $q->whereBetween('created_at', [$desde, $hasta]))
                ->when($request->filled('estado_revision'), fn($q) => $q->where('estado_revision', $request->estado_revision))
                ->when($request->filled('anio'), fn($q) => $q->where('anio_publicacion', $request->anio))
                ->when($request->filled('investigador_id'), fn($q) => $q->where('author_id', $request->investigador_id));

            if ($tipo === 'por_investigador') {
                $rows[] = ['Investigador', 'Total Productos', 'Periodo'];
                $agrupado = $baseQuery->get()->groupBy('author_id');
                foreach ($agrupado as $productos) {
                    $nombre = $productos->first()->author?->person?->nombre_completo
                        ?? $productos->first()->author?->email
                        ?? 'Desconocido';
                    $rows[] = [$nombre, $productos->count(), $this->etiquetaPeriodo($periodo, $request)];
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
                        $this->etiquetaPeriodo($periodo, $request),
                    ];
                }
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
            'tipo'            => ['nullable', 'in:general,por_investigador,por_anio,por_proyecto,por_macroproyecto,investigadores_detalle'],
            'periodo'         => ['nullable', 'in:semanal,mensual,anual,personalizado'],
            'desde'           => ['nullable', 'date'],
            'hasta'           => ['nullable', 'date'],
            'anio'            => ['nullable', 'integer', 'min:2000', 'max:2099'],
            'estado_revision' => ['nullable', 'string'],
            'investigador_id' => ['nullable', 'integer'],
        ]);

        $grupoId  = $this->getGrupoId();
        $tipo     = $request->input('tipo', 'general');
        $periodo  = $request->input('periodo') ?? '';

        [$desde, $hasta] = $this->rangoFechas($periodo, $request);
        $etiqueta        = $this->etiquetaPeriodo($periodo, $request);
        $actividadGeneral = $this->service->actividadGeneral($grupoId);

        if (in_array($tipo, ['por_proyecto', 'por_macroproyecto', 'investigadores_detalle'])) {
            $data = match($tipo) {
                'por_proyecto' => $this->service->resumenProyectos($grupoId)['lista'] ?? collect(),
                'por_macroproyecto' => $this->service->resumenMacroproyectos($grupoId)['lista'] ?? collect(),
                'investigadores_detalle' => $this->service->investigadoresDetallados($grupoId),
            };

            $pdf = Pdf::loadView('director_investigacion.reportes.pdf_adicional', compact(
                'tipo', 'etiqueta', 'actividadGeneral', 'data'
            ))->setPaper('A4', 'landscape');

            $filename = 'reporte_grupo_' . $tipo . '_' . now()->format('Ymd_His') . '.pdf';
            return $pdf->download($filename);
        }

        $userIds = ResearchGroupUser::where('research_group_id', $grupoId)->pluck('user_id');
        $productos = GroupProduct::with(['author.person', 'product.project', 'mincienciasTypology'])
            ->whereIn('author_id', $userIds)
            ->when($desde, fn($q) => $q->whereBetween('created_at', [$desde, $hasta]))
            ->when($request->filled('estado_revision'), fn($q) => $q->where('estado_revision', $request->estado_revision))
            ->when($request->filled('anio'), fn($q) => $q->where('anio_publicacion', $request->anio))
            ->when($request->filled('investigador_id'), fn($q) => $q->where('author_id', $request->investigador_id))
            ->orderBy('anio_publicacion', 'desc')
            ->get();

        $pdf = Pdf::loadView('director_investigacion.reportes.pdf', compact(
            'tipo', 'etiqueta', 'actividadGeneral', 'productos'
        ))->setPaper('A4', 'landscape');

        $filename = 'reporte_grupo_' . $tipo . '_' . now()->format('Ymd_His') . '.pdf';
        return $pdf->download($filename);
    }

    // ──────────────────────────────────────────────────────────────────────────

    private function rangoFechas(?string $periodo, Request $request = null): array
    {
        if ($periodo === 'personalizado' && $request) {
            $desde = $request->filled('desde') ? Carbon::parse($request->desde)->startOfDay() : null;
            $hasta = $request->filled('hasta') ? Carbon::parse($request->hasta)->endOfDay() : Carbon::now()->endOfDay();
            return [$desde, $hasta];
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
        if ($periodo === 'personalizado' && $request) {
            $desde = $request->filled('desde') ? Carbon::parse($request->desde)->format('d/m/Y') : 'Inicio';
            $hasta = $request->filled('hasta') ? Carbon::parse($request->hasta)->format('d/m/Y') : 'Hoy';
            return "Personalizado ($desde - $hasta)";
        }

        return match ($periodo ?? '') {
            'semanal' => 'Semana ' . now()->weekOfYear . ' (' . now()->startOfWeek()->format('d/m') . ' - ' . now()->endOfWeek()->format('d/m/Y') . ')',
            'mensual' => ucfirst(now()->translatedFormat('F Y')),
            'anual'   => (string) now()->year,
            default   => 'Todos los registros',
        };
    }
}
