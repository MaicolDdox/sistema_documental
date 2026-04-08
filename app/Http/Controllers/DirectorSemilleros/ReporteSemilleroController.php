<?php

namespace App\Http\Controllers\DirectorSemilleros;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Seedling;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
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
        $data['meta'] = $this->metaReporte($tipo, $fechaDesde, $fechaHasta);
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
        $meta = $this->metaReporte($tipo, $fechaDesde, $fechaHasta);
        $nombre = $this->nombreArchivo($tipo, 'xlsx');

        return new StreamedResponse(function () use ($tipo, $data, $meta) {
            $spreadsheet = new Spreadsheet();
            $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(11);
            $hojaReporte = $spreadsheet->getActiveSheet();
            $hojaReporte->setTitle('Reporte');

            $headers = $this->columnasReporteExcel($tipo);
            $keys = array_keys($headers);
            $titles = array_values($headers);
            $lastHeaderColumn = Coordinate::stringFromColumnIndex(max(8, count($headers)));

            $hojaReporte->mergeCells("A1:{$lastHeaderColumn}1");
            $hojaReporte->setCellValue('A1', 'Reporte Director de Semilleros');
            $hojaReporte->mergeCells("A2:{$lastHeaderColumn}2");
            $hojaReporte->setCellValue('A2', 'Informe consolidado para seguimiento académico y toma de decisiones');
            $hojaReporte->getStyle("A1:{$lastHeaderColumn}1")->applyFromArray([
                'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E7E34']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $hojaReporte->getStyle("A2:{$lastHeaderColumn}2")->applyFromArray([
                'font' => ['italic' => true, 'size' => 10, 'color' => ['argb' => 'FF334155']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE2FBE8']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $hojaReporte->getRowDimension(1)->setRowHeight(28);
            $hojaReporte->getRowDimension(2)->setRowHeight(22);

            $hojaReporte->setCellValue('A3', 'Tipo');
            $hojaReporte->setCellValue('B3', $meta['tipo'] ?? '—');
            $hojaReporte->setCellValue('A4', 'Centro');
            $hojaReporte->setCellValue('B4', $meta['centro'] ?? '—');
            $hojaReporte->setCellValue('A5', 'Generado por');
            $hojaReporte->setCellValue('B5', $meta['usuario'] ?? '—');
            $hojaReporte->setCellValue('A6', 'Generado en');
            $hojaReporte->setCellValue('B6', $meta['generado_en'] ?? '—');
            $hojaReporte->setCellValue('A7', 'Filtro fechas');
            $hojaReporte->setCellValue('B7', ($meta['fecha_desde'] ?? 'Sin filtro') . ' - ' . ($meta['fecha_hasta'] ?? 'Sin filtro'));
            $hojaReporte->getStyle('A3:A7')->getFont()->setBold(true);
            $hojaReporte->getStyle('A3:B7')->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD1D5DB']]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF8FAFC']],
            ]);
            $hojaReporte->getStyle('A3:B3')->getFill()->getStartColor()->setARGB('FFE0F2FE');
            $hojaReporte->getStyle('A4:B4')->getFill()->getStartColor()->setARGB('FFE0F2FE');

            // Tarjetas KPI
            $kpis = array_slice($this->flattenResumenExcel($data['resumen'] ?? []), 0, 4);
            $kpiStartCol = 4; // D
            foreach ($kpis as $i => $kpi) {
                $col = Coordinate::stringFromColumnIndex($kpiStartCol + ($i * 2));
                $nextCol = Coordinate::stringFromColumnIndex($kpiStartCol + ($i * 2) + 1);
                $hojaReporte->mergeCells("{$col}3:{$nextCol}3");
                $hojaReporte->mergeCells("{$col}4:{$nextCol}6");
                $hojaReporte->setCellValue("{$col}3", $kpi['label']);
                $hojaReporte->setCellValue("{$col}4", $kpi['value']);
                $hojaReporte->getStyle("{$col}3:{$nextCol}6")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCBD5E1']]],
                ]);
                $hojaReporte->getStyle("{$col}3:{$nextCol}3")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF0F172A']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFDBEAFE']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $hojaReporte->getStyle("{$col}4:{$nextCol}6")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 18, 'color' => ['argb' => 'FF0F766E']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF8FAFC']],
                ]);
            }

            $row = 9;
            $hojaReporte->setCellValue("A{$row}", 'Resumen ejecutivo detallado');
            $hojaReporte->mergeCells("A{$row}:B{$row}");
            $hojaReporte->getStyle("A{$row}:B{$row}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['argb' => 'FF166534']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFDCFCE7']],
            ]);
            $row++;

            foreach ($this->flattenResumenExcel($data['resumen'] ?? []) as $item) {
                $hojaReporte->setCellValue("A{$row}", $item['label']);
                $hojaReporte->setCellValue("B{$row}", $item['value']);
                $row++;
            }
            $hojaReporte->getStyle("A10:B" . max(10, $row - 1))->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE5E7EB']]],
            ]);

            $tableStartRow = $row + 2;
            $colIndex = 1;
            foreach ($titles as $title) {
                $col = Coordinate::stringFromColumnIndex($colIndex);
                $hojaReporte->setCellValue($col . $tableStartRow, $title);
                $colIndex++;
            }

            $tableHeaderRange = "A{$tableStartRow}:{$lastHeaderColumn}{$tableStartRow}";
            $hojaReporte->getStyle($tableHeaderRange)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0F766E']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);

            $currentRow = $tableStartRow + 1;
            foreach (($data['filas'] ?? []) as $fila) {
                $colIndex = 1;
                foreach ($keys as $key) {
                    $col = Coordinate::stringFromColumnIndex($colIndex);
                    $hojaReporte->setCellValue($col . $currentRow, $fila[$key] ?? '—');
                    $colIndex++;
                }
                $currentRow++;
            }

            if ($currentRow === $tableStartRow + 1) {
                $hojaReporte->setCellValue("A{$currentRow}", 'Sin datos para exportar');
                $hojaReporte->mergeCells("A{$currentRow}:{$lastHeaderColumn}{$currentRow}");
                $currentRow++;
            }

            $tableBodyRange = "A{$tableStartRow}:{$lastHeaderColumn}" . ($currentRow - 1);
            $hojaReporte->getStyle($tableBodyRange)->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE5E7EB']]],
            ]);
            $hojaReporte->setAutoFilter($tableHeaderRange);
            for ($i = $tableStartRow + 1; $i < $currentRow; $i++) {
                if (($i % 2) === 0) {
                    $hojaReporte->getStyle("A{$i}:{$lastHeaderColumn}{$i}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF8FAFC');
                }
            }

            // Indicadores adicionales del análisis
            $analysis = $this->buildAnalysisMetrics($data);
            $analysisRow = $currentRow + 2;
            $hojaReporte->setCellValue("A{$analysisRow}", 'Indicadores adicionales');
            $hojaReporte->mergeCells("A{$analysisRow}:D{$analysisRow}");
            $hojaReporte->getStyle("A{$analysisRow}:D{$analysisRow}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['argb' => 'FF0F172A']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFDE68A']],
            ]);
            $analysisRow++;
            foreach ($analysis as $metric) {
                $hojaReporte->setCellValue("A{$analysisRow}", $metric['label']);
                $hojaReporte->setCellValue("B{$analysisRow}", $metric['value']);
                $hojaReporte->setCellValue("C{$analysisRow}", $metric['detalle']);
                $hojaReporte->mergeCells("C{$analysisRow}:D{$analysisRow}");
                $analysisRow++;
            }
            $hojaReporte->getStyle("A" . ($currentRow + 3) . ":D" . ($analysisRow - 1))->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE5E7EB']]],
            ]);

            // Ranking por categorías del gráfico (Top 10)
            $rankingRow = $analysisRow + 1;
            $hojaReporte->setCellValue("A{$rankingRow}", 'Ranking de categorías (Top 10)');
            $hojaReporte->mergeCells("A{$rankingRow}:D{$rankingRow}");
            $hojaReporte->getStyle("A{$rankingRow}:D{$rankingRow}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1D4ED8']],
            ]);
            $rankingRow++;
            $hojaReporte->setCellValue("A{$rankingRow}", 'Posición');
            $hojaReporte->setCellValue("B{$rankingRow}", 'Categoría');
            $hojaReporte->setCellValue("C{$rankingRow}", 'Valor');
            $hojaReporte->setCellValue("D{$rankingRow}", 'Participación');
            $hojaReporte->getStyle("A{$rankingRow}:D{$rankingRow}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0F766E']],
            ]);
            $rankingRow++;
            $rankingItems = $this->buildRankingRows($data['chart']['items'] ?? []);
            foreach ($rankingItems as $item) {
                $hojaReporte->setCellValue("A{$rankingRow}", $item['posicion']);
                $hojaReporte->setCellValue("B{$rankingRow}", $item['categoria']);
                $hojaReporte->setCellValue("C{$rankingRow}", $item['valor']);
                $hojaReporte->setCellValue("D{$rankingRow}", $item['participacion']);
                $rankingRow++;
            }
            if (empty($rankingItems)) {
                $hojaReporte->setCellValue("A{$rankingRow}", '1');
                $hojaReporte->setCellValue("B{$rankingRow}", 'Sin datos');
                $hojaReporte->setCellValue("C{$rankingRow}", '0');
                $hojaReporte->setCellValue("D{$rankingRow}", '0.00%');
                $rankingRow++;
            }
            $hojaReporte->getStyle("A" . ($analysisRow + 3) . ":D" . ($rankingRow - 1))->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE5E7EB']]],
            ]);
            $hojaReporte->getStyle("D" . ($analysisRow + 5) . ":D" . ($rankingRow - 1))->getNumberFormat()->setFormatCode('0.00%');

            // Hallazgos automáticos
            $insights = $this->buildInsights($data);
            $insightRow = $rankingRow + 1;
            $hojaReporte->setCellValue("A{$insightRow}", 'Hallazgos y recomendaciones');
            $hojaReporte->mergeCells("A{$insightRow}:{$lastHeaderColumn}{$insightRow}");
            $hojaReporte->getStyle("A{$insightRow}:{$lastHeaderColumn}{$insightRow}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF334155']],
            ]);
            $insightRow++;
            foreach ($insights as $line) {
                $hojaReporte->setCellValue("A{$insightRow}", '- ' . $line);
                $hojaReporte->mergeCells("A{$insightRow}:{$lastHeaderColumn}{$insightRow}");
                $hojaReporte->getStyle("A{$insightRow}:{$lastHeaderColumn}{$insightRow}")->getAlignment()->setWrapText(true);
                $insightRow++;
            }

            foreach (range(1, count($headers)) as $i) {
                $col = Coordinate::stringFromColumnIndex($i);
                $hojaReporte->getColumnDimension($col)->setAutoSize(true);
            }
            $hojaReporte->freezePane('A' . ($tableStartRow + 1));

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition'  => 'attachment; filename="'.$nombre.'"',
            'Cache-Control'       => 'max-age=0, no-cache, no-store, must-revalidate',
            'Pragma'              => 'public',
        ]);
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
                $integrantes = $s->members()->count();
                $proyectos = $s->projects()->where('estado', 'activo')->count();
                $productos = $s->projects()->where('estado', 'activo')->withCount('products')->get()->sum('products_count');

                $filas[] = [
                    'nombre'      => $s->nombre,
                    'codigo'      => $s->codigo ?? '—',
                    'grupo'       => $s->researchGroup->nombre ?? '—',
                    'lider'       => $s->leader?->person?->nombre_completo ?? $s->leader?->email ?? 'Sin líder',
                    'asesores'    => $s->advisors()->count(),
                    'integrantes' => $integrantes,
                    'proyectos'   => $proyectos,
                    'productos'   => $productos,
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
            foreach ($semilleros as $s) {
                foreach ($s->members()->with('person')->get() as $user) {
                    $filas[] = [
                        'semillero'  => $s->nombre,
                        'codigo'     => $s->codigo ?? '—',
                        'documento'  => $user->numero_documento ?? '',
                        'nombre'     => $user->person?->nombre_completo ?? $user->email,
                        'email'      => $user->email ?? '',
                        'estado'     => (string) ($user->estado?->value ?? '—'),
                    ];
                    $contadorSemilleros[$s->nombre] = ($contadorSemilleros[$s->nombre] ?? 0) + 1;
                }
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
                'estado'        => $this->estadoProyectoLabel($p->estado),
                'responsable'   => $p->projectCreator?->person?->nombre_completo ?? $p->projectCreator?->email ?? '—',
                'productos'     => (int) $p->products()->count(),
                'fecha_inicio'  => $p->fecha_inicio?->format('d/m/Y') ?? '—',
                'fecha_fin'     => $p->fecha_fin?->format('d/m/Y') ?? '—',
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
                'grupo' => 'Grupo',
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
                'codigo' => 'Código',
                'documento' => 'Documento',
                'nombre' => 'Nombre',
                'email' => 'Email',
                'estado' => 'Estado',
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

    protected function flattenResumenExcel(array $resumen): array
    {
        $items = [];
        foreach ($resumen as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $subK => $subV) {
                    $items[] = [
                        'label' => ucwords(str_replace('_', ' ', (string) $k)) . ' - ' . (string) $subK,
                        'value' => (string) $subV,
                    ];
                }
                continue;
            }
            $items[] = [
                'label' => ucwords(str_replace('_', ' ', (string) $k)),
                'value' => (string) $v,
            ];
        }

        if ($items === []) {
            $items[] = ['label' => 'Resumen', 'value' => 'Sin datos'];
        }

        return $items;
    }

    protected function buildInsights(array $data): array
    {
        $items = $data['chart']['items'] ?? [];
        if (empty($items)) {
            return ['No hay datos suficientes en el periodo para generar hallazgos cuantitativos.'];
        }

        $maxItem = collect($items)->sortByDesc('value')->first();
        $minItem = collect($items)->sortBy('value')->first();
        $total = collect($items)->sum(fn ($i) => (int) ($i['value'] ?? 0));
        $promedio = count($items) > 0 ? round($total / count($items), 2) : 0;

        return [
            "El mayor valor corresponde a {$maxItem['label']} con {$maxItem['value']}.",
            "El menor valor corresponde a {$minItem['label']} con {$minItem['value']}.",
            "El promedio general del indicador es {$promedio}.",
            'Se recomienda priorizar seguimiento a categorías con menor participación para equilibrar resultados.',
        ];
    }

    protected function buildAnalysisMetrics(array $data): array
    {
        $items = collect($data['chart']['items'] ?? []);
        $total = (int) $items->sum(fn ($i) => (int) ($i['value'] ?? 0));
        $count = $items->count();
        $avg = $count > 0 ? round($total / $count, 2) : 0;
        $max = $items->sortByDesc('value')->first();
        $min = $items->sortBy('value')->first();

        return [
            [
                'label' => 'Registros exportados',
                'value' => count($data['filas'] ?? []),
                'detalle' => 'Cantidad total de filas incluidas en el reporte.',
            ],
            [
                'label' => 'Categorías analizadas',
                'value' => $count,
                'detalle' => 'Número de categorías contempladas en la sección gráfica.',
            ],
            [
                'label' => 'Valor acumulado',
                'value' => $total,
                'detalle' => 'Suma total del indicador principal del gráfico.',
            ],
            [
                'label' => 'Promedio por categoría',
                'value' => $avg,
                'detalle' => 'Promedio del indicador considerando todas las categorías.',
            ],
            [
                'label' => 'Mayor categoría',
                'value' => ($max['label'] ?? 'Sin datos'),
                'detalle' => 'Valor: ' . (string) ($max['value'] ?? 0),
            ],
            [
                'label' => 'Menor categoría',
                'value' => ($min['label'] ?? 'Sin datos'),
                'detalle' => 'Valor: ' . (string) ($min['value'] ?? 0),
            ],
        ];
    }

    protected function buildRankingRows(array $items): array
    {
        $collection = collect($items)->sortByDesc(fn ($i) => (int) ($i['value'] ?? 0))->take(10)->values();
        $total = max(1, (int) $collection->sum(fn ($i) => (int) ($i['value'] ?? 0)));
        $rows = [];

        foreach ($collection as $index => $item) {
            $value = (int) ($item['value'] ?? 0);
            $rows[] = [
                'posicion' => $index + 1,
                'categoria' => (string) ($item['label'] ?? 'N/A'),
                'valor' => $value,
                'participacion' => $value / $total,
            ];
        }

        return $rows;
    }

}
