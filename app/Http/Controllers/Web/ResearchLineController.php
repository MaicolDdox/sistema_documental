<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ResearchLine;
use App\Http\Requests\StoreResearchLineRequest;
use App\Http\Requests\UpdateResearchLineRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ResearchLineController extends Controller
{
    public function index(): View
    {
        //
    }

    public function create(): View
    {
        //
    }

    public function store(StoreResearchLineRequest $request): RedirectResponse
    {
        //
    }

    public function show(ResearchLine $researchLine): View
    {
        //
    }

    public function edit(ResearchLine $researchLine): View
    {
        //
    }

    public function update(UpdateResearchLineRequest $request, ResearchLine $researchLine): RedirectResponse
    {
        //
    }

    public function destroy(ResearchLine $researchLine): RedirectResponse
    {
        //
    }
}
