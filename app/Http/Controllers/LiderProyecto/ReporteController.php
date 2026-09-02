<?php

namespace App\Http\Controllers\LiderProyecto;

use App\Enums\TipoEvidenciaEnum;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

/**
 * Reporte descargable del único proyecto que gestiona el Líder de Proyecto
 * (rol sin permisos Spatie propios — el acceso lo controla role:lider_proyecto,
 * mismo patrón usado en el resto de este módulo).
 */
class ReporteController extends Controller
{
    use LiderProyectoContext;

    public function descargar()
    {
        $proyecto = $this->miProyecto()->load(['seedling', 'liderProyecto.person', 'researchLine']);

        $desarrollo = $proyecto->projectEvidences()->where('tipo', TipoEvidenciaEnum::Desarrollo)->get();
        $productoFinal = $proyecto->projectEvidences()->where('tipo', TipoEvidenciaEnum::ProductoFinal)->get();
        $aprendices = $proyecto->learners()->with('trainingProgram')->get();
        $coinvestigadores = $proyecto->authors()->wherePivot('activo', true)->with('person')->get();

        $usuario = Auth::user();
        $meta = [
            'generado_en' => now('America/Bogota')->format('d/m/Y H:i'),
            'usuario' => $usuario->person?->nombre_completo ?? $usuario->email,
        ];

        $pdf = Pdf::loadView('lider_proyecto.reportes.pdf.mi_proyecto', compact(
            'proyecto',
            'desarrollo',
            'productoFinal',
            'aprendices',
            'coinvestigadores',
            'meta',
        ));

        $nombre = 'reporte_'.str_replace(' ', '_', $proyecto->nombre).'_'.now()->format('Y-m-d_H-i').'.pdf';

        return $pdf->download($nombre);
    }
}
