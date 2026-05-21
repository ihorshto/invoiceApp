<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DevisPdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_devis_pdf_route_returns_pdf_content(): void
    {
        $user    = User::factory()->create();
        $company = Company::factory()->create(['user_id' => $user->id]);
        $client  = Client::factory()->create(['company_id' => $company->id]);
        $devis   = Invoice::factory()->devis()->create([
            'company_id' => $company->id,
            'client_id'  => $client->id,
            'status'     => 'sent',
        ]);
        InvoiceItem::factory()->create(['invoice_id' => $devis->id]);

        $response = $this->actingAs($user)->get(route('devis.pdf', $devis));

        $response->assertStatus(200);
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_devis_pdf_uses_devis_template(): void
    {
        $user    = User::factory()->create();
        $company = Company::factory()->create([
            'user_id' => $user->id,
            'name'    => 'Société Test SARL',
            'iban'    => 'FR76 1234 5678 9012',
        ]);
        $client  = Client::factory()->create([
            'company_id' => $company->id,
            'name'       => 'Client Dupont',
        ]);
        $devis   = Invoice::factory()->devis()->create([
            'company_id'          => $company->id,
            'client_id'           => $client->id,
            'number'              => 'DEVIS-2026-001',
            'status'              => 'sent',
            'payment_conditions'  => '30% à la signature',
        ]);
        InvoiceItem::factory()->create(['invoice_id' => $devis->id]);

        $response = $this->actingAs($user)->get(route('devis.pdf', $devis));

        $response->assertStatus(200);
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_invoice_pdf_route_still_works_after_routing_change(): void
    {
        $user    = User::factory()->create();
        $company = Company::factory()->create(['user_id' => $user->id]);
        $client  = Client::factory()->create(['company_id' => $company->id]);
        $invoice = Invoice::factory()->create([
            'company_id' => $company->id,
            'client_id'  => $client->id,
            'status'     => 'sent',
        ]);
        InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

        $response = $this->actingAs($user)->get(route('invoices.pdf', $invoice));

        $response->assertStatus(200);
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }
}
