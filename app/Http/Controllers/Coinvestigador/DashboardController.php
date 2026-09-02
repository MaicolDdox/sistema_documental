<?php

namespace App\Http\Controllers\Coinvestigador;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * co_investigador no gestiona nada propio — solo participa en proyectos
 * ajenos a los que fue vinculado por un administrador_sistema. Sin permisos
 * Spatie propios (rol nuevo del rediseño), el acceso lo controla únicamente
 * el middleware role:co_investigador, mismo patrón que lider_proyecto.
 */
class DashboardController extends Controller
{
    public function index(): View
    {
        $vinculaciones = Auth::user()->projectAuthors()
            ->where('activo', true)
            ->with(['project.seedling', 'project.liderProyecto.person'])
            ->get();

        return view('co_investigador.dashboard', compact('vinculaciones'));
    }
}
