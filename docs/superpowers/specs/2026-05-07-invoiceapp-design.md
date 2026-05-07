# InvoiceApp — Design Spec
**Date:** 2026-05-07  
**Version:** 1.0  
**Status:** Approved

---

## 1. Overview

Web application for managing invoices, bilingual (French / Ukrainian). Built with Laravel 11 + Vue 3 + Inertia.js + Tailwind CSS. Fully containerized via Docker / Laravel Sail.

---

## 2. Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 11 |
| Frontend | Vue 3 + Inertia.js (no SSR) |
| Styling | Tailwind CSS |
| Database | MySQL 8.0 |
| Cache / Queue / Session | Redis 7 (`redis:7-alpine`) |
| Auth | Laravel Sanctum (session-based) + OTP email |
| Email | Brevo SMTP (local + production) |
| PDF | barryvdh/laravel-dompdf + DejaVu Sans |
| i18n Frontend | vue-i18n (`uk.json` / `fr.json`) |
| i18n Backend | Laravel lang files (`lang/uk/` / `lang/fr/`) |
| Starter kit | Laravel Breeze (Vue + Inertia preset) |

---

## 3. Docker Architecture

**5 services in `docker-compose.yml`:**

| Service | Image | Port | Purpose |
|---|---|---|---|
| `app` | Sail PHP 8.2 | 80, 5173 | Laravel app + Vite HMR (inside container) |
| `mysql` | mysql:8.0 | 3306 | Main database (persistent volume) |
| `mysql_test` | mysql:8.0 | 3307 | Test database (tmpfs / RAM) |
| `redis` | redis:7-alpine | 6379 | Queue + Cache + Session |
| Brevo SMTP | external | 587 | OTP emails (not a container) |

**Key config:**
- `QUEUE_CONNECTION=redis` — OTP emails dispatched as jobs
- `CACHE_STORE=redis`
- `SESSION_DRIVER=redis`
- `phpunit.xml` points to `mysql_test` (DB_HOST=mysql_test, port 3306)
- Vite HMR runs on port 5173 inside `app` container

---

## 4. Database Schema

### 4.1 Tables

| Table | FK | Notes |
|---|---|---|
| `users` | — | `locale` enum(fr,uk) |
| `companies` | user_id | logo_path nullable, vat_number nullable, iban nullable |
| `login_otps` | user_id | code hashed, expires_at, attempts tinyint, used_at nullable |
| `clients` | company_id | Global Scope by company_id |
| `products` | company_id | archived_at nullable, unit enum |
| `invoices` | company_id, client_id | number unique (FAC-2026-0001), status enum, currency enum, locale enum |
| `invoice_items` | invoice_id, product_id? | product_id nullable (free-text allowed), total_ht stored computed |
| `invoice_templates` | company_id | config JSON, is_default boolean |

### 4.2 Eloquent Relations

```
users → companies (hasOne)
companies → clients, products, invoices, invoice_templates (hasMany)
invoices → invoice_items (hasMany)
invoice_items → products (belongsTo, nullable)
```

### 4.3 Global Scope

All models scoped by `company_id` via Eloquent Global Scope — data isolation between companies.

### 4.4 Computed field

`invoice_items.total_ht = quantity × unit_price` — stored on write, not recalculated on read.

---

## 5. Authentication Flow

```
POST /login (email + password)
  → validate credentials
  → GenerateOtpAction: 6-digit code, hashed, expires 10 min
  → dispatch SendOtpEmailJob → Brevo SMTP
  → redirect to /login/verify

POST /login/verify (code)
  → VerifyOtpAction
    → check expiry (10 min)
    → check attempts (max 3 → block 15 min via Redis)
    → mark used_at, create Sanctum session
  → redirect to /dashboard
```

**Security rules:**
- OTP hashed (not plain text in DB)
- OTP single-use (`used_at` set on success)
- Max 3 wrong attempts → Redis lock 15 min
- Logout destroys full Sanctum session

---

## 6. Module Architecture

### 6.1 Pattern: Services + Actions

