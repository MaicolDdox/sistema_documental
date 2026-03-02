<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ResearchGroup;
use App\Http\Requests\StoreResearchGroupRequest;
use App\Http\Requests\UpdateResearchGroupRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ResearchGroupController extends Controller
{
    public function index(): View
    {
        //
    }

    public function create(): View
    {
        //
    }

    public function store(StoreResearchGroupRequest $request): RedirectResponse
    {
        //
    }

    public function show(ResearchGroup $researchGroup): View
    {
        //
    }

    public function edit(ResearchGroup $researchGroup): View
    {
        //
    }

    public function update(UpdateResearchGroupRequest $request, ResearchGroup $researchGroup): RedirectResponse
    {
        //
    }

    public function destroy(ResearchGroup $researchGroup): RedirectResponse
    {
        //
    }
}
