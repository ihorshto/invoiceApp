# Invoice Status Change Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Let the user change an invoice's status to any of the 5 values via a dropdown on the Show page.

**Architecture:** New `PATCH /invoices/{invoice}/status` endpoint in `InvoiceController::updateStatus()` handles validation and `paid_at` side-effects. On the frontend, the static status badge in `Show.vue` is replaced with a styled `<select>` that fires the PATCH on change. The existing "Marquer payée" button is removed — the dropdown covers that case.

**Tech Stack:** Laravel 11, Inertia.js, Vue 3, PHPUnit

---

## File Map

| File | Change |
|---|---|
| `routes/web.php` | Add `PATCH invoices/{invoice}/status` inside EnsureUserHasCompany group |
| `app/Http/Controllers/InvoiceController.php` | Add `updateStatus()` method |
| `tests/Feature/InvoiceStatusTest.php` | New test class — 4 test cases |
| `resources/js/Pages/Invoices/Show.vue` | Replace badge with `<select>`, remove markPaid |
| `lang/fr/invoices.php` | Add `action.change_status` |
| `lang/uk/invoices.php` | Add `action.change_status` |

---

### Task 1: Backend — route, controller method, and tests

**Files:**
- Modify: `routes/web.php`
- Modify: `app/Http/Controllers/InvoiceController.php`
- Create: `tests/Feature/InvoiceStatusTest.php`

- [ ] **Step 1: Create the test file**

```php
<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceStatusTest extends TestCase
{
    use RefreshDatabase;

    private function makeSetup(): array
    {
        $user    = User::factory()->create();
        $company = Company::factory()->create(['user_id' => $user->id]);
        $client  = Client::factory()->create(['company_id' => $company->id]);
        return [$user, $company, $client];
    }

    public function test_status_can_be_changed(): void
    {
        [$user, $company, $client] = $this->makeSetup();
        $invoice = Invoice::factory()->create([
            'company_id' => $company->id,
            'client_id'  => $client->id,
            'status'     => 'draft',
        ]);

        $this->actingAs($user)
            ->patch(route('invoices.status', $invoice), ['status' => 'sent'])
            ->assertRedirect(route('invoices.show', $invoice));

        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'status' => 'sent']);
    }

    public function test_changing_to_paid_sets_paid_at(): void
    {
        [$user, $company, $client] = $this->makeSetup();
        $invoice = Invoice::factory()->create([
            'company_id' => $company->id,
            'client_id'  => $client->id,
            'status'     => 'sent',
        ]);

        $this->actingAs($user)
            ->patch(route('invoices.status', $invoice), ['status' => 'paid']);

        $this->assertNotNull(Invoice::find($invoice->id)->paid_at);
    }

    public function test_changing_from_paid_clears_paid_at(): void
    {
        [$user, $company, $client] = $this->makeSetup();
        $invoice = Invoice::factory()->create([
            'company_id' => $company->id,
            'client_id'  => $client->id,
            'status'     => 'paid',
            'paid_at'    => now(),
        ]);

        $this->actingAs($user)
            ->patch(route('invoices.status', $invoice), ['status' => 'sent']);

        $this->assertNull(Invoice::find($invoice->id)->paid_at);
    }

    public function test_invalid_status_is_rejected(): void
    {
        [$user, $company, $client] = $this->makeSetup();
        $invoice = Invoice::factory()->create([
            'company_id' => $company->id,
            'client_id'  => $client->id,
            'status'     => 'draft',
        ]);

        $this->actingAs($user)
            ->patch(route('invoices.status', $invoice), ['status' => 'bogus'])
            ->assertSessionHasErrors('status');
    }
}
```

- [ ] **Step 2: Run tests — confirm all 4 FAIL**

```bash
./vendor/bin/sail artisan test tests/Feature/InvoiceStatusTest.php
```

Expected: 4 failures — route `invoices.status` does not exist.

- [ ] **Step 3: Add route to `routes/web.php`**

Inside the `EnsureUserHasCompany` middleware group, after the existing `mark-paid` line:

```php
Route::post('invoices/{invoice}/mark-paid', [InvoiceController::class, 'markPaid'])->name('invoices.mark-paid');
Route::patch('invoices/{invoice}/status', [InvoiceController::class, 'updateStatus'])->name('invoices.status');
Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');
```

