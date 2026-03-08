<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Person;
use App\Http\Requests\StorePersonRequest;
use App\Http\Requests\UpdatePersonRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PersonController extends Controller
{
    public function index(): View
    {
        //
    }

    public function create(): View
    {
        //
    }

    public function store(StorePersonRequest $request): RedirectResponse
    {
        //
    }

    public function show(Person $person): View
    {
        //
    }

    public function edit(Person $person): View
    {
        //
    }

    public function update(UpdatePersonRequest $request, Person $person): RedirectResponse
    {
        //
    }

    public function destroy(Person $person): RedirectResponse
    {
        //
    }
}
