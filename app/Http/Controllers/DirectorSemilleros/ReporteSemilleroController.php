<?php

namespace App\Http\Controllers\DirectorSemilleros;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReporteSemilleroController extends Controller
{
    public function index()
    {
        // Hay varios reportes, cualquiera de ellos permite ver el index
        // pero validamos si al menos tiene un permiso de reportes
        if (!Auth::user()->canAny([
            'reportes.semilleros_con_metricas',
            'reportes.aprendices_por_semillero',
            'reportes.proyectos_por_estado'
        ])) {
            abort(403, 'No tienes permisos para ver reportes.');
        }

        return view('director_semilleros.reportes.index');
    }

    public function exportar(Request $request)
    {
        $this->authorize('reportes.exportar_pdf_excel');

        $request->validate([
            'tipo_reporte' => 'required|string',
            'formato'      => 'required|in:pdf,excel',
        ]);

        // Simulación de exportación
        return redirect()->back()->with('success', 'Exportación de ' . $request->tipo_reporte . ' en formato ' . $request->formato . ' se ha encolado correctamente.');
    }
}
