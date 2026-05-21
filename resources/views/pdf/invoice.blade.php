<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
    .page { padding: 40px; }

    /* Header */
    .header { display: flex; justify-content: space-between; margin-bottom: 40px; }
    .company-name { font-size: 20px; font-weight: bold; color: #1d4ed8; }
    .company-info { font-size: 10px; color: #6b7280; margin-top: 4px; line-height: 1.5; }
    .invoice-meta { text-align: right; }
    .invoice-number { font-size: 18px; font-weight: bold; }
    .invoice-label { font-size: 10px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; }

    /* Status badge */
    .badge { display: inline-block; padding: 2px 10px; border-radius: 12px; font-size: 10px; font-weight: bold; margin-top: 6px; }
    .badge-draft     { background: #f3f4f6; color: #374151; }
    .badge-sent      { background: #dbeafe; color: #1d4ed8; }
    .badge-paid      { background: #d1fae5; color: #065f46; }
    .badge-overdue   { background: #fee2e2; color: #991b1b; }
    .badge-cancelled { background: #fef3c7; color: #92400e; }
    .badge-accepted  { background: #d1fae5; color: #065f46; }
    .badge-rejected  { background: #fee2e2; color: #991b1b; }
    .badge-converted { background: #ede9fe; color: #5b21b6; }

    /* Parties */
    .parties { display: flex; gap: 40px; margin-bottom: 30px; }
    .party { flex: 1; }
    .party-label { font-size: 9px; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; }
    .party-name { font-weight: bold; font-size: 13px; }
    .party-info { font-size: 10px; color: #6b7280; line-height: 1.6; margin-top: 3px; }

    /* Dates */
    .dates { display: flex; gap: 30px; margin-bottom: 30px; padding: 14px 18px; background: #f9fafb; border-radius: 6px; }
    .date-block { }
    .date-label { font-size: 9px; color: #9ca3af; text-transform: uppercase; }
    .date-value { font-weight: 600; font-size: 12px; margin-top: 2px; }

    /* Items table */
    table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
    thead th { background: #1d4ed8; color: #fff; padding: 10px 12px; text-align: left; font-size: 10px; text-transform: uppercase; }
    thead th.right { text-align: right; }
    tbody td { padding: 9px 12px; font-size: 11px; border-bottom: 1px solid #f3f4f6; }
    tbody td.right { text-align: right; }
    tbody tr:nth-child(even) { background: #f9fafb; }

    /* Totals */
    .totals { margin-left: auto; width: 240px; }
    .total-row { display: flex; justify-content: space-between; padding: 5px 0; font-size: 11px; color: #6b7280; }
    .total-row.bold { font-size: 14px; font-weight: bold; color: #111827; border-top: 2px solid #e5e7eb; padding-top: 10px; margin-top: 4px; }
    .total-row .val { font-weight: 600; color: #111827; }

    /* Notes & Footer */
    .notes-section { display: flex; gap: 30px; margin-top: 30px; font-size: 10px; color: #6b7280; }
    .notes-block { flex: 1; }
    .notes-label { font-size: 9px; text-transform: uppercase; color: #9ca3af; margin-bottom: 4px; }
    .notes-text { white-space: pre-wrap; line-height: 1.5; }
    .footer { margin-top: 50px; text-align: center; font-size: 9px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 14px; }

    /* Signature block */
    .signatures { display: flex; gap: 40px; margin-top: 40px; }
    .signature-box { flex: 1; border-top: 1px solid #d1d5db; padding-top: 8px; font-size: 10px; color: #374151; line-height: 1.8; }
    .signature-date { color: #9ca3af; margin-top: 4px; }
</style>
</head>
<body>
<div class="page">

    <!-- Header -->
    <div class="header">
        <div>
            <div class="company-name">{{ $company->name }}</div>
            <div class="company-info">
                {{ $company->address }}<br>
                {{ $company->postal_code }} {{ $company->city }}, {{ $company->country }}<br>
                @if($company->email) {{ $company->email }}<br> @endif
                @if($company->vat_number) TVA / ЄДРПОУ: {{ $company->vat_number }} @endif
            </div>
        </div>
        <div class="invoice-meta">
            <div class="invoice-label">
                {{ $invoice->isDevis() ? 'Devis' : 'Facture / Рахунок' }}
            </div>
            <div class="invoice-number">{{ $invoice->number }}</div>
            <div>
                @php
                    $labels = [
                        'draft'     => 'Brouillon',
                        'sent'      => 'Envoyée',
                        'paid'      => 'Payée',
                        'overdue'   => 'En retard',
                        'cancelled' => 'Annulée',
                        'accepted'  => 'Accepté',
                        'rejected'  => 'Refusé',
                        'converted' => 'Converti',
                    ];
                @endphp
                <span class="badge badge-{{ $invoice->status }}">
                    {{ $labels[$invoice->status] ?? $invoice->status }}
                </span>
            </div>
        </div>
    </div>

    <!-- Parties -->
    <div class="parties">
        <div class="party">
            <div class="party-label">Émetteur / Постачальник</div>
            <div class="party-name">{{ $company->name }}</div>
            <div class="party-info">
                {{ $company->address }}<br>
                {{ $company->postal_code }} {{ $company->city }}<br>
                {{ $company->country }}
            </div>
        </div>
        <div class="party">
            <div class="party-label">Destinataire / Отримувач</div>
            <div class="party-name">{{ $invoice->client->name }}</div>
            <div class="party-info">
                {{ $invoice->client->address }}<br>
                {{ $invoice->client->postal_code }} {{ $invoice->client->city }}<br>
                {{ $invoice->client->country }}<br>
                @if($invoice->client->vat_number) TVA: {{ $invoice->client->vat_number }} @endif
            </div>
        </div>
        @if($invoice->isDevis() && $invoice->chantier_address)
        <div class="party">
            <div class="party-label">Adresse du chantier</div>
            <div class="party-info" style="white-space: pre-wrap;">{{ $invoice->chantier_address }}</div>
        </div>
        @endif
    </div>

    <!-- Dates -->
    <div class="dates">
        <div class="date-block">
            <div class="date-label">Date d'émission</div>
            <div class="date-value">{{ $invoice->issue_date->format('d/m/Y') }}</div>
        </div>
        @if($invoice->isDevis())
            @if($invoice->valid_until)
            <div class="date-block">
                <div class="date-label">Validité</div>
                <div class="date-value">{{ $invoice->valid_until->format('d/m/Y') }}</div>
            </div>
            @endif
            @if($invoice->estimated_start_date)
            <div class="date-block">
                <div class="date-label">Début prévu</div>
                <div class="date-value">{{ $invoice->estimated_start_date->format('d/m/Y') }}</div>
            </div>
            @endif
        @else
            @if($invoice->due_date)
            <div class="date-block">
                <div class="date-label">Date d'échéance / Термін</div>
                <div class="date-value">{{ $invoice->due_date->format('d/m/Y') }}</div>
            </div>
            @endif
            @if($invoice->paid_at)
            <div class="date-block">
                <div class="date-label">Payée le / Оплачено</div>
                <div class="date-value">{{ $invoice->paid_at->format('d/m/Y') }}</div>
            </div>
            @endif
        @endif
    </div>

    <!-- Items -->
    <table>
        <thead>
            <tr>
                <th style="width:50%">Désignation</th>
                <th class="right" style="width:12%">Prix U. HT</th>
                <th class="right" style="width:10%">Qté</th>
                <th class="right" style="width:6%">Unité</th>
                @if(!$invoice->isDevis())
                <th class="right" style="width:8%">TVA</th>
                @endif
                <th class="right" style="width:14%">Total HT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td>{{ $item->description }}</td>
                <td class="right">{{ number_format($item->unit_price, 2, ',', ' ') }} {{ $invoice->currency }}</td>
                <td class="right">{{ number_format($item->quantity, 2, ',', ' ') }}</td>
                <td class="right">{{ $item->unit }}</td>
                @if(!$invoice->isDevis())
                <td class="right">{{ number_format($item->vat_rate, 1, ',', ' ') }}%</td>
                @endif
                <td class="right">{{ number_format($item->total_ht, 2, ',', ' ') }} {{ $invoice->currency }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <div class="totals">
        <div class="total-row">
            <span>Total HT</span>
            <span class="val">{{ number_format($invoice->subtotal, 2, ',', ' ') }} {{ $invoice->currency }}</span>
        </div>
        @if(!$invoice->isDevis())
        <div class="total-row">
            <span>TVA</span>
            <span class="val">{{ number_format($invoice->vat_amount, 2, ',', ' ') }} {{ $invoice->currency }}</span>
        </div>
        <div class="total-row bold">
            <span>Total TTC</span>
            <span>{{ number_format($invoice->total, 2, ',', ' ') }} {{ $invoice->currency }}</span>
        </div>
        @else
        <div class="total-row bold">
            <span>Total HT</span>
            <span>{{ number_format($invoice->total, 2, ',', ' ') }} {{ $invoice->currency }}</span>
        </div>
        @endif
    </div>

    @if($invoice->vat_amount == 0 || $invoice->isDevis())
    <div style="margin-top:12px; font-size:10px; color:#6b7280; font-style:italic;">
        TVA non applicable, article 293 B du CGI
    </div>
    @endif

    @if($invoice->notes || $invoice->footer || ($invoice->isDevis() && $invoice->payment_conditions))
    <div class="notes-section">
        @if($invoice->notes)
        <div class="notes-block">
            <div class="notes-label">Notes</div>
            <div class="notes-text">{{ $invoice->notes }}</div>
        </div>
        @endif
        @if($invoice->footer)
        <div class="notes-block">
            <div class="notes-label">Informations</div>
            <div class="notes-text">{{ $invoice->footer }}</div>
        </div>
        @endif
        @if($invoice->isDevis() && $invoice->payment_conditions)
        <div class="notes-block">
            <div class="notes-label">Conditions de paiement</div>
            <div class="notes-text">{{ $invoice->payment_conditions }}</div>
        </div>
        @endif
    </div>
    @endif

    @if($invoice->isDevis())
    <div class="signatures">
        <div class="signature-box">
            <strong>Bon pour accord</strong><br>
            Signature du client<br>
            <div class="signature-date">Date : ________________________</div>
        </div>
        <div class="signature-box">
            <strong>Signature de l'entreprise</strong><br>
            {{ $company->name }}<br>
            <div class="signature-date">Date : ________________________</div>
        </div>
    </div>
    @endif

    @if($company->iban || $company->legal_footer)
    <div class="footer">
        @if($company->iban) IBAN: {{ $company->iban }} &nbsp;|&nbsp; @endif
        @if($company->legal_footer) {{ $company->legal_footer }} @endif
    </div>
    @endif

</div>
</body>
</html>
