<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientTest extends TestCase
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
            ->get(route('clients.index'))
            ->assertStatus(200)
            ->assertInertia(fn ($p) => $p->component('Clients/Index'));
    }

    public function test_client_can_be_created(): void
    {
        [$user, $company] = $this->userWithCompany();

        $this->actingAs($user)
            ->post(route('clients.store'), [
                'name'        => 'ACME',
                'email'       => 'acme@example.com',
                'address'     => '123 rue',
                'postal_code' => '75001',
                'city'        => 'Paris',
                'country'     => 'France',
            ])
            ->assertRedirect(route('clients.index'));

        $this->assertDatabaseHas('clients', ['name' => 'ACME', 'company_id' => $company->id]);
    }

    public function test_client_can_be_updated(): void
    {
        [$user, $company] = $this->userWithCompany();
        $client = Client::factory()->create(['company_id' => $company->id, 'name' => 'Old']);

        $this->actingAs($user)
            ->put(route('clients.update', $client), [
                'name'        => 'New Name',
                'email'       => $client->email,
                'address'     => $client->address,
                'postal_code' => $client->postal_code,
                'city'        => $client->city,
                'country'     => $client->country,
            ])
            ->assertRedirect(route('clients.index'));

        $this->assertEquals('New Name', $client->fresh()->name);
    }

    public function test_client_without_invoices_can_be_deleted(): void
    {
        [$user, $company] = $this->userWithCompany();
        $client = Client::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user)
            ->delete(route('clients.destroy', $client))
            ->assertRedirect(route('clients.index'));

        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
    }

    public function test_search_filters_by_name(): void
    {
        [$user, $company] = $this->userWithCompany();
        Client::factory()->create(['company_id' => $company->id, 'name' => 'AlphaClient']);
        Client::factory()->create(['company_id' => $company->id, 'name' => 'BetaClient']);

        $this->actingAs($user)
            ->get(route('clients.index', ['search' => 'Alpha']))
            ->assertInertia(fn ($p) => $p
                ->component('Clients/Index')
                ->where('clients.total', 1)
            );
    }

    public function test_required_fields_validation(): void
    {
        [$user] = $this->userWithCompany();

        $this->actingAs($user)
            ->post(route('clients.store'), [])
            ->assertSessionHasErrors(['name', 'email', 'address', 'postal_code', 'city', 'country']);
    }
}
