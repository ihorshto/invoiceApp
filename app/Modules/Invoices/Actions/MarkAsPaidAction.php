<?php

namespace App\Modules\Invoices\Actions;

use App\Models\Invoice;

class MarkAsPaidAction
{
    public function execute(Invoice $invoice): Invoice
    {
        $invoice->update([
            'status'  => 'paid',
            'paid_at' => now(),
        ]);

        return $invoice;
    }
}
