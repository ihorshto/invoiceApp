<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Models\Product;
use App\Modules\Products\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function __construct(private ProductService $service) {}

    public function index(Request $request): Response
    {
        $company  = $request->user()->company;
        $products = $this->service->list($company, $request->input('search'));

        return Inertia::render('Products/Index', [
            'products' => $products,
            'filters'  => $request->only('search'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Products/Create');
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $this->service->create($request->user()->company, $request->validated());
        return redirect()->route('products.index');
    }

    public function edit(Product $product): Response
    {
        return Inertia::render('Products/Edit', ['product' => $product]);
    }

    public function update(StoreProductRequest $request, Product $product): RedirectResponse
    {
        $this->service->update($product, $request->validated());
        return redirect()->route('products.index');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->service->delete($product);
        return redirect()->route('products.index');
    }
}
