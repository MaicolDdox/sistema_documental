<?php

declare(strict_types=1);

namespace App\Services\Reportes;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Servicio transversal de exportación Excel para reportes.
 *
 * Centraliza la construcción del Spreadsheet, estilos y formato.
 * Cada controlador sigue siendo responsable de filtrar sus propios datos
 * (respetando training_center_id) y de mapear las filas; este servicio
 * recibe los datos ya preparados y genera la respuesta de descarga.
 *
 * Dos variantes:
 *  - generarExcelSimple()   → Admin, LiderSemillero (meta 3 filas, tabla fija en fila 8)
 *  - generarExcelCompleto() → DirectorSemilleros (meta extendida, KPIs, análisis, ranking, hallazgos)
 */
class ReporteExportService
{
    // ─────────────────────────────────────────────────
    // API pública
    // ─────────────────────────────────────────────────

    /**
     * Genera un Excel en formato simple.
     *
     * @param  string  $titulo  Texto fila 1 (cabecera verde oscuro).
     * @param  string  $subtitulo  Texto fila 2 (fondo verde claro).
     * @param  array  $metaItems  Hasta 3 pares ['label' => '...', 'value' => '...'] (filas 4-6).
     * @param  array  $columnHeaders  Mapa clave => título de columna para la tabla.
     * @param  array  $filas  Filas de datos; cada elemento es un array asociativo con las mismas claves que $columnHeaders.
     * @param  string  $filename  Nombre del archivo .xlsx para la descarga.
     */
    public function generarExcelSimple(
        string $titulo,
        string $subtitulo,
        array $metaItems,
        array $columnHeaders,
        array $filas,
        string $filename
    ): StreamedResponse {
        return new StreamedResponse(
            function () use ($titulo, $subtitulo, $metaItems, $columnHeaders, $filas): void {
                $spreadsheet = $this->iniciarSpreadsheet();
                $sheet = $spreadsheet->getActiveSheet();

                $keys = array_keys($columnHeaders);
                $titles = array_values($columnHeaders);
                $lastCol = Coordinate::stringFromColumnIndex(max(4, count($titles)));

                // ─── Filas 1-2: título y subtítulo ────────────────
                $this->aplicarFilasTitulo($sheet, $lastCol, $titulo, $subtitulo);
                $sheet->getRowDimension(1)->setRowHeight(26);

                // ─── Meta block filas 4-6 ─────────────────────────
                $metaEndRow = 3;
                foreach ($metaItems as $item) {
                    $metaEndRow++;
                    $sheet->setCellValue("A{$metaEndRow}", $item['label']);
                    $sheet->setCellValue("B{$metaEndRow}", $item['value']);
                }
                $sheet->getStyle("A4:A{$metaEndRow}")->getFont()->setBold(true);
                $sheet->getStyle("A4:B{$metaEndRow}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD1D5DB']]],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF8FAFC']],
                ]);

                // ─── Tabla de datos (fila 8 fija) ─────────────────
                $tableStartRow = 8;
                $this->aplicarTabla($sheet, $tableStartRow, $lastCol, $keys, $titles, $filas);

                $this->autoSizeColumnas($sheet, count($titles));
                $sheet->freezePane('A'.($tableStartRow + 1));

                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
            },
            200,
            $this->headersHttp($filename)
        );
    }

    /**
     * Genera un Excel completo con KPIs, resumen ejecutivo, tabla de datos,
     * indicadores adicionales, ranking y hallazgos automáticos.
     *
     * @param  string  $titulo  Texto fila 1.
     * @param  string  $subtitulo  Texto fila 2.
     * @param  array  $meta  Meta del reporte: tipo, centro, usuario, generado_en, fecha_desde, fecha_hasta.
     * @param  array  $columnHeaders  Mapa clave => título de columna.
     * @param  array  $data  Datos del reporte: filas, resumen, chart (con items).
     * @param  string  $filename  Nombre del archivo .xlsx.
     */
    public function generarExcelCompleto(
        string $titulo,
        string $subtitulo,
        array $meta,
        array $columnHeaders,
        array $data,
        string $filename
    ): StreamedResponse {
        return new StreamedResponse(
            function () use ($titulo, $subtitulo, $meta, $columnHeaders, $data): void {
                $spreadsheet = $this->iniciarSpreadsheet();
                $sheet = $spreadsheet->getActiveSheet();

                $keys = array_keys($columnHeaders);
                $titles = array_values($columnHeaders);
                $lastHeaderColumn = Coordinate::stringFromColumnIndex(max(8, count($columnHeaders)));

                // ─── Filas 1-2: título y subtítulo ────────────────
                $this->aplicarFilasTitulo($sheet, $lastHeaderColumn, $titulo, $subtitulo);
                $sheet->getRowDimension(1)->setRowHeight(28);
                $sheet->getRowDimension(2)->setRowHeight(22);

                // ─── Meta block filas 3-7 ─────────────────────────
                $sheet->setCellValue('A3', 'Tipo');
                $sheet->setCellValue('B3', $meta['tipo'] ?? '—');
                $sheet->setCellValue('A4', 'Centro');
                $sheet->setCellValue('B4', $meta['centro'] ?? '—');
                $sheet->setCellValue('A5', 'Generado por');
                $sheet->setCellValue('B5', $meta['usuario'] ?? '—');
                $sheet->setCellValue('A6', 'Generado en');
                $sheet->setCellValue('B6', $meta['generado_en'] ?? '—');
                $sheet->setCellValue('A7', 'Filtro fechas');
                $sheet->setCellValue('B7', ($meta['fecha_desde'] ?? 'Sin filtro').' - '.($meta['fecha_hasta'] ?? 'Sin filtro'));
                $sheet->getStyle('A3:A7')->getFont()->setBold(true);
                $sheet->getStyle('A3:B7')->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD1D5DB']]],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF8FAFC']],
                ]);
                $sheet->getStyle('A3:B3')->getFill()->getStartColor()->setARGB('FFE0F2FE');
                $sheet->getStyle('A4:B4')->getFill()->getStartColor()->setARGB('FFE0F2FE');

                // ─── Tarjetas KPI (columna D en adelante, filas 3-6) ──
                $kpis = array_slice($this->flattenResumenExcel($data['resumen'] ?? []), 0, 4);
                $kpiStartCol = 4; // D
                foreach ($kpis as $i => $kpi) {
                    $col = Coordinate::stringFromColumnIndex($kpiStartCol + ($i * 2));
                    $nextCol = Coordinate::stringFromColumnIndex($kpiStartCol + ($i * 2) + 1);
                    $sheet->mergeCells("{$col}3:{$nextCol}3");
                    $sheet->mergeCells("{$col}4:{$nextCol}6");
                    $sheet->setCellValue("{$col}3", $kpi['label']);
                    $sheet->setCellValue("{$col}4", $kpi['value']);
                    $sheet->getStyle("{$col}3:{$nextCol}6")->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCBD5E1']]],
                    ]);
                    $sheet->getStyle("{$col}3:{$nextCol}3")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF0F172A']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFDBEAFE']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);
                    $sheet->getStyle("{$col}4:{$nextCol}6")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 18, 'color' => ['argb' => 'FF0F766E']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF8FAFC']],
                    ]);
                }

                // ─── Resumen ejecutivo detallado ───────────────────
                $row = 9;
                $sheet->setCellValue("A{$row}", 'Resumen ejecutivo detallado');
                $sheet->mergeCells("A{$row}:B{$row}");
                $sheet->getStyle("A{$row}:B{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FF166534']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFDCFCE7']],
                ]);
                $row++;
                foreach ($this->flattenResumenExcel($data['resumen'] ?? []) as $item) {
                    $sheet->setCellValue("A{$row}", $item['label']);
                    $sheet->setCellValue("B{$row}", $item['value']);
                    $row++;
                }
                $sheet->getStyle('A10:B'.max(10, $row - 1))->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE5E7EB']]],
                ]);

                // ─── Tabla de datos (fila dinámica) ───────────────
                $tableStartRow = $row + 2;
                $currentRow = $this->aplicarTabla($sheet, $tableStartRow, $lastHeaderColumn, $keys, $titles, $data['filas'] ?? []);

                // ─── Indicadores adicionales ───────────────────────
                $analysis = $this->buildAnalysisMetrics($data);
                $analysisRow = $currentRow + 2;
                $sheet->setCellValue("A{$analysisRow}", 'Indicadores adicionales');
                $sheet->mergeCells("A{$analysisRow}:D{$analysisRow}");
                $sheet->getStyle("A{$analysisRow}:D{$analysisRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FF0F172A']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFDE68A']],
                ]);
                $analysisRow++;
                foreach ($analysis as $metric) {
                    $sheet->setCellValue("A{$analysisRow}", $metric['label']);
                    $sheet->setCellValue("B{$analysisRow}", $metric['value']);
                    $sheet->setCellValue("C{$analysisRow}", $metric['detalle']);
                    $sheet->mergeCells("C{$analysisRow}:D{$analysisRow}");
                    $analysisRow++;
                }
                $sheet->getStyle('A'.($currentRow + 3).':D'.($analysisRow - 1))->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE5E7EB']]],
                ]);

                // ─── Ranking de categorías (Top 10) ───────────────
                $rankingRow = $analysisRow + 1;
                $sheet->setCellValue("A{$rankingRow}", 'Ranking de categorías (Top 10)');
                $sheet->mergeCells("A{$rankingRow}:D{$rankingRow}");
                $sheet->getStyle("A{$rankingRow}:D{$rankingRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1D4ED8']],
                ]);
                $rankingRow++;
                $sheet->setCellValue("A{$rankingRow}", 'Posición');
                $sheet->setCellValue("B{$rankingRow}", 'Categoría');
                $sheet->setCellValue("C{$rankingRow}", 'Valor');
                $sheet->setCellValue("D{$rankingRow}", 'Participación');
                $sheet->getStyle("A{$rankingRow}:D{$rankingRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0F766E']],
                ]);
                $rankingRow++;
                $rankingItems = $this->buildRankingRows($data['chart']['items'] ?? []);
                foreach ($rankingItems as $item) {
                    $sheet->setCellValue("A{$rankingRow}", $item['posicion']);
                    $sheet->setCellValue("B{$rankingRow}", $item['categoria']);
                    $sheet->setCellValue("C{$rankingRow}", $item['valor']);
                    $sheet->setCellValue("D{$rankingRow}", $item['participacion']);
                    $rankingRow++;
                }
                if ($rankingItems === []) {
                    $sheet->setCellValue("A{$rankingRow}", '1');
                    $sheet->setCellValue("B{$rankingRow}", 'Sin datos');
                    $sheet->setCellValue("C{$rankingRow}", '0');
                    $sheet->setCellValue("D{$rankingRow}", '0.00%');
                    $rankingRow++;
                }
                $sheet->getStyle('A'.($analysisRow + 3).':D'.($rankingRow - 1))->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE5E7EB']]],
                ]);
                $sheet->getStyle('D'.($analysisRow + 5).':D'.($rankingRow - 1))->getNumberFormat()->setFormatCode('0.00%');

                // ─── Hallazgos y recomendaciones ──────────────────
                $insights = $this->buildInsights($data);
                $insightRow = $rankingRow + 1;
                $sheet->setCellValue("A{$insightRow}", 'Hallazgos y recomendaciones');
                $sheet->mergeCells("A{$insightRow}:{$lastHeaderColumn}{$insightRow}");
                $sheet->getStyle("A{$insightRow}:{$lastHeaderColumn}{$insightRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF334155']],
                ]);
                $insightRow++;
                foreach ($insights as $line) {
                    $sheet->setCellValue("A{$insightRow}", '- '.$line);
                    $sheet->mergeCells("A{$insightRow}:{$lastHeaderColumn}{$insightRow}");
                    $sheet->getStyle("A{$insightRow}:{$lastHeaderColumn}{$insightRow}")->getAlignment()->setWrapText(true);
                    $insightRow++;
                }

                $this->autoSizeColumnas($sheet, count($columnHeaders));
                $sheet->freezePane('A'.($tableStartRow + 1));

                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
            },
            200,
            $this->headersHttp($filename)
        );
    }

    // ─────────────────────────────────────────────────
    // Helpers privados — construcción del Spreadsheet
    // ─────────────────────────────────────────────────

    private function iniciarSpreadsheet(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(11);
        $spreadsheet->getActiveSheet()->setTitle('Reporte');

        return $spreadsheet;
    }

    /**
     * Escribe las filas 1 y 2: cabecera verde oscuro + subtítulo verde claro.
     */
    private function aplicarFilasTitulo(Worksheet $sheet, string $lastCol, string $titulo, string $subtitulo): void
    {
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', $titulo);
        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->setCellValue('A2', $subtitulo);

        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E7E34']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle("A2:{$lastCol}2")->applyFromArray([
            'font' => ['italic' => true, 'size' => 10, 'color' => ['argb' => 'FF334155']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE2FBE8']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
    }

    /**
     * Escribe encabezados, datos, estado vacío, bordes, filtro automático y rayas zebra.
     * Devuelve la primera fila disponible después de la tabla.
     */
    private function aplicarTabla(Worksheet $sheet, int $tableStartRow, string $lastCol, array $keys, array $titles, array $filas): int
    {
        // Encabezados de columna
        $colIndex = 1;
        foreach ($titles as $title) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex).$tableStartRow, $title);
            $colIndex++;
        }
        $headerRange = "A{$tableStartRow}:{$lastCol}{$tableStartRow}";
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0F766E']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Filas de datos
        $currentRow = $tableStartRow + 1;
        foreach ($filas as $fila) {
            $colIndex = 1;
            foreach ($keys as $key) {
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex).$currentRow, $fila[$key] ?? '—');
                $colIndex++;
            }
            $currentRow++;
        }

        // Estado vacío
        if ($currentRow === $tableStartRow + 1) {
            $sheet->setCellValue("A{$currentRow}", 'Sin datos para exportar');
            $sheet->mergeCells("A{$currentRow}:{$lastCol}{$currentRow}");
            $currentRow++;
        }

        // Bordes, autofilter y zebra
        $bodyRange = "A{$tableStartRow}:{$lastCol}".($currentRow - 1);
        $sheet->getStyle($bodyRange)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE5E7EB']]],
        ]);
        $sheet->setAutoFilter($headerRange);
        for ($i = $tableStartRow + 1; $i < $currentRow; $i++) {
            if ($i % 2 === 0) {
                $sheet->getStyle("A{$i}:{$lastCol}{$i}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF8FAFC');
            }
        }

        return $currentRow;
    }

    private function autoSizeColumnas(Worksheet $sheet, int $count): void
    {
        foreach (range(1, $count) as $i) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }
    }

    /**
     * @return array<string, string>
     */
    private function headersHttp(string $filename): array
    {
        return [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
            'Pragma' => 'public',
        ];
    }

    // ─────────────────────────────────────────────────
    // Helpers privados — análisis de datos
    // ─────────────────────────────────────────────────

    /**
     * Aplana un array de resumen anidado a pares [label, value] para el Spreadsheet.
     *
     * @param  array<string, mixed>  $resumen
     * @return array<int, array{label: string, value: string}>
     */
    private function flattenResumenExcel(array $resumen): array
    {
        $items = [];
        foreach ($resumen as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $subK => $subV) {
                    $items[] = [
                        'label' => ucwords(str_replace('_', ' ', (string) $k)).' - '.(string) $subK,
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

    /**
     * @param  array{filas?: array<mixed>, chart?: array{items?: array<mixed>}}  $data
     * @return list<array{label: string, value: string}>
     */
    private function buildInsights(array $data): array
    {
        $items = $data['chart']['items'] ?? [];
        if ($items === []) {
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

    /**
     * @param  array{filas?: array<mixed>, chart?: array{items?: array<mixed>}}  $data
     * @return list<array{label: string, value: int|float|string, detalle: string}>
     */
    private function buildAnalysisMetrics(array $data): array
    {
        $items = collect($data['chart']['items'] ?? []);
        $total = (int) $items->sum(fn ($i) => (int) ($i['value'] ?? 0));
        $count = $items->count();
        $avg = $count > 0 ? round($total / $count, 2) : 0;
        $max = $items->sortByDesc('value')->first();
        $min = $items->sortBy('value')->first();

        return [
            ['label' => 'Registros exportados',  'value' => count($data['filas'] ?? []), 'detalle' => 'Cantidad total de filas incluidas en el reporte.'],
            ['label' => 'Categorías analizadas',  'value' => $count,                     'detalle' => 'Número de categorías contempladas en la sección gráfica.'],
            ['label' => 'Valor acumulado',         'value' => $total,                     'detalle' => 'Suma total del indicador principal del gráfico.'],
            ['label' => 'Promedio por categoría', 'value' => $avg,                       'detalle' => 'Promedio del indicador considerando todas las categorías.'],
            ['label' => 'Mayor categoría',         'value' => ($max['label'] ?? 'Sin datos'), 'detalle' => 'Valor: '.(string) ($max['value'] ?? 0)],
            ['label' => 'Menor categoría',         'value' => ($min['label'] ?? 'Sin datos'), 'detalle' => 'Valor: '.(string) ($min['value'] ?? 0)],
        ];
    }

    /**
     * @param  array<int, array{label?: string, value?: mixed}>  $items
     * @return list<array{posicion: int, categoria: string, valor: int, participacion: float}>
     */
    private function buildRankingRows(array $items): array
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
