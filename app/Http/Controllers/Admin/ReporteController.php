<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EstadoEnum;
use App\Http\Controllers\Controller;
use App\Models\Seedling;
use App\Models\User;
use App\Services\Reportes\ReporteExportService;
use App\Support\TrainingCenterAccess;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

    protected function exportarExcel(string $tipo, array $data): StreamedResponse
    {
        $meta = $data['meta'];
        $metaItems = [
            ['label' => 'Centro',       'value' => $meta['centro'] ?? '—'],
            ['label' => 'Generado por', 'value' => $meta['usuario'] ?? '—'],
            ['label' => 'Generado en',  'value' => $meta['generado_en'] ?? '—'],
        ];

        return app(ReporteExportService::class)->generarExcelSimple(
            titulo: 'Reporte Administrador del Sistema',
            subtitulo: $tipo,
            metaItems: $metaItems,
            columnHeaders: $this->columnasReporteExcel($tipo),
            filas: $data['filas'] ?? [],
            filename: $this->nombreArchivo($tipo, 'xlsx'),
        );
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
        $semilleros = Seedling::with(['leader.person', 'projects.projectEvidences'])
            ->when($centerId, fn ($q) => $q->where('training_center_id', $centerId))
            ->orderBy('nombre')
            ->get();

        $filas = $semilleros->map(function (Seedling $s) {
            $proyectosActivos = $s->projects
                ->where('estado', EstadoEnum::Activo)
                ->count();
            $productos = $s->projects
                ->flatMap(fn ($p) => $p->projectEvidences ?? collect())
                ->where('tipo', \App\Enums\TipoEvidenciaEnum::ProductoFinal)
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
