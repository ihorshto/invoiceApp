<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceStatusTest extends TestCase
{
    use RefreshDatabase;

    private function makeSetup(): array
    {
        $user    = User::factory()->create();
        $company = Company::factory()->create(['user_id' => $user->id]);
        $client  = Client::factory()->create(['company_id' => $company->id]);
        return [$user, $company, $client];
    }

    public function test_status_can_be_changed(): void
    {
        [$user, $company, $client] = $this->makeSetup();
        $invoice = Invoice::factory()->create([
            'company_id' => $company->id,
            'client_id'  => $client->id,
            'status'     => 'draft',
        ]);

        $this->actingAs($user)
            ->patch(route('invoices.status', $invoice), ['status' => 'sent'])
            ->assertRedirect(route('invoices.show', $invoice));

        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'status' => 'sent']);
    }

    public function test_changing_to_paid_sets_paid_at(): void
    {
        [$user, $company, $client] = $this->makeSetup();
        $invoice = Invoice::factory()->create([
            'company_id' => $company->id,
            'client_id'  => $client->id,
            'status'     => 'sent',
        ]);

        $this->actingAs($user)
            ->patch(route('invoices.status', $invoice), ['status' => 'paid']);

        $this->assertNotNull(Invoice::find($invoice->id)->paid_at);
    }

    public function test_changing_from_paid_clears_paid_at(): void
    {
        [$user, $company, $client] = $this->makeSetup();
        $invoice = Invoice::factory()->create([
            'company_id' => $company->id,
            'client_id'  => $client->id,
            'status'     => 'paid',
            'paid_at'    => now(),
        ]);

        $this->actingAs($user)
            ->patch(route('invoices.status', $invoice), ['status' => 'sent']);

        $this->assertNull(Invoice::find($invoice->id)->paid_at);
    }

    public function test_invalid_status_is_rejected(): void
    {
        [$user, $company, $client] = $this->makeSetup();
        $invoice = Invoice::factory()->create([
            'company_id' => $company->id,
            'client_id'  => $client->id,
            'status'     => 'draft',
        ]);

        $this->actingAs($user)
            ->patch(route('invoices.status', $invoice), ['status' => 'bogus'])
            ->assertSessionHasErrors('status');
    }
}
