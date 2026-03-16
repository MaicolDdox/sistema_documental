<?php

namespace App\Http\Controllers\AsesorSemillero;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Product;
use App\Models\Seedling;
use App\Models\ExternalAdvisor;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExportarReporteController extends Controller
{
    /** Obtiene los semilleros del asesor autenticado. */
    private function getSemillerosDelAsesor(): \Illuminate\Database\Eloquent\Collection
    {
        $advisor = ExternalAdvisor::where('user_id', Auth::id())->first();
        if (!$advisor) return collect();

        return Seedling::whereHas('seedlingAdvisors', function ($q) use ($advisor) {
            $q->where('external_advisor_id', $advisor->id)
              ->where('activo', true);
        })->get();
    }

    private function getAllProjectIdsDelAsesor(): \Illuminate\Support\Collection
    {
        $semilleroIds = $this->getSemillerosDelAsesor()->pluck('id');

        return DB::table('project_seedlings')
            ->whereIn('seedling_id', $semilleroIds)
            ->pluck('project_id')
            ->unique();
    }

    /** Helper para aplicar rango de fechas a un query por created_at */
    private function aplicarFechas($query, Request $request)
    {
        if ($request->filled('rango')) {
            switch ($request->rango) {
                case 'hoy':
                    $query->whereDate('created_at', now()->toDateString());
                    break;
                case 'semanal':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'mensual':
                    $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
                    break;
                case 'anual':
                    $query->whereYear('created_at', now()->year);
                    break;
            }
        }
        return $query;
    }

    public function dashboard(Request $request)
    {
        $semilleros = $this->getSemillerosDelAsesor();
        $semilleroIds = $semilleros->pluck('id');
        $projectIds = $this->getAllProjectIdsDelAsesor();

        // Proyectos en el rango
        $proyectosQuery = Project::with(['projectAuthors.user.person', 'products', 'projectModality', 'investigationType'])
                                 ->whereIn('id', $projectIds);
        $this->aplicarFechas($proyectosQuery, $request);
        $proyectos = $proyectosQuery->orderByDesc('created_at')->get();

        // Productos en el rango
        $productosQuery = Product::with(['project', 'productAuthors.projectAuthor.user.person'])
                                 ->whereIn('project_id', $projectIds);
        $this->aplicarFechas($productosQuery, $request);
        $productos = $productosQuery->orderByDesc('created_at')->get();

        // Aprendices vinculados a los semilleros en el rango
        $aprendicesQuery = User::with(['person.trainingProgram', 'person.entityPosition', 'person.linkageType'])
                               ->whereHas('seedlings', function($q) use ($semilleroIds) {
                                   $q->whereIn('seedlings.id', $semilleroIds);
                               })
                               ->where('id', '!=', Auth::id());
        $this->aplicarFechas($aprendicesQuery, $request);
        $aprendices = $aprendicesQuery->orderByDesc('created_at')->get();

        $pdf = Pdf::loadView('asesor_semillero.pdf.dashboard', compact(
            'proyectos', 'productos', 'aprendices', 'request'
        ))->setPaper('a4', 'landscape');
        $filename = 'reporte_general_' . now()->format('Ymd_Hi') . '.pdf';
        return $pdf->download($filename);
    }
    
    public function semilleros(Request $request)
    {
        $query = $this->getSemillerosDelAsesor()->toQuery()->with(['researchGroup', 'leader.person']);
        $this->aplicarFechas($query, $request);
        $semilleros = $query->get();

        $pdf = Pdf::loadView('asesor_semillero.pdf.semilleros', compact('semilleros', 'request'))
                  ->setPaper('a4', 'landscape');
        $filename = 'reporte_semilleros_' . now()->format('Ymd_Hi') . '.pdf';
        return $pdf->download($filename);
    }

    public function proyectos(Request $request)
    {
        $projectIds = $this->getAllProjectIdsDelAsesor();
        
        $query = Project::with(['projectAuthors.user.person', 'products', 'projectModality', 'investigationType'])
            ->whereIn('id', $projectIds);
        
        $this->aplicarFechas($query, $request);
        
        $proyectos = $query->orderByDesc('created_at')->get();

        $pdf = Pdf::loadView('asesor_semillero.pdf.proyectos', compact('proyectos', 'request'))
                  ->setPaper('a4', 'landscape');
        $filename = 'reporte_proyectos_' . now()->format('Ymd_Hi') . '.pdf';
        return $pdf->download($filename);
    }

    public function productos(Request $request)
    {
        $projectIds = $this->getAllProjectIdsDelAsesor();

        $query = Product::with(['project', 'productAuthors.projectAuthor.user.person'])
            ->whereIn('project_id', $projectIds);
        
        $this->aplicarFechas($query, $request);
        
        $productos = $query->orderByDesc('created_at')->get();

        $pdf = Pdf::loadView('asesor_semillero.pdf.productos', compact('productos', 'request'))
                  ->setPaper('a4', 'landscape');
        $filename = 'reporte_productos_' . now()->format('Ymd_Hi') . '.pdf';
        return $pdf->download($filename);
    }

    public function aprendices(Request $request)
    {
        $semilleros = $this->getSemillerosDelAsesor();
        $semilleroIds = $semilleros->pluck('id');

        $query = User::with(['person.trainingProgram', 'person.entityPosition', 'person.linkageType'])
            ->whereHas('seedlings', function($q) use ($semilleroIds) {
                $q->whereIn('seedlings.id', $semilleroIds);
            })
            ->where('id', '!=', Auth::id());

        $this->aplicarFechas($query, $request);
        
        $aprendices = $query->orderByDesc('created_at')->get();

        $pdf = Pdf::loadView('asesor_semillero.pdf.aprendices', compact('aprendices', 'request'))
                  ->setPaper('a4', 'landscape');
        $filename = 'reporte_aprendices_' . now()->format('Ymd_Hi') . '.pdf';
        return $pdf->download($filename);
    }
}
