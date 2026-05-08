# Крок 3 — Company Settings

## Що зроблено

### Таблиця `companies`
| Поле | Тип | Обов'язкове |
|---|---|---|
| name | string | ✅ |
| logo_path | string | ❌ nullable |
| address | text | ✅ |
| postal_code + city + country | string | ✅ |
| email | string | ✅ |
| phone | string | ❌ |
| vat_number (TVA/ЄДРПОУ) | string | ❌ |
| iban | string | ❌ |
| legal_footer | text | ❌ |

### Файли
| Файл | Роль |
|---|---|
| `app/Models/Company.php` | Модель з HasFactory |
| `app/Models/Concerns/HasCompanyScope.php` | Trait для ізоляції по company_id (буде використовуватись у Client, Product, Invoice) |
| `app/Http/Controllers/Settings/CompanyController.php` | GET /settings/company · POST · DELETE logo |
| `app/Http/Requests/UpdateCompanyRequest.php` | Server-side validation |
| `resources/js/Pages/Settings/Company.vue` | Білінгвальна форма з preview логотипу |
| `database/factories/CompanyFactory.php` | Для тестів |

### Логотип
- Зберігається в `storage/app/public/logos/`
- Доступний через `/storage/logos/filename.png`
- `php artisan storage:link` вже виконано (symlink `/public/storage → storage/app/public`)

### Тести
- 6 feature tests, 24 assertions — всі проходять
