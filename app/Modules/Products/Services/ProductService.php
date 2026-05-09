<?php

namespace App\Modules\Products\Services;

use App\Models\Company;
use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductService
{
    public function list(Company $company, ?string $search): LengthAwarePaginator
    {
        return Product::withoutGlobalScopes()
            ->where('company_id', $company->id)
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('is_active', 'desc')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();
    }

    public function create(Company $company, array $data): Product
    {
        return $company->products()->create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);
        return $product;
    }

    public function archive(Product $product): void
    {
        $product->update(['is_active' => false]);
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }
}
