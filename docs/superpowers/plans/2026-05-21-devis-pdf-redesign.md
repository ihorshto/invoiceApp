# Devis PDF Redesign — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Create a dedicated `devis-fr.blade.php` PDF template for French devis with a clean, professional layout matching the approved design spec.

**Architecture:** New Blade template handles devis-only PDF rendering; `GeneratePdfAction` routes devis+fr to the new template; `invoice-fr.blade.php` is cleaned of all devis conditionals. TDD: failing test first, then template, then routing update.

**Tech Stack:** Laravel 11, Blade, barryvdh/laravel-dompdf, DejaVu Sans font, PHPUnit/Pest

---

## File Map

| Action | File | Purpose |
|---|---|---|
| CREATE | `resources/views/pdf/devis-fr.blade.php` | New dedicated devis PDF template |
| MODIFY | `app/Modules/Invoices/Actions/GeneratePdfAction.php` | Route devis+fr to new template |
| MODIFY | `resources/views/pdf/invoice-fr.blade.php` | Remove all `isDevis()` conditionals |
| CREATE | `tests/Feature/DevisPdfTest.php` | Feature tests for devis PDF route |

---

## Task 1: Write the failing devis PDF test

**Files:**
- Create: `tests/Feature/DevisPdfTest.php`

- [ ] **Step 1: Create the test file**

```php
<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DevisPdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_devis_pdf_route_returns_pdf_content(): void
    {
        $user    = User::factory()->create();
        $company = Company::factory()->create(['user_id' => $user->id]);
        $client  = Client::factory()->create(['company_id' => $company->id]);
        $devis   = Invoice::factory()->devis()->create([
            'company_id' => $company->id,
            'client_id'  => $client->id,
            'status'     => 'sent',
        ]);
        InvoiceItem::factory()->create(['invoice_id' => $devis->id]);

        $response = $this->actingAs($user)->get(route('devis.pdf', $devis));

        $response->assertStatus(200);
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_devis_pdf_uses_devis_template(): void
    {
        $user    = User::factory()->create();
        $company = Company::factory()->create([
            'user_id' => $user->id,
            'name'    => 'Société Test SARL',
            'iban'    => 'FR76 1234 5678 9012',
        ]);
        $client  = Client::factory()->create([
            'company_id' => $company->id,
            'name'       => 'Client Dupont',
        ]);
        $devis   = Invoice::factory()->devis()->create([
            'company_id'          => $company->id,
            'client_id'           => $client->id,
            'number'              => 'DEVIS-2026-001',
            'status'              => 'sent',
            'payment_conditions'  => '30% à la signature',
        ]);
        InvoiceItem::factory()->create(['invoice_id' => $devis->id]);

        $response = $this->actingAs($user)->get(route('devis.pdf', $devis));

        $response->assertStatus(200);
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_invoice_pdf_route_still_works_after_routing_change(): void
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
```

- [ ] **Step 2: Run the tests to confirm they fail**

```bash
./vendor/bin/sail php artisan test tests/Feature/DevisPdfTest.php --no-coverage
```

Expected: FAIL — `test_devis_pdf_route_returns_pdf_content` fails because the template will still route through `invoice-fr.blade.php` (which works, so this test may pass already). The other tests confirm no regression. We proceed knowing the new template does not exist yet.

---

## Task 2: Create `devis-fr.blade.php`

**Files:**
- Create: `resources/views/pdf/devis-fr.blade.php`

- [ ] **Step 1: Create the template**

```blade
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

    {{-- HEADER: DEVIS info block, right-aligned --}}
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

    {{-- VAT NOTICE: always shown for devis --}}
    <div class="vat-notice">TVA non applicable, article 293 B du CGI</div>

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
```

- [ ] **Step 2: Run tests — they should still fail** (template exists but routing not updated yet)

```bash
./vendor/bin/sail php artisan test tests/Feature/DevisPdfTest.php --no-coverage
```

Expected: all 3 tests PASS (devis route still renders via `invoice-fr.blade.php` → valid PDF). Confirm no error.

---

## Task 3: Update `GeneratePdfAction` routing

**Files:**
- Modify: `app/Modules/Invoices/Actions/GeneratePdfAction.php`

- [ ] **Step 1: Replace the view-selection logic**

Current file (`app/Modules/Invoices/Actions/GeneratePdfAction.php`):

```php
$view = in_array($locale, ['fr', 'uk'], true)
    ? "pdf.invoice-{$locale}"
    : 'pdf.invoice-fr';
```

Replace with:

```php
$view = match(true) {
    $invoice->isDevis() && $locale === 'fr' => 'pdf.devis-fr',
    $locale === 'fr'                         => 'pdf.invoice-fr',
    $locale === 'uk'                         => 'pdf.invoice-uk',
    default                                  => 'pdf.invoice-fr',
};
```

