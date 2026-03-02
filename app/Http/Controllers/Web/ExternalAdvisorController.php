<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ExternalAdvisor;
use App\Http\Requests\StoreExternalAdvisorRequest;
use App\Http\Requests\UpdateExternalAdvisorRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ExternalAdvisorController extends Controller
{
    public function index(): View
    {
        //
    }

    public function create(): View
    {
        //
    }

    public function store(StoreExternalAdvisorRequest $request): RedirectResponse
    {
        //
    }

    public function show(ExternalAdvisor $externalAdvisor): View
    {
        //
    }

    public function edit(ExternalAdvisor $externalAdvisor): View
    {
        //
    }

    public function update(UpdateExternalAdvisorRequest $request, ExternalAdvisor $externalAdvisor): RedirectResponse
    {
        //
    }

    public function destroy(ExternalAdvisor $externalAdvisor): RedirectResponse
    {
        //
    }
}
