<?php

namespace App\Http\Controllers\LiderSemillero;

use App\Enums\TipoEvidenciaEnum;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectLearner;
use App\Services\Reportes\ReporteExportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

    protected function exportarExcel(string $tipo, array $data): StreamedResponse
    {
        $meta = $data['meta'];
        $metaItems = [
            ['label' => 'Semillero',    'value' => $meta['semillero'] ?? '—'],
            ['label' => 'Generado por', 'value' => $meta['usuario'] ?? '—'],
            ['label' => 'Generado en',  'value' => $meta['generado_en'] ?? '—'],
        ];

        return app(ReporteExportService::class)->generarExcelSimple(
            titulo: 'Reporte Líder de Semillero',
            subtitulo: $meta['tipo'] ?? '—',
            metaItems: $metaItems,
            columnHeaders: $this->columnasReporteExcel($tipo),
            filas: $data['filas'] ?? [],
            filename: $this->nombreArchivo($tipo, 'xlsx'),
        );
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
