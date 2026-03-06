<?php

namespace App\Http\Controllers\LiderSemillero;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectAuthor;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AprendicesController extends Controller
{
    /**
     * Lista aprendices (integrantes del semillero) con su proyecto vinculado y acciones.
     */
    public function index(Request $request): View
    {
        $semillero = Auth::user()->ledSeedlings()->first();
        $aprendices = collect();
        $proyectosDelSemillero = collect();

        if ($semillero) {
            $projectIds = DB::table('project_seedlings')
                ->where('seedling_id', $semillero->id)
                ->pluck('project_id');

            $proyectosDelSemillero = Project::whereIn('id', $projectIds)->orderBy('nombre')->get(['id', 'nombre']);

            $query = $semillero->members()->with('person');
            if ($request->filled('documento')) {
                $doc = $request->input('documento');
                $query->where('numero_documento', 'like', '%' . $doc . '%');
            }
            $members = $query->get();

            $vinculosActivos = ProjectAuthor::with('project')
                ->whereIn('project_id', $projectIds)
                ->where('activo', true)
                ->get()
                ->groupBy('user_id');

            $aprendices = $members->map(function ($user) use ($vinculosActivos, $proyectosDelSemillero) {
                $vinculo = $vinculosActivos->get($user->id)?->first();
                $proyectoNombre = $vinculo && $vinculo->project
                    ? $vinculo->project->nombre
                    : 'Sin proyecto';
                $user->vinculo_activo = $vinculo;
                $user->proyecto_vinculado = $proyectoNombre;
                $user->tiene_vinculo = (bool) $vinculo;
                return $user;
            });
        }

        return view('lider_semillero.aprendices.index', [
            'semillero' => $semillero,
            'aprendices' => $aprendices,
            'proyectosDelSemillero' => $proyectosDelSemillero,
        ]);
    }

    /**
     * Desvincular: UPDATE activo -> false en project_authors. Nunca DELETE.
     * El líder de semillero solo puede visualizar; vinculación/desvinculación la gestiona el asesor.
     */
    public function desvincular(ProjectAuthor $projectAuthor): RedirectResponse
    {
        abort(403, 'El Líder de Semillero solo puede visualizar aprendices. La vinculación y desvinculación las gestiona el asesor.');
        $semillero = Auth::user()->ledSeedlings()->first();
        if (!$semillero) {
            return redirect()->route('lider-sem.aprendices')->with('error', 'Sin semillero asignado.');
        }
        $projectIds = DB::table('project_seedlings')->where('seedling_id', $semillero->id)->pluck('project_id');
        if (!in_array($projectAuthor->project_id, $projectIds->toArray())) {
            abort(403, 'No puedes desvincular de un proyecto que no es de tu semillero.');
        }

        $projectAuthor->activo = false;
        $projectAuthor->save();

        return redirect()->route('lider-sem.aprendices')->with('success', 'Aprendiz desvinculado del proyecto.');
    }

    /**
     * Vincular aprendiz a un proyecto del semillero (crear o activar project_author).
     * El líder de semillero solo puede visualizar; esta acción la gestiona el asesor.
     */
    public function vincular(Request $request): RedirectResponse
    {
        abort(403, 'El Líder de Semillero solo puede visualizar aprendices. La vinculación la gestiona el asesor.');
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'project_id' => 'required|exists:projects,id',
        ]);

        $semillero = Auth::user()->ledSeedlings()->first();
        if (!$semillero) {
            return redirect()->route('lider-sem.aprendices')->with('error', 'Sin semillero asignado.');
        }
        $projectIds = DB::table('project_seedlings')->where('seedling_id', $semillero->id)->pluck('project_id');
        $memberIds = $semillero->members()->pluck('users.id');

        if (!in_array((int) $request->project_id, $projectIds->toArray())) {
            return redirect()->route('lider-sem.aprendices')->with('error', 'El proyecto no pertenece a tu semillero.');
        }
        if (!$memberIds->contains((int) $request->user_id)) {
            return redirect()->route('lider-sem.aprendices')->with('error', 'El usuario no es integrante de tu semillero.');
        }

        $pa = ProjectAuthor::firstOrCreate(
            [
                'project_id' => $request->project_id,
                'user_id' => $request->user_id,
            ],
            ['activo' => true]
        );
        $pa->activo = true;
        $pa->save();

        return redirect()->route('lider-sem.aprendices')->with('success', 'Aprendiz vinculado al proyecto.');
    }

    /**
     * Registrar aprendiz: agregar usuario existente (por documento) al semillero.
     * El registro de aprendices lo realiza el asesor, no el líder de semillero.
     */
    public function registrar(Request $request): RedirectResponse
    {
        abort(403, 'El registro de aprendices lo realiza el asesor. El Líder de Semillero solo puede visualizar.');
        $request->validate([
            'numero_documento' => 'required|string|max:50',
        ]);

        $semillero = Auth::user()->ledSeedlings()->first();
        if (!$semillero) {
            return redirect()->route('lider-sem.aprendices')->with('error', 'Sin semillero asignado.');
        }

        $user = User::where('numero_documento', $request->numero_documento)->first();
        if (!$user) {
            return redirect()->route('lider-sem.aprendices')
                ->with('error', 'No se encontró un usuario con ese número de documento.')
                ->withInput();
        }

        if ($semillero->members()->where('users.id', $user->id)->exists()) {
            return redirect()->route('lider-sem.aprendices')->with('error', 'Ese usuario ya es integrante del semillero.');
        }

        $semillero->members()->attach($user->id);

        return redirect()->route('lider-sem.aprendices')->with('success', 'Aprendiz registrado en el semillero.');
    }
}
