<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EstadoEnum;
use App\Http\Controllers\Controller;
use App\Models\ProjectEvidence;
use App\Models\Seedling;
use App\Models\User;
use App\Support\TrainingCenterAccess;
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

class ReporteController extends Controller
{
    public function index()
    {
        if (! Auth::user()->canAny(['reportes.usuarios_por_rol', 'reportes.semilleros_con_metricas'])) {
            abort(403, 'No tienes permisos para ver reportes.');
        }

        return view('admin.reportes.index');
    }

    public function exportar(Request $request)
    {
        $this->authorize('reportes.exportar_pdf_excel');

        $validated = $request->validate([
            'tipo_reporte' => 'required|string|in:Usuarios por Rol,Semilleros con Métricas',
            'formato' => 'required|in:pdf,excel',
        ]);

        $tipo = $validated['tipo_reporte'];
        $formato = $validated['formato'];

        if ($tipo === 'Usuarios por Rol') {
            $this->authorize('reportes.usuarios_por_rol');
        } else {
            $this->authorize('reportes.semilleros_con_metricas');
        }

        $data = $this->buildDatosReporte($tipo);
        $data['meta'] = $this->metaReporte($tipo);

        return $formato === 'pdf'
            ? $this->exportarPdf($tipo, $data)
            : $this->exportarExcel($tipo, $data);
    }

    protected function exportarPdf(string $tipo, array $data)
    {
        $vista = $tipo === 'Usuarios por Rol'
            ? 'admin.reportes.pdf.usuarios_por_rol'
            : 'admin.reportes.pdf.semilleros_metricas';

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

        return new StreamedResponse(function () use ($tipo, $data, $keys, $titles, $meta) {
            $spreadsheet = new Spreadsheet;
            $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(11);
            $hoja = $spreadsheet->getActiveSheet();
            $hoja->setTitle('Reporte');

            $lastCol = Coordinate::stringFromColumnIndex(max(4, count($titles)));

            $hoja->mergeCells("A1:{$lastCol}1");
            $hoja->setCellValue('A1', 'Reporte Administrador del Sistema');
            $hoja->mergeCells("A2:{$lastCol}2");
            $hoja->setCellValue('A2', $tipo);
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

            $hoja->setCellValue('A4', 'Centro');
            $hoja->setCellValue('B4', $meta['centro'] ?? '—');
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

    protected function buildDatosReporte(string $tipo): array
    {
        $centerId = Auth::user()->training_center_id;

        if ($tipo === 'Usuarios por Rol') {
            // scopeUserQueryForMetrics() (no scopeUserQueryForList): este es
            // un reporte completo del centro — el propio admin es un usuario
            // real con un rol real y debe aparecer (BUG-20260813-035).
            $usuarios = TrainingCenterAccess::scopeUserQueryForMetrics(
                User::with(['person', 'roles']),
                Auth::user()
            )->get();

            $filas = $usuarios->map(fn (User $u) => [
                'nombre' => $u->person?->nombre_completo ?? $u->email,
                'documento' => $u->numero_documento ?? '—',
                'rol' => $u->roles->pluck('name')->map(fn ($r) => ucwords(str_replace('_', ' ', (string) $r)))->implode(', ') ?: 'Sin rol',
                'estado' => (string) ($u->estado?->value ?? '—'),
            ])->all();

            $porRol = $usuarios->flatMap(fn (User $u) => $u->roles->pluck('name'))
                ->countBy()
                ->map(fn ($v, $k) => ucwords(str_replace('_', ' ', (string) $k)).': '.$v)
                ->values()
                ->implode(' | ');

            return [
                'titulo' => $tipo,
                'filas' => $filas,
                'resumen' => [
                    'total_usuarios' => $usuarios->count(),
                    'por_rol' => $porRol ?: 'Sin datos',
                ],
            ];
        }

        // Semilleros con Métricas
        $semilleros = Seedling::with(['leader.person'])
            ->when($centerId, fn ($q) => $q->where('training_center_id', $centerId))
            ->orderBy('nombre')
            ->get();

        $filas = $semilleros->map(function (Seedling $s) {
            $proyectosActivos = $s->projects()->where('estado', EstadoEnum::Activo)->count();
            $productos = ProjectEvidence::where('tipo', \App\Enums\TipoEvidenciaEnum::ProductoFinal)
                ->whereHas('project', fn ($q) => $q->where('seedling_id', $s->id))
                ->count();

            return [
                'nombre' => $s->nombre,
                'codigo' => $s->codigo ?? '—',
                'lider' => $s->leader?->person?->nombre_completo ?? $s->leader?->email ?? 'Sin líder',
                'estado' => (string) ($s->estado?->value ?? '—'),
                'proyectos_activos' => $proyectosActivos,
                'productos' => $productos,
            ];
        })->all();

        return [
            'titulo' => $tipo,
            'filas' => $filas,
            'resumen' => [
                'total_semilleros' => count($filas),
                'activos' => $semilleros->where('estado', EstadoEnum::Activo)->count(),
            ],
        ];
    }

    protected function columnasReporteExcel(string $tipo): array
    {
        if ($tipo === 'Usuarios por Rol') {
            return [
                'nombre' => 'Nombre',
                'documento' => 'Documento',
                'rol' => 'Rol(es)',
                'estado' => 'Estado',
            ];
        }

        return [
            'nombre' => 'Semillero',
            'codigo' => 'Código',
            'lider' => 'Líder',
            'estado' => 'Estado',
            'proyectos_activos' => 'Proyectos activos',
            'productos' => 'Productos',
        ];
    }

    protected function metaReporte(string $tipo): array
    {
        $usuario = Auth::user();

        return [
            'tipo' => $tipo,
            'generado_en' => now('America/Bogota')->format('d/m/Y H:i'),
            'usuario' => $usuario->person?->nombre_completo ?? $usuario->email,
            'centro' => $usuario->trainingCenter?->nombre ?? 'Centro no definido',
        ];
    }

    protected function nombreArchivo(string $tipo, string $ext): string
    {
        $slug = str_replace(' ', '_', $tipo);
        $fecha = now()->format('Y-m-d_H-i');

        return "reporte_admin_{$slug}_{$fecha}.{$ext}";
    }
}