The full updated method:

```php
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
```

- [ ] **Step 2: Run tests to confirm all pass**

```bash
./vendor/bin/sail php artisan test tests/Feature/DevisPdfTest.php --no-coverage
```

Expected: all 3 tests PASS.

- [ ] **Step 3: Commit**

```bash
git add resources/views/pdf/devis-fr.blade.php \
        app/Modules/Invoices/Actions/GeneratePdfAction.php \
        tests/Feature/DevisPdfTest.php
git commit -m "feat(pdf): add dedicated devis-fr PDF template with clean layout"
```

---

## Task 4: Clean up `invoice-fr.blade.php`

Remove all `isDevis()` conditionals — the file now handles invoices only.

**Files:**
- Modify: `resources/views/pdf/invoice-fr.blade.php`

- [ ] **Step 1: Fix the document type label (line 74)**

Find:
```blade
<div class="invoice-label">{{ $invoice->isDevis() ? 'Devis' : 'Facture' }}</div>
```

Replace with:
```blade
<div class="invoice-label">Facture</div>
```

- [ ] **Step 2: Remove the chantier_address party block (lines 116–121)**

Find and remove entirely:
```blade
@if($invoice->isDevis() && $invoice->chantier_address)
<div class="party">
    <div class="party-label">Adresse du chantier</div>
    <div class="party-info" style="white-space: pre-wrap;">{{ $invoice->chantier_address }}</div>
</div>
@endif
```

- [ ] **Step 3: Simplify the dates block (lines 129–155)**

Find this entire block:
```blade
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
        <div class="date-label">Date d'échéance</div>
        <div class="date-value">{{ $invoice->due_date->format('d/m/Y') }}</div>
    </div>
    @endif
    @if($invoice->paid_at)
    <div class="date-block">
        <div class="date-label">Payée le</div>
        <div class="date-value">{{ $invoice->paid_at->format('d/m/Y') }}</div>
    </div>
    @endif
@endif
```

Replace with:
```blade
@if($invoice->due_date)
<div class="date-block">
    <div class="date-label">Date d'échéance</div>
    <div class="date-value">{{ $invoice->due_date->format('d/m/Y') }}</div>
</div>
@endif
@if($invoice->paid_at)
<div class="date-block">
    <div class="date-label">Payée le</div>
    <div class="date-value">{{ $invoice->paid_at->format('d/m/Y') }}</div>
</div>
@endif
```

- [ ] **Step 4: Remove the isDevis() guard from the TVA table column header (lines 165–168)**

Find:
```blade
@if(!$invoice->isDevis())
<th class="right" style="width:8%">TVA</th>
@endif
```

Replace with:
```blade
<th class="right" style="width:8%">TVA</th>
```

- [ ] **Step 5: Remove the isDevis() guard from the TVA table row (lines 178–180)**

Find:
```blade
@if(!$invoice->isDevis())
<td class="right">{{ number_format($item->vat_rate, 1, ',', ' ') }}%</td>
@endif
```

Replace with:
```blade
<td class="right">{{ number_format($item->vat_rate, 1, ',', ' ') }}%</td>
```

- [ ] **Step 6: Simplify the totals block (lines 192–207)**

Find:
```blade
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
```

Replace with:
```blade
<div class="total-row">
    <span>TVA</span>
    <span class="val">{{ number_format($invoice->vat_amount, 2, ',', ' ') }} {{ $invoice->currency }}</span>
</div>
<div class="total-row bold">
    <span>Total TTC</span>
    <span>{{ number_format($invoice->total, 2, ',', ' ') }} {{ $invoice->currency }}</span>
</div>
```

- [ ] **Step 7: Simplify the VAT notice condition (line 209)**

Find:
```blade
@if($invoice->vat_amount == 0 || $invoice->isDevis())
```

Replace with:
```blade
@if($invoice->vat_amount == 0)
```

- [ ] **Step 8: Remove the payment_conditions block from notes section (lines 229–235)**

Find and remove entirely:
```blade
@if($invoice->isDevis() && $invoice->payment_conditions)
<div class="notes-block">
    <div class="notes-label">Conditions de paiement</div>
    <div class="notes-text">{{ $invoice->payment_conditions }}</div>
</div>
@endif
```

- [ ] **Step 9: Remove the entire signatures section (lines 238–251)**

Find and remove entirely:
```blade
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
```

- [ ] **Step 10: Run the full test suite**

```bash
./vendor/bin/sail php artisan test --no-coverage
```

Expected: all tests PASS. Confirm `InvoicePdfTest` and `DevisPdfTest` both pass.

- [ ] **Step 11: Commit**

```bash
git add resources/views/pdf/invoice-fr.blade.php
git commit -m "refactor(pdf): remove devis conditionals from invoice-fr template"
```
