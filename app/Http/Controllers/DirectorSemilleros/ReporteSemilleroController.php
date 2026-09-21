<?php

namespace App\Http\Controllers\DirectorSemilleros;

use App\Enums\EstadoEnum;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Seedling;
use App\Services\Reportes\ReporteExportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        return Seedling::with(['leader.person', 'members', 'advisors', 'projects.projectEvidences'])
            ->where('training_center_id', $centerId)
            ->orderBy('nombre');
    }

    public function exportar(Request $request)
    {
        $this->authorize('reportes.exportar_pdf_excel');

        $validated = $request->validate([
            'tipo_reporte' => 'required|string|in:Semilleros con Métricas,Aprendices por Semillero,Proyectos por Estado',
            'formato' => 'required|in:pdf,excel',
            'semillero_id' => ['nullable', 'integer', Rule::in($this->semillerosQuery()->pluck('id')->all())],
            'fecha_desde' => 'nullable|date',
            'fecha_hasta' => 'nullable|date|after_or_equal:fecha_desde',
        ]);

        $query = $this->semillerosQuery();
        if (! empty($validated['semillero_id'])) {
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
        $data['meta'] = $this->metaReporte($tipo, $fechaDesde, $fechaHasta);
        $vista = match ($tipo) {
            'Semilleros con Métricas' => 'director_semilleros.reportes.pdf.semilleros_metricas',
            'Aprendices por Semillero' => 'director_semilleros.reportes.pdf.aprendices_por_semillero',
            'Proyectos por Estado' => 'director_semilleros.reportes.pdf.proyectos_por_estado',
            default => abort(400, 'Tipo de reporte no válido'),
        };

        $pdf = Pdf::loadView($vista, $data);
        $nombre = $this->nombreArchivo($tipo, 'pdf');

        return $pdf->download($nombre);
    }

    protected function exportarExcel(string $tipo, $semilleros, ?string $fechaDesde, ?string $fechaHasta): StreamedResponse
    {
        $data = $this->buildDatosReporte($tipo, $semilleros, $fechaDesde, $fechaHasta);
        $meta = $this->metaReporte($tipo, $fechaDesde, $fechaHasta);

        return app(ReporteExportService::class)->generarExcelCompleto(
            titulo: 'Reporte Director de Semilleros',
            subtitulo: 'Informe consolidado para seguimiento académico y toma de decisiones',
            meta: $meta,
            columnHeaders: $this->columnasReporteExcel($tipo),
            data: $data,
            filename: $this->nombreArchivo($tipo, 'xlsx'),
        );
    }

    protected function buildDatosReporte(string $tipo, $semilleros, ?string $fechaDesde, ?string $fechaHasta): array
    {
        $titulo = $tipo;
        $filas = [];

        if ($tipo === 'Semilleros con Métricas') {
            $totIntegrantes = 0;
            $totProyectos = 0;
            $totProductos = 0;
            foreach ($semilleros as $s) {
                $integrantes = $s->members->count();
                $proyectos = $s->projects
                    ->where('estado', EstadoEnum::Activo)
                    ->count();
                $productos = $s->projects
                    ->where('estado', EstadoEnum::Activo)
                    ->flatMap(fn ($p) => $p->projectEvidences ?? collect())
                    ->where('tipo', \App\Enums\TipoEvidenciaEnum::ProductoFinal)
                    ->count();

                $filas[] = [
                    'nombre' => $s->nombre,
                    'codigo' => $s->codigo ?? '—',
                    'lider' => $s->leader?->person?->nombre_completo ?? $s->leader?->email ?? 'Sin líder',
                    'asesores' => $s->advisors->count(),
                    'integrantes' => $integrantes,
                    'proyectos' => $proyectos,
                    'productos' => $productos,
                ];
                $totIntegrantes += $integrantes;
                $totProyectos += $proyectos;
                $totProductos += $productos;
            }
            $topSemillerosIntegrantes = collect($filas)
                ->sortByDesc('integrantes')
                ->take(6)
                ->values()
                ->map(fn ($f) => ['label' => $f['nombre'], 'value' => (int) $f['integrantes']])
                ->all();

            return [
                'titulo' => $titulo,
                'filas' => $filas,
                'semilleros' => $semilleros,
                'resumen' => [
                    'total_semilleros' => count($filas),
                    'total_integrantes' => $totIntegrantes,
                    'total_proyectos' => $totProyectos,
                    'total_productos' => $totProductos,
                ],
                'chart' => [
                    'title' => 'Integrantes por semillero (Top 6)',
                    'description' => 'Compara la carga actual de integrantes entre semilleros para apoyar decisiones de acompañamiento.',
                    'items' => $topSemillerosIntegrantes,
                ],
            ];
        }

        if ($tipo === 'Aprendices por Semillero') {
            $contadorSemilleros = [];
            $aprendices = \App\Models\ProjectLearner::query()
                ->whereHas('project', fn ($q) => $q->whereIn('seedling_id', $semilleros->pluck('id')))
                ->with('project.seedling', 'trainingProgram')
                ->get();

            foreach ($aprendices as $a) {
                $semilleroNombre = $a->project?->seedling?->nombre ?? '—';
                $filas[] = [
                    'semillero' => $semilleroNombre,
                    'proyecto' => $a->project?->nombre ?? '—',
                    'documento' => $a->numero_documento ?? '',
                    'nombre' => $a->nombre_completo ?? '',
                    'ficha' => $a->ficha ?? '',
                    'tecnologo' => $a->trainingProgram?->nombre ?? '',
                ];
                $contadorSemilleros[$semilleroNombre] = ($contadorSemilleros[$semilleroNombre] ?? 0) + 1;
            }
            $chartItems = collect($contadorSemilleros)
                ->sortDesc()
                ->take(8)
                ->map(fn ($value, $label) => ['label' => $label, 'value' => (int) $value])
                ->values()
                ->all();

            return [
                'titulo' => $titulo,
                'filas' => $filas,
                'semilleros' => $semilleros,
                'resumen' => [
                    'total_registros' => count($filas),
                    'total_semilleros' => count($contadorSemilleros),
                ],
                'chart' => [
                    'title' => 'Distribución de aprendices por semillero',
                    'description' => 'Muestra la concentración de aprendices por semillero para equilibrar la asignación académica.',
                    'items' => $chartItems,
                ],
            ];
        }

        // Proyectos por Estado
        $proyectosQuery = Project::query()
            ->whereIn('seedling_id', $semilleros->pluck('id'))
            ->with(['seedling:id,nombre', 'liderProyecto.person', 'projectEvidences']);
        if ($fechaDesde) {
            $proyectosQuery->whereDate('fecha_inicio', '>=', $fechaDesde);
        }
        if ($fechaHasta) {
            $proyectosQuery->whereDate('fecha_fin', '<=', $fechaHasta);
        }
        $proyectos = $proyectosQuery->orderBy('estado')->orderBy('nombre')->get();
        foreach ($proyectos as $p) {
            $filas[] = [
                'semillero' => $p->seedling?->nombre ?? '—',
                'proyecto' => $p->nombre,
                'estado' => $this->estadoProyectoLabel($p->estado),
                'responsable' => $p->liderProyecto?->person?->nombre_completo ?? $p->liderProyecto?->email ?? '—',
                'productos' => (int) $p->projectEvidences->where('tipo', \App\Enums\TipoEvidenciaEnum::ProductoFinal)->count(),
                'fecha_inicio' => $p->fecha_inicio?->format('d/m/Y') ?? '—',
                'fecha_fin' => $p->fecha_fin?->format('d/m/Y') ?? '—',
            ];
        }

        return [
            'titulo' => $titulo,
            'filas' => $filas,
            'semilleros' => $semilleros,
            'proyectos' => $proyectos,
            'resumen' => [
                'total_proyectos' => count($filas),
                'por_estado' => collect($filas)->groupBy('estado')->map->count()->toArray(),
            ],
            'chart' => [
                'title' => 'Proyectos por estado',
                'description' => 'Permite identificar rápidamente cuántos proyectos están activos, en formulación o cerrados.',
                'items' => collect($filas)->groupBy('estado')->map->count()
                    ->sortDesc()
                    ->map(fn ($value, $label) => ['label' => $label, 'value' => (int) $value])
                    ->values()
                    ->all(),
            ],
        ];
    }

    protected function estadoProyectoLabel($estado): string
    {
        if (is_object($estado) && isset($estado->value)) {
            return (string) $estado->value;
        }

        return (string) ($estado ?? '—');
    }

    protected function nombreArchivo(string $tipo, string $ext): string
    {
        $slug = str_replace(' ', '_', $tipo);
        $fecha = now()->format('Y-m-d_H-i');

        return "reporte_{$slug}_{$fecha}.{$ext}";
    }

    protected function metaReporte(string $tipo, ?string $fechaDesde, ?string $fechaHasta): array
    {
        $usuario = Auth::user();

        return [
            'tipo' => $tipo,
            'generado_en' => now('America/Bogota')->format('d/m/Y H:i'),
            'usuario' => $usuario->person?->nombre_completo ?? $usuario->email,
            'centro' => $usuario->trainingCenter?->nombre ?? 'Centro no definido',
            'fecha_desde' => $fechaDesde ? date('d/m/Y', strtotime($fechaDesde)) : 'Sin filtro',
            'fecha_hasta' => $fechaHasta ? date('d/m/Y', strtotime($fechaHasta)) : 'Sin filtro',
        ];
    }

    protected function columnasReporteExcel(string $tipo): array
    {
        if ($tipo === 'Semilleros con Métricas') {
            return [
                'nombre' => 'Semillero',
                'codigo' => 'Código',
                'lider' => 'Líder',
                'asesores' => 'Asesores',
                'integrantes' => 'Integrantes',
                'proyectos' => 'Proyectos activos',
                'productos' => 'Productos',
            ];
        }

        if ($tipo === 'Aprendices por Semillero') {
            return [
                'semillero' => 'Semillero',
                'proyecto' => 'Proyecto',
                'documento' => 'Documento',
                'nombre' => 'Nombre',
                'ficha' => 'Ficha',
                'tecnologo' => 'Tecnólogo',
            ];
        }

        return [
            'semillero' => 'Semillero(s)',
            'proyecto' => 'Proyecto',
            'estado' => 'Estado',
            'responsable' => 'Responsable',
            'productos' => 'Productos',
            'fecha_inicio' => 'Fecha inicio',
            'fecha_fin' => 'Fecha fin',
        ];
    }
}
