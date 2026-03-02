<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\GroupProduct;
use App\Http\Requests\StoreGroupProductRequest;
use App\Http\Requests\UpdateGroupProductRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GroupProductController extends Controller
{
    public function index(): View
    {
        //
    }

    public function create(): View
    {
        //
    }

    public function store(StoreGroupProductRequest $request): RedirectResponse
    {
        //
    }

    public function show(GroupProduct $groupProduct): View
    {
        //
    }

    public function edit(GroupProduct $groupProduct): View
    {
        //
    }

    public function update(UpdateGroupProductRequest $request, GroupProduct $groupProduct): RedirectResponse
    {
        //
    }

    public function destroy(GroupProduct $groupProduct): RedirectResponse
    {
        //
    }
}
