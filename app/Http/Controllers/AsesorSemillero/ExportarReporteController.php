<?php

namespace App\Http\Controllers\AsesorSemillero;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Product;
use App\Models\Seedling;
use App\Models\ExternalAdvisor;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
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

        return Seedling::with(['researchGroup', 'leader.person', 'members.person'])
            ->whereHas('seedlingAdvisors', function ($q) use ($advisor) {
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

    /**
     * Aplica filtros de fecha a un query según el tipo de rango seleccionado.
     * Soporta: hoy, semanal (semana+año), mensual (mes+año), anual (año), personalizado (desde-hasta)
     */
    private function aplicarFechas($query, Request $request)
    {
        $rango = $request->get('rango', '');

        switch ($rango) {
            case 'hoy':
                $query->whereDate('created_at', Carbon::today());
                break;

            case 'semanal':
                $anio   = (int) $request->get('anio', Carbon::now()->year);
                $semana = (int) $request->get('semana', Carbon::now()->weekOfYear);
                $inicio = Carbon::now()->setISODate($anio, $semana)->startOfWeek();
                $fin    = $inicio->copy()->endOfWeek();
                $query->whereBetween('created_at', [$inicio, $fin]);
                break;

            case 'mensual':
                $anio = (int) $request->get('anio', Carbon::now()->year);
                $mes  = (int) $request->get('mes', Carbon::now()->month);
                $query->whereYear('created_at', $anio)->whereMonth('created_at', $mes);
                break;

            case 'anual':
                $anio = (int) $request->get('anio', Carbon::now()->year);
                $query->whereYear('created_at', $anio);
                break;

            case 'personalizado':
                $desde = $request->get('fecha_desde');
                $hasta = $request->get('fecha_hasta');
                if ($desde) $query->whereDate('created_at', '>=', $desde);
                if ($hasta) $query->whereDate('created_at', '<=', $hasta);
                break;

            default:
                // Sin filtro: todo el histórico
                break;
        }

        return $query;
    }

    /**
     * Construye un string legible del rango aplicado para el PDF.
     */
    private function describeRango(Request $request): string
    {
        $rango = $request->get('rango', '');
        switch ($rango) {
            case 'hoy':
                return 'Hoy – ' . Carbon::today()->format('d/m/Y');
            case 'semanal':
                $anio   = (int) $request->get('anio', Carbon::now()->year);
                $semana = (int) $request->get('semana', Carbon::now()->weekOfYear);
                $inicio = Carbon::now()->setISODate($anio, $semana)->startOfWeek();
                $fin    = $inicio->copy()->endOfWeek();
                return "Semana {$semana} de {$anio} ({$inicio->format('d/m')} – {$fin->format('d/m/Y')})";
            case 'mensual':
                $anio = (int) $request->get('anio', Carbon::now()->year);
                $mes  = (int) $request->get('mes', Carbon::now()->month);
                return Carbon::createFromDate($anio, $mes, 1)->translatedFormat('F Y');
            case 'anual':
                return 'Año ' . $request->get('anio', Carbon::now()->year);
            case 'personalizado':
                $desde = $request->get('fecha_desde') ? Carbon::parse($request->get('fecha_desde'))->format('d/m/Y') : '—';
                $hasta = $request->get('fecha_hasta') ? Carbon::parse($request->get('fecha_hasta'))->format('d/m/Y') : 'hoy';
                return "Del {$desde} al {$hasta}";
            default:
                return 'Todo el histórico';
        }
    }

    public function dashboard(Request $request)
    {
        $semilleros   = $this->getSemillerosDelAsesor();
        $semilleroIds = $semilleros->pluck('id');
        $projectIds   = $this->getAllProjectIdsDelAsesor();
        $rangoLabel   = $this->describeRango($request);

        // Proyectos
        $proyectosQuery = Project::with([
            'projectAuthors.user.person',
            'products',
            'projectModality',
            'investigationType',
            'researchLine',
            'technologicalLine',
            'thematicArea',
            'macroProject',
            'seedlings',
        ])->whereIn('id', $projectIds);
        $this->aplicarFechas($proyectosQuery, $request);
        $proyectos = $proyectosQuery->orderByDesc('created_at')->get();

        // Productos
        $productosQuery = Product::with([
            'project',
            'productAuthors.projectAuthor.user.person',
        ])->whereIn('project_id', $projectIds);
        $this->aplicarFechas($productosQuery, $request);
        $productos = $productosQuery->orderByDesc('created_at')->get();

        // Aprendices: miembros de los semilleros (no el asesor)
        $aprendicesQuery = User::with([
            'person.trainingProgram',
            'person.entityPosition',
            'person.linkageType',
            'seedlings' => fn($q) => $q->whereIn('seedlings.id', $semilleroIds),
        ])
        ->whereHas('seedlings', fn($q) => $q->whereIn('seedlings.id', $semilleroIds))
        ->where('id', '!=', Auth::id());
        $this->aplicarFechas($aprendicesQuery, $request);
        $aprendices = $aprendicesQuery->orderByDesc('created_at')->get();

        // Semilleros con sus miembros y conteos
        $semillerosConDatos = $semilleros->map(function ($sem) use ($projectIds) {
            $sem->proyectosActivos  = DB::table('project_seedlings')
                ->where('seedling_id', $sem->id)
                ->join('projects', 'projects.id', '=', 'project_seedlings.project_id')
                ->where('projects.estado', 'activo')
                ->count();
            $sem->proyectosInactivos = DB::table('project_seedlings')
                ->where('seedling_id', $sem->id)
                ->join('projects', 'projects.id', '=', 'project_seedlings.project_id')
                ->where('projects.estado', 'inactivo')
                ->count();
            $sem->totalMiembros = $sem->members()->count();
            return $sem;
        });

        // Estadísticas generales
        $stats = [
            'totalProyectos'          => $proyectos->count(),
            'proyectosActivos'        => $proyectos->where('estado.value', 'activo')->count(),
            'proyectosInactivos'      => $proyectos->where('estado.value', 'inactivo')->count(),
            'totalProductos'          => $productos->count(),
            'productosPendientes'     => $productos->where('estado_revision', 'pendiente')->count(),
            'productosAprobados'      => $productos->where('estado_revision', 'aprobado')->count(),
            'productosRechazados'     => $productos->where('estado_revision', 'rechazado')->count(),
            'totalAprendices'         => $aprendices->count(),
            'totalSemilleros'         => $semilleros->count(),
        ];

        $pdf = Pdf::loadView('asesor_semillero.pdf.dashboard', [
            'proyectos' => $proyectos,
            'productos' => $productos,
            'aprendices' => $aprendices,
            'semilleros' => $semillerosConDatos,
            'stats' => $stats,
            'rangoLabel' => $rangoLabel
        ])->setPaper('a4', 'landscape');

        $filename = 'reporte_general_' . now()->format('Ymd_Hi') . '.pdf';
        return $pdf->download($filename);
    }

    public function semilleros(Request $request)
    {
        $semilleros = $this->getSemillerosDelAsesor();
        $rangoLabel = $this->describeRango($request);

        $semilleros = $semilleros->map(function ($sem) {
            $semQuery = DB::table('project_seedlings')
                ->where('seedling_id', $sem->id);

            $sem->proyectosActivos   = (clone $semQuery)->join('projects', 'projects.id', '=', 'project_seedlings.project_id')
                ->where('projects.estado', 'activo')->count();
            $sem->proyectosInactivos = (clone $semQuery)->join('projects', 'projects.id', '=', 'project_seedlings.project_id')
                ->where('projects.estado', 'inactivo')->count();
            $sem->totalMiembros = $sem->members()->count();
            $sem->miembros = $sem->members()->with('person')->get();
            return $sem;
        });

        $pdf = Pdf::loadView('asesor_semillero.pdf.semilleros', compact('semilleros', 'rangoLabel'))
                  ->setPaper('a4', 'landscape');
        $filename = 'reporte_semilleros_' . now()->format('Ymd_Hi') . '.pdf';
        return $pdf->download($filename);
    }

    public function proyectos(Request $request)
    {
        $projectIds = $this->getAllProjectIdsDelAsesor();
        $rangoLabel = $this->describeRango($request);

        $query = Project::with([
            'projectAuthors.user.person',
            'products',
            'projectModality',
            'investigationType',
            'researchLine',
            'technologicalLine',
            'thematicArea',
            'macroProject',
            'seedlings',
        ])->whereIn('id', $projectIds);

        $this->aplicarFechas($query, $request);
        $proyectos = $query->orderByDesc('created_at')->get();

        $pdf = Pdf::loadView('asesor_semillero.pdf.proyectos', compact('proyectos', 'rangoLabel'))
                  ->setPaper('a4', 'landscape');
        $filename = 'reporte_proyectos_' . now()->format('Ymd_Hi') . '.pdf';
        return $pdf->download($filename);
    }

    public function productos(Request $request)
    {
        $projectIds = $this->getAllProjectIdsDelAsesor();
        $rangoLabel = $this->describeRango($request);

        $query = Product::with([
            'project',
            'productAuthors.projectAuthor.user.person',
        ])->whereIn('project_id', $projectIds);

        $this->aplicarFechas($query, $request);
        $productos = $query->orderByDesc('created_at')->get();

        $pdf = Pdf::loadView('asesor_semillero.pdf.productos', compact('productos', 'rangoLabel'))
                  ->setPaper('a4', 'landscape');
        $filename = 'reporte_productos_' . now()->format('Ymd_Hi') . '.pdf';
        return $pdf->download($filename);
    }

    public function aprendices(Request $request)
    {
        $semilleros   = $this->getSemillerosDelAsesor();
        $semilleroIds = $semilleros->pluck('id');
        $rangoLabel   = $this->describeRango($request);

        $query = User::with([
            'person.trainingProgram',
            'person.entityPosition',
            'person.linkageType',
            'seedlings' => fn($q) => $q->whereIn('seedlings.id', $semilleroIds),
        ])
        ->whereHas('seedlings', fn($q) => $q->whereIn('seedlings.id', $semilleroIds))
        ->where('id', '!=', Auth::id());

        $this->aplicarFechas($query, $request);
        $aprendices = $query->orderByDesc('created_at')->get();

        $pdf = Pdf::loadView('asesor_semillero.pdf.aprendices', compact('aprendices', 'rangoLabel'))
                  ->setPaper('a4', 'landscape');
        $filename = 'reporte_aprendices_' . now()->format('Ymd_Hi') . '.pdf';
        return $pdf->download($filename);
    }
}
