<?php

namespace App\Http\Controllers\InvestigadorAsociado;

use App\Http\Controllers\Controller;
use App\Models\GroupProduct;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReporteController extends Controller
{
    use InvestigadorContext;

    /**
     * Panel principal de reportes.
     */
    public function index(): View
    {
        $userId = Auth::id();

        $metricas = [
            'total_proyectos'     => Project::where('project_creator_id', $userId)->count(),
            'total_productos'     => GroupProduct::where('author_id', $userId)->count(),
            'productos_aprobados' => GroupProduct::where('author_id', $userId)->where('estado_revision', 'aprobado')->count(),
            'productos_rechazados'=> GroupProduct::where('author_id', $userId)->where('estado_revision', 'rechazado')->count(),
        ];

        return view('investigador.reportes.index', compact('metricas'));
    }

    /**
     * Vista de productos por estado.
     */
    public function productosPorEstado(): View
    {
        $userId   = Auth::id();
        $productos = GroupProduct::with(['product.project'])
            ->where('author_id', $userId)
            ->get();

        $data = [
            'pendiente'   => $productos->where('estado_revision', 'pendiente')->count(),
            'en_revision' => $productos->where('estado_revision', 'en_revision')->count(),
            'aprobado'    => $productos->where('estado_revision', 'aprobado')->count(),
            'rechazado'   => $productos->where('estado_revision', 'rechazado')->count(),
        ];

        return view('investigador.reportes.productos_estado', compact('data', 'productos'));
    }

    /**
     * Descarga CSV de productos.
     * ?tipo=aprobados|todos
     * ?periodo=semanal|mensual|anual  (opcional)
     */
    public function exportar(Request $request): Response
    {
        $request->validate([
            'tipo'    => ['nullable', 'in:aprobados,todos'],
            'periodo' => ['nullable', 'in:semanal,mensual,anual'],
        ]);

        $userId  = Auth::id();
        $tipo    = $request->input('tipo', 'aprobados');
        $periodo = $request->input('periodo', '');

        [$desde, $hasta] = $this->rangoFechas($periodo);

        $query = GroupProduct::with(['product.project', 'mincienciasTypology', 'knowledgeArea'])
            ->where('author_id', $userId)
            ->orderBy('anio_publicacion', 'desc')
            ->when($tipo === 'aprobados', fn($q) => $q->where('estado_revision', 'aprobado'))
            ->when($desde, fn($q) => $q->whereBetween('created_at', [$desde, $hasta]));

        $productos = $query->get();

        $etiqueta = $this->etiquetaPeriodo($periodo);

        $rows = [['Título del Producto', 'Proyecto', 'Año Publicación', 'Estado', 'Tipología Minciencias', 'Área del Conocimiento', 'Periodo']];

        foreach ($productos as $p) {
            $rows[] = [
                $p->titulo,
                $p->product?->project?->nombre ?? 'N/A',
                $p->anio_publicacion ?? '',
                $p->estado_revision?->value ?? '',
                $p->mincienciasTypology?->nombre ?? 'N/A',
                $p->knowledgeArea?->nombre ?? 'N/A',
                $etiqueta,
            ];
        }

        // CSV con BOM para Excel
        $output = fopen('php://temp', 'r+');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        foreach ($rows as $row) {
            fputcsv($output, $row, ';');
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        $sufijoPeriodo = $periodo ? "_{$periodo}" : '';
        $filename = "mis_productos_{$tipo}{$sufijoPeriodo}_" . now()->format('Ymd_His') . '.csv';

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
        ]);
    }

    private function rangoFechas(string $periodo): array
    {
        return match ($periodo) {
            'semanal' => [Carbon::now()->startOfWeek(),  Carbon::now()->endOfWeek()],
            'mensual' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'anual'   => [Carbon::now()->startOfYear(),  Carbon::now()->endOfYear()],
            default   => [null, null],
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
