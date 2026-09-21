<?php

namespace App\Http\Controllers\CoinvestigadorSdi;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * co_investigador_sdi no gestiona nada propio — solo participa en proyectos
 * ajenos a los que fue vinculado por un lider_proyecto. Sin permisos Spatie
 * propios, el acceso lo controla únicamente el middleware
 * role:co_investigador_sdi, mismo patrón que lider_proyecto.
 */
class DashboardController extends Controller
{
    public function index(): View
    {
        $vinculaciones = Auth::user()->projectAuthors()
            ->where('activo', true)
            ->with(['project.seedling', 'project.liderProyecto.person'])
            ->get();

        return view('co_investigador_sdi.dashboard', compact('vinculaciones'));
    }
}
