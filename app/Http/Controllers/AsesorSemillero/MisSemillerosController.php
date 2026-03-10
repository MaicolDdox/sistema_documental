<?php

namespace App\Http\Controllers\AsesorSemillero;

use App\Http\Controllers\Controller;
use App\Models\ExternalAdvisor;
use App\Models\Project;
use App\Models\Seedling;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MisSemillerosController extends Controller
{
    public function index(): View
    {
        $advisor = ExternalAdvisor::where('user_id', Auth::id())->first();

        $semilleros = collect();

        if ($advisor) {
            // Carga semilleros donde el asesor está activo + sus proyectos
            $semilleros = Seedling::whereHas('seedlingAdvisors', function ($q) use ($advisor) {
                $q->where('external_advisor_id', $advisor->id)
                  ->where('activo', true);
            })
            ->with([
                'leader.person',
                'researchGroup',
                'projects' => function ($q) {
                    $q->with(['projectAuthors.user.person'])->orderBy('nombre');
                },
            ])
            ->get();
        }

        return view('asesor_semillero.mis_semilleros.index', compact('semilleros', 'advisor'));
    }
}
