# Step 4 — Clients Module

## What was built

Full CRUD module for managing clients (companies that receive invoices).

## Files created

| File | Purpose |
|------|---------|
| `database/migrations/2026_05_09_000002_create_clients_table.php` | clients table |
| `app/Models/Client.php` | Eloquent model with HasCompanyScope |
| `app/Modules/Clients/Services/ClientService.php` | list/create/update/delete |
| `app/Http/Controllers/ClientController.php` | resource controller |
| `app/Http/Requests/StoreClientRequest.php` | validation rules |
| `resources/js/Pages/Clients/Index.vue` | table + debounced search + pagination |
| `resources/js/Pages/Clients/Create.vue` | create form |
| `resources/js/Pages/Clients/Edit.vue` | edit form |
| `database/factories/ClientFactory.php` | factory for tests |
| `tests/Feature/ClientTest.php` | 6 feature tests |

## Key decisions

- `HasCompanyScope` global scope (defined in Step 3) is applied to Client — all queries
  are automatically scoped to the authenticated user's company.
- `ClientService::list()` uses `withoutGlobalScopes()` to avoid double-scope and manually
  adds `where('company_id', $company->id)` — allows paginated search without conflicts.
- `hasInvoices()` uses `class_exists()` guard so the method is safe before Invoice model
  exists (Step 6). Will be a real DB check once invoices migration runs.
- `@vueuse/core` was added (`npm install @vueuse/core`) for `useDebounceFn` in Index.vue.

## Routes added (`routes/web.php`)

```php
Route::resource('clients', ClientController::class)->except(['show']);
```

## Tests (6 passing)

- `test_index_is_accessible` — GET /clients → 200 + Inertia component
- `test_client_can_be_created` — POST /clients → 302 + DB record
- `test_client_can_be_updated` — PUT /clients/{id} → 302 + updated name
- `test_client_without_invoices_can_be_deleted` — DELETE /clients/{id} → 302 + DB missing
- `test_search_filters_by_name` — GET /clients?search=Alpha → 1 result
- `test_required_fields_validation` — POST /clients with empty data → validation errors

## Commands run

```bash
sail artisan migrate
sail npm install @vueuse/core
sail npm run build
sail artisan test tests/Feature/ClientTest.php --no-coverage
```

## Commit

`feat(clients): add clients CRUD module`
