<?php

namespace Tests\Unit\Models;

use App\Enums\DevisStatus;
use App\Enums\DocumentType;
use App\Enums\InvoiceStatus;
use App\Models\Company;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceDevisTest extends TestCase
{
    use RefreshDatabase;

    private function makeInvoice(array $attrs = []): Invoice
    {
        return Invoice::factory()->create(array_merge([
            'company_id' => Company::factory(),
        ], $attrs));
    }

    public function test_isInvoice_returns_true_for_invoice_type(): void
    {
        $invoice = $this->makeInvoice(['type' => 'invoice']);
        $this->assertTrue($invoice->isInvoice());
        $this->assertFalse($invoice->isDevis());
    }

    public function test_isDevis_returns_true_for_devis_type(): void
    {
        $devis = $this->makeInvoice(['type' => 'devis', 'due_date' => null]);
        $this->assertTrue($devis->isDevis());
        $this->assertFalse($devis->isInvoice());
    }

    public function test_isAccepted_true_for_accepted_devis(): void
    {
        $devis = $this->makeInvoice(['type' => 'devis', 'due_date' => null, 'status' => 'accepted']);
        $this->assertTrue($devis->isAccepted());
    }

    public function test_isAccepted_false_for_invoice(): void
    {
        $invoice = $this->makeInvoice(['status' => 'sent']);
        $this->assertFalse($invoice->isAccepted());
    }

    public function test_isEditable_invoice_draft_and_sent(): void
    {
        $draft = $this->makeInvoice(['type' => 'invoice', 'status' => 'draft']);
        $sent  = $this->makeInvoice(['type' => 'invoice', 'status' => 'sent']);
        $paid  = $this->makeInvoice(['type' => 'invoice', 'status' => 'paid']);

        $this->assertTrue($draft->isEditable());
        $this->assertTrue($sent->isEditable());
        $this->assertFalse($paid->isEditable());
    }

    public function test_isEditable_devis_draft_and_sent_only(): void
    {
        $draft    = $this->makeInvoice(['type' => 'devis', 'due_date' => null, 'status' => 'draft']);
        $sent     = $this->makeInvoice(['type' => 'devis', 'due_date' => null, 'status' => 'sent']);
        $accepted = $this->makeInvoice(['type' => 'devis', 'due_date' => null, 'status' => 'accepted']);

        $this->assertTrue($draft->isEditable());
        $this->assertTrue($sent->isEditable());
        $this->assertFalse($accepted->isEditable());
    }

    public function test_canConvertToInvoice_true_for_accepted_devis(): void
    {
        $devis = $this->makeInvoice(['type' => 'devis', 'due_date' => null, 'status' => 'accepted']);
        $this->assertTrue($devis->canConvertToInvoice());
    }

    public function test_canConvertToInvoice_true_for_sent_devis(): void
    {
        $devis = $this->makeInvoice(['type' => 'devis', 'due_date' => null, 'status' => 'sent']);
        $this->assertTrue($devis->canConvertToInvoice());
    }

    public function test_canConvertToInvoice_false_for_rejected_devis(): void
    {
        $devis = $this->makeInvoice(['type' => 'devis', 'due_date' => null, 'status' => 'rejected']);
        $this->assertFalse($devis->canConvertToInvoice());
    }

    public function test_canConvertToInvoice_false_for_invoice_type(): void
    {
        $invoice = $this->makeInvoice(['type' => 'invoice', 'status' => 'draft']);
        $this->assertFalse($invoice->canConvertToInvoice());
    }

    public function test_canConvertToInvoice_false_when_already_converted(): void
    {
        $devis = $this->makeInvoice(['type' => 'devis', 'due_date' => null, 'status' => 'accepted']);

        Invoice::factory()->create([
            'company_id'         => $devis->company_id,
            'type'               => 'invoice',
            'source_document_id' => $devis->id,
        ]);

        $this->assertFalse($devis->fresh()->canConvertToInvoice());
    }

    public function test_type_is_cast_to_document_type_enum(): void
    {
        $invoice = $this->makeInvoice(['type' => 'invoice']);
        $this->assertInstanceOf(DocumentType::class, $invoice->type);
        $this->assertSame(DocumentType::Invoice, $invoice->type);
    }
}
