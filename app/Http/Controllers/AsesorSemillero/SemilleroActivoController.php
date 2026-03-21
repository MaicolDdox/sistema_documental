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
        ]);

        AsesorSemilleroContext::setSemilleroActivo((int) $validated['seedling_id']);

        return redirect()->back();
    }
}
