<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111; }
    .page { padding: 40px; }

    .doc-header { text-align: right; margin-bottom: 26px; }
    .doc-title { font-size: 15px; font-weight: bold; letter-spacing: 0.04em; }
    .doc-number { font-size: 11px; font-weight: bold; margin: 2px 0; color: #333; }
    .doc-meta { font-size: 9px; color: #666; line-height: 1.9; margin-top: 5px; }

    .parties { display: flex; gap: 120px; margin-bottom: 26px; }
    .party { flex: 1; font-size: 10px; color: #444; line-height: 1.7; }
    .party-name { font-size: 12px; font-weight: bold; color: #111; display: block; margin-bottom: 3px; }
    .party-chantier { font-size: 9px; color: #888; margin-top: 4px; }

    table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    thead th { background: #f7f7f7; border-top: 1px solid #ccc; border-bottom: 1px solid #ccc; padding: 7px 8px; font-size: 10px; font-weight: bold; color: #333; text-align: left; }
    thead th.right { text-align: right; }
    tbody td { padding: 6px 8px; border-bottom: 1px solid #f0f0f0; font-size: 10px; }
    tbody td.right { text-align: right; }

    .totals { margin-left: auto; width: 220px; font-size: 10px; margin-bottom: 16px; }
    .total-row { display: flex; justify-content: space-between; padding: 4px 0; color: #555; }
    .total-row .val { font-weight: bold; color: #111; }
    .total-grand { display: flex; justify-content: space-between; padding: 7px 0; border-top: 1px solid #ccc; margin-top: 4px; font-size: 12px; font-weight: bold; color: #111; }

    .vat-notice { font-size: 9px; color: #777; font-style: italic; margin-bottom: 20px; }

    .payment { font-size: 10px; color: #444; line-height: 1.8; margin-bottom: 28px; }
    .payment-title { font-weight: bold; margin-bottom: 4px; }
    .payment-body { white-space: pre-wrap; }

    .signatures { display: flex; gap: 40px; margin-top: 10px; }
    .sig-box { flex: 1; border-top: 1px solid #ccc; padding-top: 8px; font-size: 9px; color: #444; line-height: 1.9; }
    .sig-date { color: #aaa; }
</style>
</head>
<body>
<div class="page">

    {{-- HEADER: DEVIS info block, right-aligned. estimated_start_date intentionally omitted per design spec. --}}
    <div class="doc-header">
        <div class="doc-title">DEVIS</div>
        <div class="doc-number">N° {{ $invoice->number }}</div>
        <div class="doc-meta">
            Date d'émission : {{ $invoice->issue_date->format('d/m/Y') }}<br>
            @if($invoice->valid_until)
                Validité : {{ $invoice->valid_until->format('d/m/Y') }}
            @endif
        </div>
    </div>

    {{-- COMPANY + CLIENT: two columns, no section labels --}}
    <div class="parties">
        <div class="party">
            <span class="party-name">{{ $company->name }}</span>
            {{ $company->address }}<br>
            {{ $company->postal_code }} {{ $company->city }}<br>
            @if($company->phone) Tél : {{ $company->phone }}<br> @endif
            @if($company->email) {{ $company->email }}<br> @endif
            @if($company->vat_number) SIRET : {{ $company->vat_number }} @endif
        </div>
        <div class="party">
            <span class="party-name">{{ $invoice->client->name }}</span>
            {{ $invoice->client->address }}<br>
            {{ $invoice->client->postal_code }} {{ $invoice->client->city }}<br>
            @if($invoice->client->phone) Tél : {{ $invoice->client->phone }} @endif
            @if($invoice->chantier_address)
                <div class="party-chantier">Chantier : {{ $invoice->chantier_address }}</div>
            @endif
        </div>
    </div>

    {{-- WORK ITEMS TABLE --}}
    <table>
        <thead>
            <tr>
                <th style="width:52%">Désignation</th>
                <th class="right" style="width:10%">Qté</th>
                <th class="right" style="width:8%">Unité</th>
                <th class="right" style="width:15%">Prix U. HT</th>
                <th class="right" style="width:15%">Total HT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td>{{ $item->description }}</td>
                <td class="right">{{ number_format($item->quantity, 2, ',', ' ') }}</td>
                <td class="right">{{ $item->unit }}</td>
                <td class="right">{{ number_format($item->unit_price, 2, ',', ' ') }} {{ $invoice->currency }}</td>
                <td class="right">{{ number_format($item->total_ht, 2, ',', ' ') }} {{ $invoice->currency }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- TOTALS: right-aligned, TVA always shown --}}
    <div class="totals">
        <div class="total-row">
            <span>Total HT</span>
            <span class="val">{{ number_format($invoice->subtotal, 2, ',', ' ') }} {{ $invoice->currency }}</span>
        </div>
        <div class="total-row">
            <span>TVA</span>
            <span class="val">{{ number_format($invoice->vat_amount, 2, ',', ' ') }} {{ $invoice->currency }}</span>
        </div>
        <div class="total-grand">
            <span>Total TTC</span>
            <span>{{ number_format($invoice->total, 2, ',', ' ') }} {{ $invoice->currency }}</span>
        </div>
    </div>

    {{-- VAT NOTICE: shown only for micro-entreprises (TVA = 0) --}}
    @if($invoice->vat_amount == 0)
    <div class="vat-notice">TVA non applicable, article 293 B du CGI</div>
    @endif

    {{-- PAYMENT CONDITIONS --}}
    @if($invoice->payment_conditions || $company->iban)
    <div class="payment">
        @if($invoice->payment_conditions)
            <div class="payment-title">Conditions de paiement :</div>
            <div class="payment-body">{{ $invoice->payment_conditions }}</div>
        @endif
        @if($company->iban)
            <br>Mode de paiement : Virement bancaire<br>
            IBAN : {{ $company->iban }}
        @endif
    </div>
    @endif

    {{-- SIGNATURES --}}
    <div class="signatures">
        <div class="sig-box">
            <strong>Bon pour accord</strong><br>
            Signature du client<br>
            <span class="sig-date">Date : ________________________</span>
        </div>
        <div class="sig-box">
            <strong>Signature de l'entreprise</strong><br>
            {{ $company->name }}<br>
            <span class="sig-date">Date : ________________________</span>
        </div>
    </div>

</div>
</body>
</html>
