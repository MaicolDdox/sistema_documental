<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\TechnologicalLine;
use App\Http\Requests\StoreTechnologicalLineRequest;
use App\Http\Requests\UpdateTechnologicalLineRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TechnologicalLineController extends Controller
{
    public function index(): View
    {
        //
    }

    public function create(): View
    {
        //
    }

    public function store(StoreTechnologicalLineRequest $request): RedirectResponse
    {
        //
    }

    public function show(TechnologicalLine $technologicalLine): View
    {
        //
    }

    public function edit(TechnologicalLine $technologicalLine): View
    {
        //
    }

    public function update(UpdateTechnologicalLineRequest $request, TechnologicalLine $technologicalLine): RedirectResponse
    {
        //
    }

    public function destroy(TechnologicalLine $technologicalLine): RedirectResponse
    {
        //
    }
}
