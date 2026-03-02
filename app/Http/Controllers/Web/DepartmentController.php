<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(): View
    {
        //
    }

    public function create(): View
    {
        //
    }

    public function store(StoreDepartmentRequest $request): RedirectResponse
    {
        //
    }

    public function show(Department $department): View
    {
        //
    }

    public function edit(Department $department): View
    {
        //
    }

    public function update(UpdateDepartmentRequest $request, Department $department): RedirectResponse
    {
        //
    }

    public function destroy(Department $department): RedirectResponse
    {
        //
    }
}
