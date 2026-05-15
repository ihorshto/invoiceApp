<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    private function makeSetup(): array
    {
        $user    = User::factory()->create();
        $company = Company::factory()->create(['user_id' => $user->id]);
        $client  = Client::factory()->create(['company_id' => $company->id]);
        return [$user, $company, $client];
    }

    private function itemPayload(): array
    {
        return [
            'description' => 'Consulting',
            'unit_price'  => 100.00,
            'quantity'    => 2,
            'vat_rate'    => 20.00,
            'unit'        => 'heure',
        ];
    }

    public function test_index_is_accessible(): void
    {
        [$user] = $this->makeSetup();

        $this->actingAs($user)
            ->get(route('invoices.index'))
            ->assertStatus(200)
            ->assertInertia(fn ($p) => $p->component('Invoices/Index'));
    }

    public function test_invoice_can_be_created(): void
    {
        [$user, $company, $client] = $this->makeSetup();

        $this->actingAs($user)
            ->post(route('invoices.store'), [
                'client_id'  => $client->id,
                'issue_date' => '2026-01-01',
                'due_date'   => '2026-01-31',
                'items'      => [$this->itemPayload()],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('invoices', ['company_id' => $company->id, 'client_id' => $client->id]);
        $this->assertDatabaseHas('invoice_items', ['description' => 'Consulting', 'total_ht' => 200.00]);
    }

    public function test_invoice_totals_are_calculated_correctly(): void
    {
        [$user, , $client] = $this->makeSetup();

        $this->actingAs($user)
            ->post(route('invoices.store'), [
                'client_id'  => $client->id,
                'issue_date' => '2026-01-01',
                'due_date'   => '2026-01-31',
                'items'      => [
                    ['description' => 'A', 'unit_price' => 100, 'quantity' => 1, 'vat_rate' => 20, 'unit' => 'u'],
                    ['description' => 'B', 'unit_price' => 50,  'quantity' => 2, 'vat_rate' => 10, 'unit' => 'u'],
                ],
            ]);

        $invoice = Invoice::withoutGlobalScopes()->latest()->first();
        $this->assertEquals(200.00, (float) $invoice->subtotal);   // 100 + 100
        $this->assertEquals(30.00,  (float) $invoice->vat_amount); // 20 + 10
        $this->assertEquals(230.00, (float) $invoice->total);
    }

    public function test_invoice_number_is_unique_and_sequential(): void
    {
        [$user, , $client] = $this->makeSetup();
        $payload = ['client_id' => $client->id, 'issue_date' => '2026-01-01', 'due_date' => '2026-01-31', 'items' => [$this->itemPayload()]];

        $this->actingAs($user)->post(route('invoices.store'), $payload);
        $this->actingAs($user)->post(route('invoices.store'), $payload);

        $numbers = Invoice::withoutGlobalScopes()->pluck('number')->toArray();
        $this->assertCount(2, array_unique($numbers));
        $this->assertStringContainsString('INV-2026-0001', $numbers[0]);
        $this->assertStringContainsString('INV-2026-0002', $numbers[1]);
    }

    public function test_invoice_can_be_marked_paid(): void
    {
        [$user, $company, $client] = $this->makeSetup();
        $invoice = Invoice::factory()->create([
            'company_id' => $company->id,
            'client_id'  => $client->id,
            'status'     => 'sent',
        ]);

        $this->actingAs($user)
            ->patch(route('invoices.status', $invoice), ['status' => 'paid'])
            ->assertRedirect();

        $this->assertEquals('paid', $invoice->fresh()->status);
        $this->assertNotNull($invoice->fresh()->paid_at);
    }

    public function test_draft_invoice_can_be_deleted(): void
    {
        [$user, $company, $client] = $this->makeSetup();
        $invoice = Invoice::factory()->create([
            'company_id' => $company->id,
            'client_id'  => $client->id,
            'status'     => 'draft',
        ]);

        $this->actingAs($user)
            ->delete(route('invoices.destroy', $invoice))
            ->assertRedirect(route('invoices.index'));

        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
    }

    public function test_required_fields_validation(): void
    {
        [$user] = $this->makeSetup();

        $this->actingAs($user)
            ->post(route('invoices.store'), [])
            ->assertSessionHasErrors(['client_id', 'issue_date', 'due_date', 'items']);
    }
}
