<?php

namespace App\Http\Controllers\LiderSemillero;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InfoSemilleroController extends Controller
{
    /**
     * Muestra solo la información del semillero (modo lectura).
     */
    public function edit(): View
    {
        $semillero = Auth::user()->ledSeedlings()->with('researchGroup')->first();

        return view('lider_semillero.info_semillero', compact('semillero'));
    }

    /**
     * El líder no puede modificar la información del semillero.
     */
    public function update(Request $request): RedirectResponse
    {
        return redirect()->route('lider-sem.info-semillero')
            ->with('warning', 'La información del semillero solo puede ser modificada por el Director de Semilleros.');
    }
}
