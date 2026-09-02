<?php

namespace App\Http\Controllers\LiderSemillero;

use App\Enums\TipoEvidenciaEnum;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectLearner;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Reportes del semillero que lidera el usuario (rediseño de roles: un
 * lider_semillero lidera exactamente un semillero, vía Seedling::leader_id).
 */
class ReporteController extends Controller
{
    public function index()
    {
        if (! Auth::user()->canAny(['reportes.semilleros_con_metricas', 'reportes.aprendices_por_semillero'])) {
            abort(403, 'No tienes permisos para ver reportes.');
        }

        return view('lider_semillero.reportes.index');
    }

    public function exportar(Request $request)
    {
        $this->authorize('reportes.exportar_pdf_excel');

        $validated = $request->validate([
            'tipo_reporte' => 'required|string|in:Proyectos del Semillero,Aprendices por Semillero',
            'formato' => 'required|in:pdf,excel',
        ]);

        $tipo = $validated['tipo_reporte'];
        $formato = $validated['formato'];

        if ($tipo === 'Proyectos del Semillero') {
            $this->authorize('reportes.semilleros_con_metricas');
        } else {
            $this->authorize('reportes.aprendices_por_semillero');
        }

        $semillero = Auth::user()->ledSeedlings()->first();
        abort_if(! $semillero, 404, 'No tienes un semillero asignado.');

        $data = $this->buildDatosReporte($tipo, $semillero);
        $data['meta'] = $this->metaReporte($tipo, $semillero);

        return $formato === 'pdf'
            ? $this->exportarPdf($tipo, $data)
            : $this->exportarExcel($tipo, $data);
    }

    protected function exportarPdf(string $tipo, array $data)
    {
        $vista = $tipo === 'Proyectos del Semillero'
            ? 'lider_semillero.reportes.pdf.proyectos_semillero'
            : 'lider_semillero.reportes.pdf.aprendices_semillero';

        $pdf = Pdf::loadView($vista, $data);

        return $pdf->download($this->nombreArchivo($tipo, 'pdf'));
    }

