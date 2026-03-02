<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\EntityPosition;
use App\Http\Requests\StoreEntityPositionRequest;
use App\Http\Requests\UpdateEntityPositionRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EntityPositionController extends Controller
{
    public function index(): View
    {
        //
    }

    public function create(): View
    {
        //
    }

    public function store(StoreEntityPositionRequest $request): RedirectResponse
    {
        //
    }

    public function show(EntityPosition $entityPosition): View
    {
        //
    }

    public function edit(EntityPosition $entityPosition): View
    {
        //
    }

    public function update(UpdateEntityPositionRequest $request, EntityPosition $entityPosition): RedirectResponse
    {
        //
    }

    public function destroy(EntityPosition $entityPosition): RedirectResponse
    {
        //
    }
}
