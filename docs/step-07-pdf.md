# Step 7 — PDF Generation

## What was built

PDF invoice generation using barryvdh/laravel-dompdf with a bilingual (French/Ukrainian) Blade template.

## Files created

| File | Purpose |
|------|---------|
| `app/Modules/Invoices/Actions/GeneratePdfAction.php` | streams PDF to browser |
| `resources/views/pdf/invoice.blade.php` | Blade template for PDF |
| `config/dompdf.php` | dompdf configuration |
| `tests/Feature/InvoicePdfTest.php` | 1 test: verifies %PDF output |

## Template design

- A4 paper with DejaVu Sans font (supports Cyrillic/Ukrainian characters)
- Sections: header (company + invoice number + status badge), parties (emitter + recipient),
  dates row, items table with line totals, totals block (HT / TVA / TTC), notes/footer,
  IBAN and legal footer from company settings
- Inline CSS only (dompdf limitation)
- Status badge with color per status

## Route

```
GET /invoices/{invoice}/pdf → InvoiceController::pdf → GeneratePdfAction::stream()
```

## Cyrillic support

DejaVu Sans is bundled with dompdf and covers Unicode including Ukrainian. Font metrics are
cached in `storage/fonts/` (auto-created on first render).

## Install

```bash
sail composer require barryvdh/laravel-dompdf
sail artisan vendor:publish --provider="Barryvdh\\DomPDF\\ServiceProvider"
```

## Test

```bash
sail artisan test tests/Feature/InvoicePdfTest.php --no-coverage
```

## Commit

`feat(pdf): add PDF invoice generation with dompdf`
