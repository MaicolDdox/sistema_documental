<?php

namespace App\Http\Controllers\LiderSemillero;

use App\Http\Controllers\Controller;
use App\Models\Seedling;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class InfoSemilleroController extends Controller
{
    /**
     * Muestra el formulario de edición limitada (nombre, logo, descripción).
     * Solo para el semillero que lidera el usuario. Si no tiene, se muestra mensaje en la misma página.
     */
    public function edit(): View
    {
        $semillero = Auth::user()->ledSeedlings()->with('researchGroup')->first();

        return view('lider_semillero.info_semillero', compact('semillero'));
    }

    /**
     * Actualiza solo nombre, logo y descripción (el resto lo gestiona el Director).
     */
    public function update(Request $request): RedirectResponse
    {
        $semillero = Auth::user()->ledSeedlings()->first();

        if (!$semillero) {
            return redirect()->route('lider-sem.dashboard')
                ->with('error', 'No tienes un semillero asignado como líder.');
        }

        $validated = $request->validate([
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:2000',
            'logo'        => 'nullable|image|mimes:jpeg,png,gif,webp|max:2048',
        ]);

        $semillero->nombre = $validated['nombre'];
        $semillero->descripccion = $validated['descripcion'] ?? $semillero->descripccion;

        if ($request->hasFile('logo')) {
            if ($semillero->logo) {
                Storage::disk('public')->delete($semillero->logo);
            }
            $path = $request->file('logo')->store('semilleros/logos', 'public');
            $semillero->logo = $path;
        }

        $semillero->save();

        return redirect()->route('lider-sem.info-semillero')
            ->with('success', 'Información del semillero actualizada correctamente.');
    }
}