| Module | Pattern | Reason |
|---|---|---|
| Clients CRUD | `ClientService` | Simple 4-method CRUD |
| Products CRUD | `ProductService` | Simple 4-method CRUD + archive |
| Auth / OTP | Actions | `GenerateOtpAction`, `VerifyOtpAction` |
| Create invoice | `CreateInvoiceAction` | Complex multi-step logic |
| Generate PDF | `GeneratePdfAction` | Reusable (controller, job, CLI) |
| Mark as paid | `MarkAsPaidAction` | Fires events for future integrations |

### 6.2 Folder Structure

```
app/
├── Modules/
│   ├── Auth/
│   │   └── Actions/
│   │       ├── GenerateOtpAction.php
│   │       └── VerifyOtpAction.php
│   ├── Invoices/
│   │   ├── Actions/
│   │   │   ├── CreateInvoiceAction.php
│   │   │   ├── GeneratePdfAction.php
│   │   │   └── MarkAsPaidAction.php
│   │   └── Services/
│   │       └── InvoiceNumberGenerator.php
│   ├── Clients/
│   │   └── Services/
│   │       └── ClientService.php
│   └── Products/
│       └── Services/
│           └── ProductService.php
```

---

## 7. Invoice Logic

### 7.1 Number generation

Format: `FAC-{YEAR}-{SEQUENCE}` (e.g. `FAC-2026-0001`)  
Sequence is per-company, per-year, auto-incremented.

### 7.2 Statuses

| Status | Color | Trigger |
|---|---|---|
| `draft` | Grey | Default on create |
| `sent` | Blue | Manual action |
| `paid` | Green | `MarkAsPaidAction` |
| `overdue` | Red | Laravel Scheduler (daily check) |
| `cancelled` | Light grey | Manual action |

### 7.3 Calculations

```
total_ht (per line) = quantity × unit_price
subtotal_ht         = Σ total_ht
total_vat           = Σ (total_ht × vat_rate)
total_ttc           = subtotal_ht + total_vat
```

---

## 8. PDF Generation

- Library: `barryvdh/laravel-dompdf`
- Font: DejaVu Sans (Ukrainian + Latin support)
- Endpoint: `GET /invoices/{id}/pdf`
- Filename: `FAC-2026-0001_ClientName.pdf`
- Language: set per invoice (independent of UI locale)
- Logo: displayed if present; company name as text if absent
- Template config: stored as JSON in `invoice_templates`
- V1: single template design

---

## 9. i18n

| Element | Tool | Files |
|---|---|---|
| Vue UI | `vue-i18n` | `resources/js/i18n/uk.json`, `fr.json` |
| Backend / emails | Laravel lang | `lang/uk/`, `lang/fr/` |
| PDF | Blade bilingual template | Language chosen per invoice |
| Default language | French | — |
| Persistence | `users.locale` in DB | Saved preference |

---

## 10. Implementation Order (7 Steps)

| Step | Module | Deliverable |
|---|---|---|
| 1 | Project Setup | Docker, Sail, Breeze, .env, vite config, phpunit.xml |
| 2 | Auth + OTP | Migrations, Actions, Mailable, Redis throttle, Vue pages |
| 3 | Company Settings | Migration, Model, logo upload, Vue page |
| 4 | Clients | Migration, ClientService, Vue pages (Index/Create/Edit) |
| 5 | Products | Migration, ProductService, archive logic, Vue pages |
| 6 | Invoices | Migrations, Actions, scheduler, Vue pages, composables |
| 7 | PDF + i18n | dompdf, Blade template, vue-i18n, lang files |

Each step produces:
- Migrations + Models
- Form Requests (server-side validation)
- Controller + Routes
- Vue pages / components
- `docs/step-0N-<name>.md` documentation file

---

## 11. Security

- Sanctum session-based (Inertia-compatible)
- OTP required every login
- All routes protected by `auth` middleware
- Data isolation by `company_id` via Global Scope
- Server-side validation on all forms (Form Requests)
- CSRF protection via Inertia
- OTP blocked after 3 wrong attempts (15 min, Redis)
- OTP code stored hashed

---

## 12. Out of Scope (V1)

- Dashboard statistics
- Email invoice to client
- Automatic overdue reminders (email)
- Quotes → invoice conversion
- Multi-user / roles
- Stripe payments
- Excel import/export
- Public REST API
- AI invoice fill (V2)
