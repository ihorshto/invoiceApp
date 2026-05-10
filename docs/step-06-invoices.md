# Step 6 — Invoices Module

## What was built

Full invoices module with line items, auto-numbering, status transitions, and overdue scheduling.

## Files created

| File | Purpose |
|------|---------|
| `database/migrations/2026_05_09_000004_create_invoices_table.php` | invoices table |
| `database/migrations/2026_05_09_000005_create_invoice_items_table.php` | invoice_items table |
| `app/Models/Invoice.php` | model with HasCompanyScope + helpers |
| `app/Models/InvoiceItem.php` | invoice line model |
| `app/Modules/Invoices/Actions/InvoiceNumberGenerator.php` | INV-YYYY-NNNN numbering |
| `app/Modules/Invoices/Actions/CreateInvoiceAction.php` | create invoice + items, calc totals |
| `app/Modules/Invoices/Actions/UpdateInvoiceAction.php` | replace items, recalc |
| `app/Modules/Invoices/Actions/MarkAsPaidAction.php` | set status=paid + paid_at |
| `app/Modules/Invoices/Services/InvoiceService.php` | list with search/status filter |
| `app/Http/Controllers/InvoiceController.php` | resource + mark-paid + pdf routes |
| `app/Console/Commands/MarkOverdueInvoices.php` | daily overdue marking |
| `resources/js/Composables/useInvoiceItems.js` | shared line-items logic |
| `resources/js/Pages/Invoices/Index.vue` | list + search + status filter |
| `resources/js/Pages/Invoices/Create.vue` | form with product picker + live totals |
| `resources/js/Pages/Invoices/Edit.vue` | same as Create, pre-filled |
| `resources/js/Pages/Invoices/Show.vue` | read-only view + mark-paid + PDF download |
| `database/factories/InvoiceFactory.php` | factory |
| `database/factories/InvoiceItemFactory.php` | factory |
| `tests/Feature/InvoiceTest.php` | 7 tests |

## Schemas

```
invoices: id, company_id, client_id, number (unique), status (draft/sent/paid/overdue/cancelled),
          issue_date, due_date, subtotal, vat_amount, total, currency, notes, footer, pdf_path,
          paid_at, timestamps

invoice_items: id, invoice_id, product_id (nullable), description, unit_price, unit, quantity,
               vat_rate, total_ht, total_ttc, sort_order, timestamps
```

## Key decisions

- Invoice number format: `INV-YYYY-NNNN` (sequential per company per year).
- Totals are calculated server-side in CreateInvoiceAction/UpdateInvoiceAction — frontend
  live-preview uses `useInvoiceItems` composable but server is authoritative.
- `UpdateInvoiceAction` deletes all items and re-creates — simpler than diff-based sync.
- `isEditable()` returns true for draft/sent statuses — controls UI button visibility.
- Scheduler (`invoices:mark-overdue`) runs daily at 01:00 via `routes/console.php`.

## Routes added

```php
Route::resource('invoices', InvoiceController::class);
Route::post('invoices/{invoice}/mark-paid', [InvoiceController::class, 'markPaid'])->name('invoices.mark-paid');
Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');
```

## Tests (7 passing)

- `test_index_is_accessible`
- `test_invoice_can_be_created`
- `test_invoice_totals_are_calculated_correctly`
- `test_invoice_number_is_unique_and_sequential`
- `test_invoice_can_be_marked_paid`
- `test_draft_invoice_can_be_deleted`
- `test_required_fields_validation`

## Commit

`feat(invoices): add invoices module with CRUD, totals, and overdue scheduler`
