<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        //
    }

    public function create(): View
    {
        //
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        //
    }

    public function show(Product $product): View
    {
        //
    }

    public function edit(Product $product): View
    {
        //
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        //
    }

    public function destroy(Product $product): RedirectResponse
    {
        //
    }
}
