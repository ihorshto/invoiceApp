<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoicePdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_pdf_route_returns_pdf_content(): void
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
