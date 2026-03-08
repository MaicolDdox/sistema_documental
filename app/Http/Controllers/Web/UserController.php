<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        //
    }

    public function create(): View
    {
        //
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        //
    }

    public function show(User $user): View
    {
        //
    }

    public function edit(User $user): View
    {
        //
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        //
    }

    public function destroy(User $user): RedirectResponse
    {
        //
    }
}
