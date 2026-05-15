<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function makeSetup(): array
    {
        $user    = User::factory()->create();
        $company = Company::factory()->create(['user_id' => $user->id]);
        $client  = Client::factory()->create(['company_id' => $company->id]);
        return [$user, $company, $client];
    }

    public function test_dashboard_returns_stats(): void
    {
        [$user, $company, $client] = $this->makeSetup();

        Invoice::factory()->create(['company_id' => $company->id, 'client_id' => $client->id, 'status' => 'paid',    'total' => 1000]);
        Invoice::factory()->create(['company_id' => $company->id, 'client_id' => $client->id, 'status' => 'sent',    'total' => 500]);
        Invoice::factory()->create(['company_id' => $company->id, 'client_id' => $client->id, 'status' => 'overdue', 'total' => 200]);
        Invoice::factory()->create(['company_id' => $company->id, 'client_id' => $client->id, 'status' => 'draft',   'total' => 100]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('stats')
            ->where('stats.total_amount',   1800)
            ->where('stats.paid_amount',    1000)
            ->where('stats.pending_amount', 500)
            ->where('stats.overdue_amount', 200)
            ->where('stats.total_count',    4)
            ->where('stats.paid_count',     1)
            ->where('stats.pending_count',  1)
            ->where('stats.overdue_count',  1)
        );
    }

    public function test_dashboard_only_shows_own_company_stats(): void
    {
        [$user, $company, $client]                   = $this->makeSetup();
        [$otherUser, $otherCompany, $otherClient]    = $this->makeSetup();

        Invoice::factory()->create(['company_id' => $company->id,      'client_id' => $client->id,      'status' => 'paid', 'total' => 1000]);
        Invoice::factory()->create(['company_id' => $otherCompany->id, 'client_id' => $otherClient->id, 'status' => 'paid', 'total' => 9999]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(fn ($page) => $page
            ->where('stats.paid_amount', 1000)
            ->where('stats.total_count', 1)
        );
    }
}
