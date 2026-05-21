<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DevisControllerTest extends TestCase
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
            'description' => 'Pose de parquet',
            'unit_price'  => 45.00,
            'quantity'    => 30,
            'vat_rate'    => 0,
            'unit'        => 'm²',
        ];
    }

    private function devisPayload(int $clientId): array
    {
        return [
            'client_id'   => $clientId,
            'issue_date'  => '2026-06-01',
            'valid_until' => '2026-07-01',
            'items'       => [$this->itemPayload()],
        ];
    }

    public function test_index_renders_devis_page(): void
    {
        [$user] = $this->makeSetup();

        $this->actingAs($user)
            ->get(route('devis.index'))
            ->assertStatus(200)
            ->assertInertia(fn ($p) => $p->component('Devis/Index'));
    }

    public function test_index_lists_only_devis_not_invoices(): void
    {
        [$user, $company, $client] = $this->makeSetup();

        Invoice::factory()->create(['company_id' => $company->id, 'client_id' => $client->id, 'type' => 'invoice']);
        Invoice::factory()->devis()->create(['company_id' => $company->id, 'client_id' => $client->id]);

        $response = $this->actingAs($user)
            ->get(route('devis.index'))
            ->assertStatus(200);

        $response->assertInertia(fn ($p) =>
            $p->component('Devis/Index')
              ->where('devis.total', 1)
        );
    }

    public function test_devis_can_be_created(): void
    {
        [$user, $company, $client] = $this->makeSetup();

        $this->actingAs($user)
            ->post(route('devis.store'), $this->devisPayload($client->id))
            ->assertRedirect();

        $this->assertDatabaseHas('invoices', [
            'company_id' => $company->id,
            'client_id'  => $client->id,
            'type'       => 'devis',
        ]);

        $devis = Invoice::withoutGlobalScopes()->where('type', 'devis')->first();
        $this->assertStringStartsWith('DEV-', $devis->number);
        $this->assertNull($devis->due_date);
        $this->assertNotNull($devis->valid_until);
    }

    public function test_devis_store_requires_valid_until(): void
    {
        [$user, , $client] = $this->makeSetup();

        $this->actingAs($user)
            ->post(route('devis.store'), [
                'client_id'  => $client->id,
                'issue_date' => '2026-06-01',
                'items'      => [$this->itemPayload()],
            ])
            ->assertSessionHasErrors(['valid_until']);
    }

    public function test_devis_store_with_optional_fields(): void
    {
        [$user, $company, $client] = $this->makeSetup();

        $this->actingAs($user)
            ->post(route('devis.store'), array_merge($this->devisPayload($client->id), [
                'chantier_address'   => "12 rue du Bâtiment\n75001 Paris",
                'payment_conditions' => '30% à la commande',
                'estimated_start_date' => '2026-08-01',
            ]))
            ->assertRedirect();

        $this->assertDatabaseHas('invoices', [
            'company_id'       => $company->id,
            'chantier_address' => "12 rue du Bâtiment\n75001 Paris",
        ]);
    }

    public function test_devis_can_be_updated(): void
    {
        [$user, $company, $client] = $this->makeSetup();
        $devis = Invoice::factory()->devis()->create(['company_id' => $company->id, 'client_id' => $client->id]);

        $this->actingAs($user)
            ->put(route('devis.update', $devis), array_merge($this->devisPayload($client->id), [
                'valid_until' => '2026-09-01',
            ]))
            ->assertRedirect(route('devis.show', $devis));

        $this->assertDatabaseHas('invoices', ['id' => $devis->id, 'valid_until' => '2026-09-01']);
    }

    public function test_update_status_to_accepted_sets_accepted_at(): void
    {
        [$user, $company, $client] = $this->makeSetup();
        $devis = Invoice::factory()->devis()->create([
            'company_id' => $company->id,
            'client_id'  => $client->id,
            'status'     => 'sent',
        ]);

        $this->actingAs($user)
            ->patch(route('devis.status', $devis), ['status' => 'accepted'])
            ->assertRedirect();

        $fresh = $devis->fresh();
        $this->assertSame('accepted', $fresh->status);
        $this->assertNotNull($fresh->accepted_at);
    }

    public function test_update_status_rejects_invalid_status(): void
    {
        [$user, $company, $client] = $this->makeSetup();
        $devis = Invoice::factory()->devis()->create(['company_id' => $company->id, 'client_id' => $client->id]);

        $this->actingAs($user)
            ->patch(route('devis.status', $devis), ['status' => 'paid'])
            ->assertSessionHasErrors(['status']);
    }

    public function test_pdf_returns_pdf_response_with_devis_filename(): void
    {
        [$user, $company, $client] = $this->makeSetup();
        $company->update(['name' => 'Test Corp', 'address' => '1 rue Test', 'postal_code' => '75001', 'city' => 'Paris', 'country' => 'France']);
        $client->update(['address' => '2 rue Client', 'postal_code' => '75002', 'city' => 'Paris', 'country' => 'France']);

        $devis = Invoice::factory()->devis()->create([
            'company_id' => $company->id,
            'client_id'  => $client->id,
        ]);
        $devis->items()->create([
            'description' => 'Test', 'unit_price' => 100, 'quantity' => 1,
            'vat_rate' => 0, 'unit' => 'm²', 'total_ht' => 100, 'total_ttc' => 100, 'sort_order' => 0,
        ]);

        $response = $this->actingAs($user)
            ->get(route('devis.pdf', $devis));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('devis-', $response->headers->get('content-disposition'));
    }

    public function test_convert_creates_invoice_and_marks_devis_converted(): void
    {
        [$user, $company, $client] = $this->makeSetup();
        $devis = Invoice::factory()->devis()->create([
            'company_id' => $company->id,
            'client_id'  => $client->id,
            'status'     => 'accepted',
            'subtotal'   => 1000,
            'vat_amount' => 0,
            'total'      => 1000,
        ]);
        $devis->items()->create([
            'description' => 'Carrelage', 'unit_price' => 1000, 'quantity' => 1,
            'vat_rate' => 0, 'unit' => 'm²', 'total_ht' => 1000, 'total_ttc' => 1000, 'sort_order' => 0,
        ]);

        $this->actingAs($user)
            ->post(route('devis.convert', $devis))
            ->assertRedirect();

        $this->assertDatabaseHas('invoices', [
            'type'               => 'invoice',
            'source_document_id' => $devis->id,
            'subtotal'           => '1000.00',
        ]);

        $invoice = Invoice::withoutGlobalScopes()->where('source_document_id', $devis->id)->first();
        $this->assertStringStartsWith('INV-', $invoice->number);
        $this->assertCount(1, $invoice->items);

        $this->assertSame('converted', $devis->fresh()->status);
    }

    public function test_convert_throws_when_already_converted(): void
    {
        [$user, $company, $client] = $this->makeSetup();
        $devis = Invoice::factory()->devis()->create([
            'company_id' => $company->id,
            'client_id'  => $client->id,
            'status'     => 'converted',
        ]);

        $this->actingAs($user)
            ->post(route('devis.convert', $devis))
            ->assertStatus(500);
    }

    public function test_draft_devis_can_be_deleted(): void
    {
        [$user, $company, $client] = $this->makeSetup();
        $devis = Invoice::factory()->devis()->create([
            'company_id' => $company->id,
            'client_id'  => $client->id,
            'status'     => 'draft',
        ]);

        $this->actingAs($user)
            ->delete(route('devis.destroy', $devis))
            ->assertRedirect(route('devis.index'));

        $this->assertDatabaseMissing('invoices', ['id' => $devis->id]);
    }

    public function test_show_returns_404_for_invoice_type(): void
    {
        [$user, $company, $client] = $this->makeSetup();
        $invoice = Invoice::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);

        $this->actingAs($user)
            ->get(route('devis.show', $invoice))
            ->assertStatus(404);
    }
}
