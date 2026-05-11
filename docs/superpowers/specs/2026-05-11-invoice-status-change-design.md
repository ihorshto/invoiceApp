# Invoice Status Change — Design Spec

**Date:** 2026-05-11  
**Status:** Approved

## Problem

Currently the only way to change an invoice status is the "Marquer payée" button (only for `sent`/`overdue`). The user wants to freely set any of the 5 statuses at any time.

## Solution

Replace the static status badge on the Show page with a styled `<select>` dropdown. Any status → any status is allowed. Backed by a new `PATCH /invoices/{id}/status` endpoint.

## Backend

**New route** (`routes/web.php`):
```
PATCH invoices/{invoice}/status   InvoiceController@updateStatus   invoices.status
```

**New method** `InvoiceController::updateStatus(Request $request, Invoice $invoice)`:
- Validates: `status` ∈ `['draft', 'sent', 'paid', 'overdue', 'cancelled']`
- If transitioning **to** `paid` → set `paid_at = now()`
- If transitioning **away from** `paid` → set `paid_at = null`
- Redirects back to `invoices.show`

No new Action class needed — the logic is simple enough for the controller method.

## Frontend (Show.vue)

**Status badge → styled select:**
- Replace `<span :class="statusCls[invoice.status]">` with `<select>` that carries the same color class based on current value
- On `change` → `router.patch(route('invoices.status', invoice.id), { status: selectedStatus })`
- No confirm dialog needed (any status change is reversible)

**Remove "Marquer payée" button** — the dropdown covers this case.

**Keep "Edit" button** logic unchanged (`draft` / `sent` only).

## Translations

Add `invoices.action.change_status` key to FR and UK lang files (used as `<select>` aria-label / screen-reader label — not visible but good practice).

## Scope

| File | Change |
|---|---|
| `routes/web.php` | Add `PATCH invoices/{invoice}/status` route |
| `app/Http/Controllers/InvoiceController.php` | Add `updateStatus()` method |
| `resources/js/Pages/Invoices/Show.vue` | Replace badge with select, remove markPaid button |
| `lang/fr/invoices.php` | Add `action.change_status` |
| `lang/uk/invoices.php` | Add `action.change_status` |

## Verification

1. Open any invoice → status dropdown is visible with current value highlighted
2. Change to any other status → page reloads with new status and correct color
3. Change to `paid` → `paid_at` is set in DB
4. Change from `paid` to `sent` → `paid_at` is cleared
5. "Marquer payée" button is gone
