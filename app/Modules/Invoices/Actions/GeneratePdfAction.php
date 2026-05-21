<?php

namespace App\Modules\Invoices\Actions;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class GeneratePdfAction
{
    public function stream(Invoice $invoice, string $locale = 'fr'): Response
    {
        $invoice->load('company', 'client', 'items');

        $view = match(true) {
            $invoice->isDevis() && $locale === 'fr' => 'pdf.devis-fr',
            $locale === 'fr'                         => 'pdf.invoice-fr',
            $locale === 'uk'                         => 'pdf.invoice-uk',
            default                                  => 'pdf.invoice-fr',
        };

        $pdf = Pdf::loadView($view, [
            'invoice' => $invoice,
            'company' => $invoice->company,
        ])->setPaper('a4');

        $prefix   = $invoice->isDevis() ? 'devis' : 'facture';
        $filename = "{$prefix}-{$invoice->number}.pdf";

        return $pdf->stream($filename);
    }
}
