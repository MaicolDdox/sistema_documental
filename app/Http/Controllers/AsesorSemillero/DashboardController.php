<?php

namespace App\Http\Controllers\AsesorSemillero;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Seedling;
use App\Models\Project;
use App\Models\Product;
use App\Models\ProjectAuthor;
use App\Support\AsesorSemilleroContext;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Permiso: dashboard (general access for asesor_semillero/lider_semillero)
     */
    public function index(): View
    {
        $semilleros = AsesorSemilleroContext::semillerosDelUsuarioAutenticado();
        $totalSemilleros = $semilleros->count();

        // Proyectos de todos los semilleros (unique() evita contar doble un proyecto que pertenezca a varios semilleros)
        $allProjectIds = DB::table('project_seedlings')
            ->whereIn('seedling_id', $semilleros->pluck('id'))
            ->pluck('project_id')
            ->unique();

        $totalProyectos = $allProjectIds->count();

        // Aprendices (autores activos en esos proyectos, excluyendo al asesor)
        $totalAprendices = ProjectAuthor::whereIn('project_id', $allProjectIds)
            ->where('activo', true)
            ->where('user_id', '!=', auth()->id())
            ->distinct('user_id')
            ->count('user_id');

        // Productos
        $productos = Product::whereIn('project_id', $allProjectIds)->get();
        $totalProductos     = $productos->count();
        $pendienteCount     = $productos->where('estado_revision', 'pendiente')->count();
        $aprobadoCount      = $productos->where('estado_revision', 'aprobado')->count();
        $rechazadoCount     = $productos->where('estado_revision', 'rechazado')->count();

        // Productos rechazados recientes
        $productosRechazados = Product::whereIn('project_id', $allProjectIds)
            ->where('estado_revision', 'rechazado')
            ->with('project')
            ->latest()
            ->limit(5)
            ->get();

        // Proyectos sin aprendices autores
        $proyectosSinIntegrantes = Project::whereIn('id', $allProjectIds)
            ->whereDoesntHave('projectAuthors', function ($q) {
                $q->where('activo', true)->where('user_id', '!=', auth()->id());
            })->get(['id','nombre']);

        // Semillero principal (el primero)
        $semillero = $semilleros->first();

        return view('asesor_semillero.dashboard', compact(
            'semilleros',
            'totalSemilleros',
            'totalProyectos',
            'totalAprendices',
            'totalProductos',
            'pendienteCount',
            'aprobadoCount',
            'rechazadoCount',
            'productosRechazados',
            'proyectosSinIntegrantes',
            'semillero'
        ));
    }
}
