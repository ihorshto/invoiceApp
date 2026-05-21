<?php

namespace App\Modules\Invoices\Actions;

use App\Enums\DevisStatus;
use App\Enums\DocumentType;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

class ConvertToInvoiceAction
{
    public function __construct(private InvoiceNumberGenerator $generator) {}

    public function execute(Invoice $devis): Invoice
    {
        if (! $devis->canConvertToInvoice()) {
            throw new \DomainException('Devis cannot be converted to invoice.');
        }

        return DB::transaction(function () use ($devis) {
            $invoice = $devis->company->invoices()->create([
                'client_id'          => $devis->client_id,
                'type'               => DocumentType::Invoice,
                'number'             => $this->generator->generate($devis->company, DocumentType::Invoice),
                'status'             => 'draft',
                'issue_date'         => now()->toDateString(),
                'due_date'           => now()->addDays(30)->toDateString(),
                'subtotal'           => $devis->subtotal,
                'vat_amount'         => $devis->vat_amount,
                'total'              => $devis->total,
                'currency'           => $devis->currency,
                'notes'              => $devis->notes,
                'footer'             => $devis->footer,
                'payment_conditions' => $devis->payment_conditions,
                'source_document_id' => $devis->id,
            ]);

            foreach ($devis->items as $item) {
                $invoice->items()->create($item->only([
                    'product_id', 'description', 'unit_price', 'unit',
                    'quantity', 'vat_rate', 'total_ht', 'total_ttc', 'sort_order',
                ]));
            }

            $devis->update(['status' => DevisStatus::Converted->value]);

            return $invoice->load('items');
        });
    }
}
