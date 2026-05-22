# Devis PDF — Design Spec

**Date:** 2026-05-21  
**Status:** Approved

## Goal

Redesign the PDF layout for French devis documents. The current `invoice-fr.blade.php` serves both invoices and devis with conditionals; the new layout requires a dedicated, clean template for devis.

## Approved Visual Style

**Ultra-clean (Option C, iterated to v8):**
- White background, black text
- No section divider lines — sections separated by spacing only
- Table: light gray header row (`border-top` + `border-bottom` only), very light row separators (`#f0f0f0`)
- Totals: thin separator line only above "Total TTC"
- Signature boxes: `border-top` lines (functional, not decorative)
- Font: DejaVu Sans (existing dompdf font)

## Layout Structure (top to bottom)

### 1. Header
- Right-aligned block only, nothing on the left
- Contents:
  - `DEVIS` — bold, 15px, uppercase, letter-spacing
  - `N° {number}` — semi-bold, 11px
  - `Date d'émission : {issue_date}` — 9px, gray
  - `Validité : {valid_until}` — 9px, gray (shown only if `valid_until` is set)

### 2. Company + Client
- Two `flex: 1` columns, `gap: 120px` (equivalent in A4 proportions)
- **No section labels** ("Prestataire", "Client", "Émetteur", etc.)
- Left column (Company): name bold 12px, then address / postal+city / phone / email / SIRET in 10px gray
- Right column (Client): name bold 12px, then address / postal+city / phone in 10px gray
- If `chantier_address` is set: shown as a third line under client address, labeled `Chantier :` in small gray

### 3. Work Items Table
- Columns: Désignation | Qté | Unité | Prix U. HT | Total HT
- Header row: `background: #f7f7f7`, `border-top: 1px solid #ccc`, `border-bottom: 1px solid #ccc`, font-weight 600
- Body rows: `border-bottom: 1px solid #f0f0f0` (very light)
- Numeric columns (Qté, Unité, Prix U. HT, Total HT): right-aligned
- Désignation: left-aligned, takes remaining width

### 4. Totals
- Right-aligned block, min-width ~220px
- Three rows:
  1. `Total HT` — label gray, value bold black
  2. `TVA (x %)` — always shown, even if 0
  3. `Total TTC` — bold, larger (12px), `border-top: 1px solid #ccc` above it

### 5. TVA Notice
- Left-aligned, small italic gray text
- Shown only when `vat_amount == 0` (micro-entreprise regime): `TVA non applicable, article 293 B du CGI`

### 6. Payment Conditions
- Left-aligned block, 10px
- Shows `payment_conditions` field content (free-text, pre-wrapped)
- If `iban` set on company: appended below as `IBAN : …` (Company model has no `bic` field)
- Section only rendered if any of the above are present

### 7. Signatures
- Two `flex: 1` columns, `gap: 40px`
- Left: `Bon pour accord` / `Signature du client` / `Date : ___`
- Right: `Signature de l'entreprise` / `{company.name}` / `Date : ___`
- Each box has `border-top: 1px solid #ccc` as the signature line

## Technical Approach

### New file
`resources/views/pdf/devis-fr.blade.php` — dedicated template for devis, French locale.

### Updated routing in GeneratePdfAction
```php
$view = match(true) {
    $invoice->isDevis() && $locale === 'fr' => 'pdf.devis-fr',
    $locale === 'fr'                         => 'pdf.invoice-fr',
    $locale === 'uk'                         => 'pdf.invoice-uk',
    default                                  => 'pdf.invoice-fr',
};
```

### invoice-fr.blade.php
Remove devis-specific conditionals that were patched in. Keep the file clean for invoice-only use.

## Data Available in Template

| Variable | Source |
|---|---|
| `$invoice->number` | Invoice model |
| `$invoice->issue_date` | cast: date |
| `$invoice->valid_until` | cast: date, nullable |
| `$invoice->chantier_address` | text, nullable |
| `$invoice->payment_conditions` | text, nullable |
| `$invoice->subtotal` | decimal |
| `$invoice->vat_amount` | decimal |
| `$invoice->total` | decimal |
| `$invoice->currency` | string |
| `$invoice->items` | HasMany InvoiceItem |
| `$item->description` | string |
| `$item->quantity` | decimal:3 |
| `$item->unit` | string |
| `$item->unit_price` | decimal:2 |
| `$item->total_ht` | decimal:2 |
| `$company->name` | string |
| `$company->address` | string |
| `$company->postal_code` | string |
| `$company->city` | string |
| `$company->phone` | string |
| `$company->email` | string |
| `$company->vat_number` (SIRET) | string |
| `$company->iban` | string, nullable |
| `$company->legal_footer` | string, nullable |
| `$invoice->client->name` | string |
| `$invoice->client->address` | string |
| `$invoice->client->postal_code` | string |
| `$invoice->client->city` | string |
| `$invoice->client->phone` | string |

## Out of Scope

- Ukrainian locale devis template (separate task)
- Company logo in PDF
- Invoice template changes beyond removing devis conditionals
