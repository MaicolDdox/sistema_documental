<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Seedling;
use App\Http\Requests\StoreSeedlingRequest;
use App\Http\Requests\UpdateSeedlingRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SeedlingController extends Controller
{
    public function index(): View
    {
        //
    }

    public function create(): View
    {
        //
    }

    public function store(StoreSeedlingRequest $request): RedirectResponse
    {
        //
    }

    public function show(Seedling $seedling): View
    {
        //
    }

    public function edit(Seedling $seedling): View
    {
        //
    }

    public function update(UpdateSeedlingRequest $request, Seedling $seedling): RedirectResponse
    {
        //
    }

    public function destroy(Seedling $seedling): RedirectResponse
    {
        //
    }
}
