<?php

namespace App\Modules\Invoices\Actions;

use App\Models\Invoice;

class UpdateInvoiceAction
{
    public function execute(Invoice $invoice, array $data): Invoice
    {
        $items    = $data['items'] ?? [];
        $subtotal = 0;
        $vatTotal = 0;

        foreach ($items as $item) {
            $ht        = round($item['unit_price'] * $item['quantity'], 2);
            $vat       = round($ht * ($item['vat_rate'] / 100), 2);
            $subtotal += $ht;
            $vatTotal += $vat;
        }

        $invoice->update([
            'client_id'            => $data['client_id'],
            'issue_date'           => $data['issue_date'],
            'due_date'             => $data['due_date'] ?? null,
            'valid_until'          => $data['valid_until'] ?? null,
            'estimated_start_date' => $data['estimated_start_date'] ?? null,
            'subtotal'             => $subtotal,
            'vat_amount'           => $vatTotal,
            'total'                => $subtotal + $vatTotal,
            'currency'             => $data['currency'] ?? 'EUR',
            'notes'                => $data['notes'] ?? null,
            'footer'               => $data['footer'] ?? null,
            'chantier_address'     => $data['chantier_address'] ?? null,
            'payment_conditions'   => $data['payment_conditions'] ?? null,
        ]);

        $invoice->items()->delete();

        foreach ($items as $index => $item) {
            $ht  = round($item['unit_price'] * $item['quantity'], 2);
            $vat = round($ht * ($item['vat_rate'] / 100), 2);

            $invoice->items()->create([
                'product_id'  => $item['product_id'] ?? null,
                'description' => $item['description'],
                'unit_price'  => $item['unit_price'],
                'unit'        => $item['unit'] ?? 'unité',
                'quantity'    => $item['quantity'],
                'vat_rate'    => $item['vat_rate'],
                'total_ht'    => $ht,
                'total_ttc'   => $ht + $vat,
                'sort_order'  => $index,
            ]);
        }

        return $invoice->fresh('items');
    }
}
