<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        //
    }

    public function create(): View
    {
        //
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        //
    }

    public function show(Project $project): View
    {
        //
    }

    public function edit(Project $project): View
    {
        //
    }

    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        //
    }

    public function destroy(Project $project): RedirectResponse
    {
        //
    }
}
