<?php

namespace App\Http\Controllers\LiderSemillero;

use App\Http\Controllers\Controller;
use App\Models\SeedlingAdvisor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AsesoresController extends Controller
{
    /**
     * Lista los asesores vinculados al semillero que lidera el usuario.
     * Usa la tabla pivot para mostrar estado activo/inactivo y permitir activar/desactivar.
     */
    public function index(): View
    {
        $semillero = Auth::user()->ledSeedlings()->first();

        $vinculos = $semillero
            ? $semillero->seedlingAdvisors()->with('externalAdvisor.user')->get()
            : collect();

        return view('lider_semillero.asesores.index', [
            'semillero' => $semillero,
            'vinculos'  => $vinculos,
        ]);
    }

    /**
     * Activa o desactiva un asesor del semillero (solo el líder del semillero).
     */
    public function toggle(Request $request, SeedlingAdvisor $vinculo): RedirectResponse
    {
        $this->authorize('asesores_externos.vincular_semillero');

        $semillero = Auth::user()->ledSeedlings()->first();

        if (!$semillero || $vinculo->seedling_id !== $semillero->id) {
            abort(403, 'No puedes modificar este asesor.');
        }

        $vinculo->activo = !($vinculo->activo ?? true);
        $vinculo->save();

        $estado = $vinculo->activo ? 'activado' : 'desactivado';
        return redirect()->route('lider-sem.asesores')
            ->with('success', "Asesor {$estado} correctamente.");
    }
}
