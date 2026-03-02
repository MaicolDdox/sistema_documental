<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ThematicArea;
use App\Http\Requests\StoreThematicAreaRequest;
use App\Http\Requests\UpdateThematicAreaRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ThematicAreaController extends Controller
{
    public function index(): View
    {
        //
    }

    public function create(): View
    {
        //
    }

    public function store(StoreThematicAreaRequest $request): RedirectResponse
    {
        //
    }

    public function show(ThematicArea $thematicArea): View
    {
        //
    }

    public function edit(ThematicArea $thematicArea): View
    {
        //
    }

    public function update(UpdateThematicAreaRequest $request, ThematicArea $thematicArea): RedirectResponse
    {
        //
    }

    public function destroy(ThematicArea $thematicArea): RedirectResponse
    {
        //
    }
}
