<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Http\Requests\StoreCityRequest;
use App\Http\Requests\UpdateCityRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CityController extends Controller
{
    public function index(): View
    {
        //
    }

    public function create(): View
    {
        //
    }

    public function store(StoreCityRequest $request): RedirectResponse
    {
        //
    }

    public function show(City $city): View
    {
        //
    }

    public function edit(City $city): View
    {
        //
    }

    public function update(UpdateCityRequest $request, City $city): RedirectResponse
    {
        //
    }

    public function destroy(City $city): RedirectResponse
    {
        //
    }
}
