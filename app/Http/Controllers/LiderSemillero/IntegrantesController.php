<?php

namespace App\Http\Controllers\LiderSemillero;

use App\Http\Controllers\Controller;
use App\Models\ProjectAuthor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class IntegrantesController extends Controller
{
    /**
     * Lista los integrantes del semillero que lidera el usuario.
     * Cada integrante se marca con "con proyecto" o "sin proyecto" según ProjectAuthor activo.
     */
    public function index(): View
    {
        $semillero = Auth::user()->ledSeedlings()->with(['members.person'])->first();

        $integrantes = collect();
        $projectIds = collect();

        if ($semillero) {
            $projectIds = DB::table('project_seedlings')
                ->where('seedling_id', $semillero->id)
                ->pluck('project_id');

            $userIdsConProyectoActivo = ProjectAuthor::whereIn('project_id', $projectIds)
                ->where('activo', true)
                ->pluck('user_id')
                ->unique()
                ->values();

            $integrantes = $semillero->members()
                ->with(['person', 'roles'])
                ->get()
                ->map(function ($user) use ($userIdsConProyectoActivo) {
                    $user->con_proyecto = $userIdsConProyectoActivo->contains($user->id);
                    $user->roles_texto = $user->roles
                        ->pluck('name')
                        ->map(fn ($roleName) => $this->formatearNombreRol((string) $roleName))
                        ->implode(', ');
                    if ($user->roles_texto === '') {
                        $user->roles_texto = 'Sin rol asignado';
                    }
                    return $user;
                });
        }

        return view('lider_semillero.integrantes.index', [
            'semillero'   => $semillero,
            'integrantes' => $integrantes,
        ]);
    }

    private function formatearNombreRol(string $roleName): string
    {
        $normalizado = str_replace('_', ' ', trim($roleName));
        return mb_convert_case($normalizado, MB_CASE_TITLE, 'UTF-8');
    }
}