    protected function exportarExcel(string $tipo, array $data)
    {
        $nombre = $this->nombreArchivo($tipo, 'xlsx');
        $headers = $this->columnasReporteExcel($tipo);
        $keys = array_keys($headers);
        $titles = array_values($headers);
        $meta = $data['meta'];

        return new StreamedResponse(function () use ($data, $keys, $titles, $meta) {
            $spreadsheet = new Spreadsheet;
            $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(11);
            $hoja = $spreadsheet->getActiveSheet();
            $hoja->setTitle('Reporte');

            $lastCol = Coordinate::stringFromColumnIndex(max(4, count($titles)));

            $hoja->mergeCells("A1:{$lastCol}1");
            $hoja->setCellValue('A1', 'Reporte Líder de Semillero');
            $hoja->mergeCells("A2:{$lastCol}2");
            $hoja->setCellValue('A2', $meta['tipo'] ?? '—');
            $hoja->getStyle("A1:{$lastCol}1")->applyFromArray([
                'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E7E34']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $hoja->getStyle("A2:{$lastCol}2")->applyFromArray([
                'font' => ['italic' => true, 'size' => 10, 'color' => ['argb' => 'FF334155']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE2FBE8']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $hoja->getRowDimension(1)->setRowHeight(26);

            $hoja->setCellValue('A4', 'Semillero');
            $hoja->setCellValue('B4', $meta['semillero'] ?? '—');
            $hoja->setCellValue('A5', 'Generado por');
            $hoja->setCellValue('B5', $meta['usuario'] ?? '—');
            $hoja->setCellValue('A6', 'Generado en');
            $hoja->setCellValue('B6', $meta['generado_en'] ?? '—');
            $hoja->getStyle('A4:A6')->getFont()->setBold(true);
            $hoja->getStyle('A4:B6')->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD1D5DB']]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF8FAFC']],
            ]);

            $tableStartRow = 8;
            $colIndex = 1;
            foreach ($titles as $title) {
                $hoja->setCellValue(Coordinate::stringFromColumnIndex($colIndex).$tableStartRow, $title);
                $colIndex++;
            }
            $headerRange = "A{$tableStartRow}:{$lastCol}{$tableStartRow}";
            $hoja->getStyle($headerRange)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0F766E']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);

            $currentRow = $tableStartRow + 1;
            foreach (($data['filas'] ?? []) as $fila) {
                $colIndex = 1;
                foreach ($keys as $key) {
                    $hoja->setCellValue(Coordinate::stringFromColumnIndex($colIndex).$currentRow, $fila[$key] ?? '—');
                    $colIndex++;
                }
                $currentRow++;
            }
            if ($currentRow === $tableStartRow + 1) {
                $hoja->setCellValue("A{$currentRow}", 'Sin datos para exportar');
                $hoja->mergeCells("A{$currentRow}:{$lastCol}{$currentRow}");
                $currentRow++;
            }

            $bodyRange = "A{$tableStartRow}:{$lastCol}".($currentRow - 1);
            $hoja->getStyle($bodyRange)->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE5E7EB']]],
            ]);
            $hoja->setAutoFilter($headerRange);
            for ($i = $tableStartRow + 1; $i < $currentRow; $i++) {
                if ($i % 2 === 0) {
                    $hoja->getStyle("A{$i}:{$lastCol}{$i}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF8FAFC');
                }
            }

            foreach (range(1, count($titles)) as $i) {
                $hoja->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
            }
            $hoja->freezePane('A'.($tableStartRow + 1));

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$nombre.'"',
            'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
            'Pragma' => 'public',
        ]);
    }

    protected function buildDatosReporte(string $tipo, \App\Models\Seedling $semillero): array
    {
        if ($tipo === 'Proyectos del Semillero') {
            $proyectos = Project::where('seedling_id', $semillero->id)
                ->with('liderProyecto.person')
                ->orderBy('nombre')
                ->get();

            $filas = $proyectos->map(function (Project $p) {
                $desarrollo = $p->projectEvidences()->where('tipo', TipoEvidenciaEnum::Desarrollo)->count();
                $productoFinal = $p->projectEvidences()->where('tipo', TipoEvidenciaEnum::ProductoFinal)->count();

                return [
                    'proyecto' => $p->nombre,
                    'lider_proyecto' => $p->liderProyecto?->person?->nombre_completo ?? $p->liderProyecto?->email ?? 'Sin asignar',
                    'estado' => (string) ($p->estado?->value ?? '—'),
                    'evidencias_desarrollo' => $desarrollo,
                    'evidencias_producto_final' => $productoFinal,
                ];
            })->all();

            return [
                'titulo' => $tipo,
                'filas' => $filas,
                'resumen' => [
                    'total_proyectos' => count($filas),
                ],
            ];
        }

        // Aprendices por Semillero (de este único semillero)
        $aprendices = ProjectLearner::whereHas('project', fn ($q) => $q->where('seedling_id', $semillero->id))
            ->with('project', 'trainingProgram')
            ->get();

        $filas = $aprendices->map(fn (ProjectLearner $a) => [
            'proyecto' => $a->project?->nombre ?? '—',
            'documento' => $a->numero_documento ?? '',
            'nombre' => $a->nombre_completo ?? '',
            'ficha' => $a->ficha ?? '',
            'tecnologo' => $a->trainingProgram?->nombre ?? '',
        ])->all();

        return [
            'titulo' => $tipo,
            'filas' => $filas,
            'resumen' => [
                'total_aprendices' => count($filas),
            ],
        ];
    }

    protected function columnasReporteExcel(string $tipo): array
    {
        if ($tipo === 'Proyectos del Semillero') {
            return [
                'proyecto' => 'Proyecto',
                'lider_proyecto' => 'Líder de Proyecto',
                'estado' => 'Estado',
                'evidencias_desarrollo' => 'Evidencias Desarrollo',
                'evidencias_producto_final' => 'Evidencias Producto Final',
            ];
        }

        return [
            'proyecto' => 'Proyecto',
            'documento' => 'Documento',
            'nombre' => 'Nombre',
            'ficha' => 'Ficha',
            'tecnologo' => 'Tecnólogo',
        ];
    }

    protected function metaReporte(string $tipo, \App\Models\Seedling $semillero): array
    {
        $usuario = Auth::user();

        return [
            'tipo' => $tipo,
            'semillero' => $semillero->nombre,
            'generado_en' => now('America/Bogota')->format('d/m/Y H:i'),
            'usuario' => $usuario->person?->nombre_completo ?? $usuario->email,
        ];
    }

    protected function nombreArchivo(string $tipo, string $ext): string
    {
        $slug = str_replace(' ', '_', $tipo);
        $fecha = now()->format('Y-m-d_H-i');

        return "reporte_lider_semillero_{$slug}_{$fecha}.{$ext}";
    }
}
