<?php

namespace Tests\Unit\Actions;

use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Modules\Invoices\Actions\ConvertToInvoiceAction;
use App\Modules\Invoices\Actions\InvoiceNumberGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConvertToInvoiceActionTest extends TestCase
{
    use RefreshDatabase;

    private function makeDevis(string $status = 'accepted'): Invoice
    {
        $company = Company::factory()->create();
        $client  = Client::factory()->create(['company_id' => $company->id]);

        $devis = Invoice::factory()->devis()->create([
            'company_id' => $company->id,
            'client_id'  => $client->id,
            'status'     => $status,
            'subtotal'   => '1500.00',
            'vat_amount' => '0.00',
            'total'      => '1500.00',
        ]);

        $devis->items()->createMany([
            ['description' => 'Peinture', 'unit_price' => 500, 'quantity' => 1, 'vat_rate' => 0, 'unit' => 'm²', 'total_ht' => 500, 'total_ttc' => 500, 'sort_order' => 0],
            ['description' => 'Enduit',   'unit_price' => 700, 'quantity' => 1, 'vat_rate' => 0, 'unit' => 'm²', 'total_ht' => 700, 'total_ttc' => 700, 'sort_order' => 1],
            ['description' => 'Apprêt',  'unit_price' => 300, 'quantity' => 1, 'vat_rate' => 0, 'unit' => 'm²', 'total_ht' => 300, 'total_ttc' => 300, 'sort_order' => 2],
        ]);

        return $devis;
    }

    private function action(): ConvertToInvoiceAction
    {
        return new ConvertToInvoiceAction(new InvoiceNumberGenerator());
    }

    public function test_converts_accepted_devis_to_invoice(): void
    {
        $devis   = $this->makeDevis('accepted');
        $invoice = $this->action()->execute($devis);

        $this->assertSame('invoice', $invoice->type->value);
        $this->assertSame('draft', $invoice->status);
        $this->assertStringStartsWith('INV-', $invoice->number);
        $this->assertSame((float) $devis->subtotal, (float) $invoice->subtotal);
        $this->assertSame((float) $devis->total, (float) $invoice->total);
        $this->assertSame($devis->id, $invoice->source_document_id);
    }

    public function test_copies_all_items_to_new_invoice(): void
    {
        $devis   = $this->makeDevis('accepted');
        $invoice = $this->action()->execute($devis);

        $this->assertCount(3, $invoice->items);
        $this->assertSame('Peinture', $invoice->items->first()->description);
    }

    public function test_devis_status_flips_to_converted(): void
    {
        $devis = $this->makeDevis('accepted');
        $this->action()->execute($devis);

        $this->assertSame('converted', $devis->fresh()->status);
    }

    public function test_throws_when_devis_already_converted(): void
    {
        $devis = $this->makeDevis('accepted');
        $this->action()->execute($devis);

        $this->expectException(\DomainException::class);
        $this->action()->execute($devis->fresh());
    }

    public function test_throws_for_rejected_devis(): void
    {
        $devis = $this->makeDevis('rejected');

        $this->expectException(\DomainException::class);
        $this->action()->execute($devis);
    }

    public function test_throws_for_invoice_type(): void
    {
        $company = Company::factory()->create();
        $invoice = Invoice::factory()->create(['company_id' => $company->id, 'type' => 'invoice']);

        $this->expectException(\DomainException::class);
        $this->action()->execute($invoice);
    }
}
