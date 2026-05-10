<?php

namespace App\Modules\Invoices\Actions;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class GeneratePdfAction
{
    public function stream(Invoice $invoice): Response
    {
        $invoice->load('company', 'client', 'items');

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'company' => $invoice->company,
        ])->setPaper('a4');

        $filename = "facture-{$invoice->number}.pdf";

        return $pdf->stream($filename);
    }
}
