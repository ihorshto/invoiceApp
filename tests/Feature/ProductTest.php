<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    private function userWithCompany(): array
    {
        $user    = User::factory()->create();
        $company = Company::factory()->create(['user_id' => $user->id]);
        return [$user, $company];
    }

    public function test_index_is_accessible(): void
    {
        [$user] = $this->userWithCompany();

        $this->actingAs($user)
            ->get(route('products.index'))
            ->assertStatus(200)
            ->assertInertia(fn ($p) => $p->component('Products/Index'));
    }

    public function test_product_can_be_created(): void
    {
        [$user, $company] = $this->userWithCompany();

        $this->actingAs($user)
            ->post(route('products.store'), [
                'name'       => 'Consulting',
                'unit_price' => '150.00',
                'unit'       => 'heure',
                'vat_rate'   => '20.00',
                'is_active'  => true,
            ])
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', ['name' => 'Consulting', 'company_id' => $company->id]);
    }

    public function test_product_can_be_updated(): void
    {
        [$user, $company] = $this->userWithCompany();
        $product = Product::factory()->create(['company_id' => $company->id, 'name' => 'Old']);

        $this->actingAs($user)
            ->put(route('products.update', $product), [
                'name'       => 'New Name',
                'unit_price' => $product->unit_price,
                'unit'       => $product->unit,
                'vat_rate'   => $product->vat_rate,
                'is_active'  => $product->is_active,
            ])
            ->assertRedirect(route('products.index'));

        $this->assertEquals('New Name', $product->fresh()->name);
    }

    public function test_product_can_be_deleted(): void
    {
        [$user, $company] = $this->userWithCompany();
        $product = Product::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user)
            ->delete(route('products.destroy', $product))
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_search_filters_by_name(): void
    {
        [$user, $company] = $this->userWithCompany();
        Product::factory()->create(['company_id' => $company->id, 'name' => 'AlphaService']);
        Product::factory()->create(['company_id' => $company->id, 'name' => 'BetaService']);

        $this->actingAs($user)
            ->get(route('products.index', ['search' => 'Alpha']))
            ->assertInertia(fn ($p) => $p
                ->component('Products/Index')
                ->where('products.total', 1)
            );
    }

    public function test_required_fields_validation(): void
    {
        [$user] = $this->userWithCompany();

        $this->actingAs($user)
            ->post(route('products.store'), [])
            ->assertSessionHasErrors(['name', 'unit_price', 'unit', 'vat_rate']);
    }
}
