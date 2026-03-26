<?php

namespace App\Http\Controllers\AsesorSemillero;

use App\Http\Controllers\Controller;
use App\Support\AsesorSemilleroContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SemilleroActivoController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'seedling_id' => ['required', 'integer'],
            'redirect_to' => ['nullable', 'string', 'max:500'],
        ]);

        AsesorSemilleroContext::setSemilleroActivo((int) $validated['seedling_id']);

        if (! empty($validated['redirect_to'])) {
            $redirect = (string) $validated['redirect_to'];
            if (str_starts_with($redirect, '/')) {
                return redirect($redirect);
            }
        }

        return redirect()->back();
    }
}
