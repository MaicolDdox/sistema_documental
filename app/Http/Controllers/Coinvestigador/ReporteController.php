<?php

namespace App\Http\Controllers\Coinvestigador;

use App\Enums\TipoEvidenciaEnum;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class ReporteController extends Controller
{
    public function descargar()
    {
        $usuario = Auth::user();

        $vinculaciones = $usuario->projectAuthors()
            ->where('activo', true)
            ->with(['project.seedling', 'project.liderProyecto.person'])
            ->get();

        $filas = $vinculaciones->map(function ($va) {
            $proyecto = $va->project;
            $productosAprobados = $proyecto
                ? $proyecto->projectEvidences()
                    ->where('tipo', TipoEvidenciaEnum::ProductoFinal)
                    ->where('estado_revision_director', \App\Enums\EstadoRevisionEnum::Aprobado)
                    ->count()
                : 0;

            return [
                'proyecto' => $proyecto?->nombre ?? '—',
                'semillero' => $proyecto?->seedling?->nombre ?? '—',
                'lider_proyecto' => $proyecto?->liderProyecto?->person?->nombre_completo ?? $proyecto?->liderProyecto?->email ?? '—',
                'estado' => (string) ($proyecto?->estado?->value ?? '—'),
                'productos_aprobados' => $productosAprobados,
                'vinculado_desde' => $va->created_at?->format('d/m/Y') ?? '—',
            ];
        });

        $meta = [
            'usuario' => $usuario->person?->nombre_completo ?? $usuario->email,
            'generado_en' => now('America/Bogota')->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('co_investigador.reportes.pdf.mis_proyectos', [
            'filas' => $filas,
            'meta' => $meta,
        ]);

        return $pdf->download('reporte_mis_proyectos_'.now()->format('Y-m-d_H-i').'.pdf');
    }
}
