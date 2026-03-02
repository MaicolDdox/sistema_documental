<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\TrainingProgram;
use App\Http\Requests\StoreTrainingProgramRequest;
use App\Http\Requests\UpdateTrainingProgramRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TrainingProgramController extends Controller
{
    public function index(): View
    {
        //
    }

    public function create(): View
    {
        //
    }

    public function store(StoreTrainingProgramRequest $request): RedirectResponse
    {
        //
    }

    public function show(TrainingProgram $trainingProgram): View
    {
        //
    }

    public function edit(TrainingProgram $trainingProgram): View
    {
        //
    }

    public function update(UpdateTrainingProgramRequest $request, TrainingProgram $trainingProgram): RedirectResponse
    {
        //
    }

    public function destroy(TrainingProgram $trainingProgram): RedirectResponse
    {
        //
    }
}
