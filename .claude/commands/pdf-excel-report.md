---
name: pdf-excel-report
description: Genera reportes PDF con DomPDF y exportaciones Excel con PhpSpreadsheet para GIDESTH. Usar cuando necesites crear reportes de semilleros, investigadores, proyectos o cualquier exportación de datos del sistema.
disable-model-invocation: false
---

Genera reportes PDF o Excel en GIDESTH usando los paquetes ya instalados.

## Argumentos
`/pdf-excel-report [tipo: pdf|excel] [descripción del reporte]`

Ejemplo: `/pdf-excel-report pdf reporte de semilleros por centro de formación`

---

## PDF con DomPDF

### Controlador
```php
<?php

namespace App\Http\Controllers\{Rol};

use App\Http\Controllers\Controller;
use App\Support\TrainingCenterAccess;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class {Nombre}ReportController extends Controller
{
    public function generarPdf(): Response
    {
        $this->authorize('viewAny', \App\Models\Modelo::class);

        // Siempre filtrar por training center:
        $datos = Modelo::query()
            ->with(['relacion1', 'relacion2'])
            ->when(
                !TrainingCenterAccess::isSuperAdmin(auth()->user()),
                fn($q) => $q->where('training_center_id', auth()->user()->training_center_id)
            )
            ->where('estado', 'activo')
            ->get();

        $pdf = Pdf::loadView('pdf.{nombre}-reporte', [
            'datos'          => $datos,
            'generadoPor'    => auth()->user()->getNameAttribute(),
            'fechaGeneracion' => now()->format('d/m/Y H:i'),
            'centro'         => auth()->user()->trainingCenter?->nombre ?? 'Todos los centros',
        ]);

        $pdf->setPaper('letter', 'portrait'); // o 'landscape'

        return $pdf->download("{nombre}-reporte-" . now()->format('Y-m-d') . ".pdf");
    }
}
```

### Vista Blade para PDF (`resources/views/pdf/`)
```blade
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #2d6a4f; padding-bottom: 10px; }
        .header h1 { font-size: 16px; color: #2d6a4f; margin: 0; }
        .header p { margin: 3px 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        thead tr { background-color: #2d6a4f; color: white; }
        th { padding: 6px 8px; text-align: left; font-size: 9px; }
        td { padding: 5px 8px; border-bottom: 1px solid #e0e0e0; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center;
                  font-size: 8px; color: #999; border-top: 1px solid #eee; padding-top: 5px; }
        .badge-activo { color: #2d6a4f; font-weight: bold; }
        .badge-inactivo { color: #c0392b; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>GIDESTH — Reporte de {Nombre}</h1>
        <p>Centro: {{ $centro }}</p>
        <p>Generado por: {{ $generadoPor }} | {{ $fechaGeneracion }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Estado</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($datos as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->nombre }}</td>
                <td class="badge-{{ $item->estado->value }}">
                    {{ ucfirst($item->estado->value) }}
                </td>
                <td>{{ $item->created_at->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        GIDESTH — Sistema de Gestión de Semilleros de Investigación | Página <span class="pagenum"></span>
    </div>
</body>
</html>
```

---

## Excel con PhpSpreadsheet

### Controlador
```php
<?php

namespace App\Http\Controllers\{Rol};

use App\Http\Controllers\Controller;
use App\Support\TrainingCenterAccess;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\{Alignment, Border, Fill};
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class {Nombre}ExcelController extends Controller
{
    public function exportar(): StreamedResponse
    {
        $this->authorize('viewAny', \App\Models\Modelo::class);

        $datos = Modelo::query()
            ->with(['relacion'])
            ->when(
                !TrainingCenterAccess::isSuperAdmin(auth()->user()),
                fn($q) => $q->where('training_center_id', auth()->user()->training_center_id)
            )
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Reporte');

        // ── Encabezado del reporte ──
        $sheet->mergeCells('A1:E1');
        $sheet->setCellValue('A1', 'GIDESTH — Reporte de ' . now()->format('d/m/Y'));
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2D6A4F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(25);

        // ── Cabeceras de columnas ──
        $cabeceras = ['#', 'Nombre', 'Estado', 'Centro de Formación', 'Fecha'];
        foreach ($cabeceras as $col => $cabecera) {
            $letra = chr(65 + $col); // A, B, C...
            $sheet->setCellValue("{$letra}2", $cabecera);
            $sheet->getStyle("{$letra}2")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '52B788']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);
        }

        // ── Datos ──
        foreach ($datos as $i => $item) {
            $fila = $i + 3;
            $sheet->setCellValue("A{$fila}", $i + 1);
            $sheet->setCellValue("B{$fila}", $item->nombre);
            $sheet->setCellValue("C{$fila}", ucfirst($item->estado->value));
            $sheet->setCellValue("D{$fila}", $item->trainingCenter?->nombre ?? '—');
            $sheet->setCellValue("E{$fila}", $item->created_at->format('d/m/Y'));

            // Alternar color de filas:
            if ($i % 2 === 0) {
                $sheet->getStyle("A{$fila}:E{$fila}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0FFF4']],
                ]);
            }
        }

        // ── Ajustar ancho de columnas ──
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $nombreArchivo = 'reporte-' . now()->format('Y-m-d') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $nombreArchivo, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
```

### Ruta y botón Blade
```php
// routes/web.php
Route::get('/admin/reportes/exportar', [NombreExcelController::class, 'exportar'])
     ->name('admin.reportes.exportar')
     ->middleware(['auth', 'verified', 'ensureUserIsActive']);
```

```blade
{{-- Botones de descarga --}}
<div class="flex gap-2">
    <flux:button
        href="{{ route('admin.reportes.pdf') }}"
        tag="a"
        variant="outline"
        icon="document">
        Descargar PDF
    </flux:button>

    <flux:button
        href="{{ route('admin.reportes.exportar') }}"
        tag="a"
        variant="outline"
        icon="table-cells">
        Exportar Excel
    </flux:button>
</div>
```

## Notas GIDESTH
- Siempre filtrar por `training_center_id` en los datos del reporte
- El super_admin ve todos los centros, los demás solo el suyo
- Usar el logo y colores del sistema: verde `#2D6A4F` para encabezados
- En PDF: usar `DejaVu Sans` para soporte de caracteres especiales (tildes, ñ)
- Los PDFs se generan en memoria — no guardar en disco a menos que se requiera