- [ ] **Step 4: Add `updateStatus()` to `InvoiceController`**

Add after the `markPaid()` method:

```php
public function updateStatus(Request $request, Invoice $invoice): RedirectResponse
{
    $data = $request->validate([
        'status' => ['required', 'in:draft,sent,paid,overdue,cancelled'],
    ]);

    $updates = ['status' => $data['status']];

    if ($data['status'] === 'paid') {
        $updates['paid_at'] = now();
    } elseif ($invoice->status === 'paid') {
        $updates['paid_at'] = null;
    }

    $invoice->update($updates);

    return redirect()->route('invoices.show', $invoice);
}
```

- [ ] **Step 5: Run tests — confirm all 4 PASS**

```bash
./vendor/bin/sail artisan test tests/Feature/InvoiceStatusTest.php
```

Expected: 4 passed.

- [ ] **Step 6: Commit**

```bash
git add routes/web.php app/Http/Controllers/InvoiceController.php tests/Feature/InvoiceStatusTest.php
git commit -m "feat(invoices): add PATCH status endpoint with paid_at side-effects"
```

---

### Task 2: Frontend — replace status badge with dropdown in Show.vue

**Files:**
- Modify: `resources/js/Pages/Invoices/Show.vue`

- [ ] **Step 1: Remove `markPaid` and add `changeStatus` in the `<script setup>` block**

Replace:
```js
const markPaid = () => {
    if (confirm(t('invoices.confirm_paid'))) {
        router.post(route('invoices.mark-paid', props.invoice.id))
    }
}
```

With:
```js
const changeStatus = (newStatus) => {
    router.patch(route('invoices.status', props.invoice.id), { status: newStatus })
}
```

- [ ] **Step 2: Replace the status badge `<span>` with a `<select>`**

Replace:
```html
<span :class="statusCls[invoice.status]" class="px-2 py-0.5 rounded-full text-xs font-medium">
    {{ t('invoices.statuses.' + invoice.status) }}
</span>
```

With:
```html
<select
    :value="invoice.status"
    @change="changeStatus($event.target.value)"
    :class="statusCls[invoice.status]"
    class="px-2 py-0.5 rounded-full text-xs font-medium border-0 cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500"
>
    <option v-for="s in ['draft', 'sent', 'paid', 'overdue', 'cancelled']" :key="s" :value="s">
        {{ t('invoices.statuses.' + s) }}
    </option>
</select>
```

- [ ] **Step 3: Remove the "Marquer payée" button**

Remove this block entirely:
```html
<button v-if="['sent','overdue'].includes(invoice.status)" @click="markPaid"
    class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-lg text-sm">
    {{ t('invoices.action.mark_paid') }}
</button>
```

- [ ] **Step 4: Manual verification**

Open `http://localhost/invoices/{any-id}` in the browser.
- The status badge is now a colored dropdown
- Selecting a different status reloads the page with the new status and correct color
- Selecting `paid` sets a green badge
- The "Marquer payée" button is gone

- [ ] **Step 5: Commit**

```bash
git add resources/js/Pages/Invoices/Show.vue
git commit -m "feat(invoices): replace status badge with interactive dropdown on Show page"
```

---

### Task 3: Translations

**Files:**
- Modify: `lang/fr/invoices.php`
- Modify: `lang/uk/invoices.php`

- [ ] **Step 1: Add key to `lang/fr/invoices.php`**

In the `'action'` array, add:
```php
'action' => [
    'add_line'       => '+ Ajouter ligne',
    'mark_paid'      => 'Marquer payée',
    'change_status'  => 'Changer le statut',
    'view'           => 'Voir',
],
```

- [ ] **Step 2: Add key to `lang/uk/invoices.php`**

In the `'action'` array, add:
```php
'action' => [
    'add_line'       => '+ Додати рядок',
    'mark_paid'      => 'Оплачено',
    'change_status'  => 'Змінити статус',
    'view'           => 'Огляд',
],
```

- [ ] **Step 3: Commit**

```bash
git add lang/fr/invoices.php lang/uk/invoices.php
git commit -m "feat(i18n): add change_status translation key for invoices"
```
