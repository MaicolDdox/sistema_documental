<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\TrainingCenter;
use App\Http\Requests\StoreTrainingCenterRequest;
use App\Http\Requests\UpdateTrainingCenterRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TrainingCenterController extends Controller
{
    public function index(): View
    {
        //
    }

    public function create(): View
    {
        //
    }

    public function store(StoreTrainingCenterRequest $request): RedirectResponse
    {
        //
    }

    public function show(TrainingCenter $trainingCenter): View
    {
        //
    }

    public function edit(TrainingCenter $trainingCenter): View
    {
        //
    }

    public function update(UpdateTrainingCenterRequest $request, TrainingCenter $trainingCenter): RedirectResponse
    {
        //
    }

    public function destroy(TrainingCenter $trainingCenter): RedirectResponse
    {
        //
    }
}
