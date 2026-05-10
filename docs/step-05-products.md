# Step 5 — Products Module

## What was built

Full CRUD module for products/services that can be added to invoice line items.

## Files created

| File | Purpose |
|------|---------|
| `database/migrations/2026_05_09_000003_create_products_table.php` | products table |
| `app/Models/Product.php` | Eloquent model with HasCompanyScope |
| `app/Modules/Products/Services/ProductService.php` | list/create/update/archive/delete |
| `app/Http/Controllers/ProductController.php` | resource controller |
| `app/Http/Requests/StoreProductRequest.php` | validation rules |
| `resources/js/Pages/Products/Index.vue` | table + search + active/archived badge |
| `resources/js/Pages/Products/Create.vue` | create form |
| `resources/js/Pages/Products/Edit.vue` | edit form |
| `database/factories/ProductFactory.php` | factory for tests |
| `tests/Feature/ProductTest.php` | 6 feature tests |

## Schema

```
products
  id, company_id (FK), name, description (nullable),
  unit_price (decimal 10,2), unit (default 'unité'),
  vat_rate (decimal 5,2, default 20.00), is_active (bool, default true),
  timestamps
```

## Key decisions

- `is_active` flag allows soft-archiving products without deletion.
- `ProductService::list()` orders active products first, then by name.
- `invoiceItems()` relation uses FQCN to guard against future Invoice-module dependency.

## Tests (6 passing)

- `test_index_is_accessible`
- `test_product_can_be_created`
- `test_product_can_be_updated`
- `test_product_can_be_deleted`
- `test_search_filters_by_name`
- `test_required_fields_validation`

## Commands run

```bash
sail artisan migrate
sail npm run build
sail artisan test tests/Feature/ProductTest.php --no-coverage
```

## Commit

`feat(products): add products CRUD module`
