<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\LinkageType;
use App\Http\Requests\StoreLinkageTypeRequest;
use App\Http\Requests\UpdateLinkageTypeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LinkageTypeController extends Controller
{
    public function index(): View
    {
        //
    }

    public function create(): View
    {
        //
    }

    public function store(StoreLinkageTypeRequest $request): RedirectResponse
    {
        //
    }

    public function show(LinkageType $linkageType): View
    {
        //
    }

    public function edit(LinkageType $linkageType): View
    {
        //
    }

    public function update(UpdateLinkageTypeRequest $request, LinkageType $linkageType): RedirectResponse
    {
        //
    }

    public function destroy(LinkageType $linkageType): RedirectResponse
    {
        //
    }
}
