# Locale Middleware Fix Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Fix the language switcher so FR/UK buttons actually change the interface language.

**Architecture:** The root cause is that `HandleInertiaRequests` is registered before `SetLocale` in the middleware stack. Since `HandleInertiaRequests::share()` reads `app()->getLocale()` before `SetLocale` has a chance to set it from the user's DB record, the frontend always receives the app default locale. Swapping the order fixes the bug with a single two-line change.

**Tech Stack:** Laravel 11, Inertia.js, PHPUnit

---

### Task 1: Write a failing feature test that proves the bug

**Files:**
- Create: `tests/Feature/LocaleMiddlewareTest.php`

- [ ] **Step 1: Create the test file**

```php
<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_inertia_shares_user_locale_from_database(): void
    {
        $user = User::factory()->create(['locale' => 'uk']);
        Company::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertInertia(fn ($page) => $page->where('locale', 'uk'));
    }
}
```

- [ ] **Step 2: Run the test and confirm it FAILS**

```bash
./vendor/bin/sail artisan test tests/Feature/LocaleMiddlewareTest.php --env=testing
```

Expected: **FAIL** — the assertion `locale = 'uk'` fails because the shared locale is `'fr'` (the app default).

---

### Task 2: Fix the middleware order

**Files:**
- Modify: `bootstrap/app.php`

- [ ] **Step 1: Swap the two middleware entries**

In `bootstrap/app.php`, change the `append` array from:

```php
$middleware->web(append: [
    \App\Http\Middleware\HandleInertiaRequests::class,
    \App\Http\Middleware\SetLocale::class,
    \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
]);
```

To:

```php
$middleware->web(append: [
    \App\Http\Middleware\SetLocale::class,
    \App\Http\Middleware\HandleInertiaRequests::class,
    \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
]);
```

- [ ] **Step 2: Run the test and confirm it PASSES**

```bash
./vendor/bin/sail artisan test tests/Feature/LocaleMiddlewareTest.php --env=testing
```

Expected: **PASS** — `locale = 'uk'` is now correctly shared by Inertia.

- [ ] **Step 3: Run the full test suite to check for regressions**

```bash
./vendor/bin/sail artisan test --env=testing
```

Expected: all tests pass.

- [ ] **Step 4: Commit**

```bash
git add bootstrap/app.php tests/Feature/LocaleMiddlewareTest.php
git commit -m "fix(i18n): run SetLocale before HandleInertiaRequests so locale is shared correctly"
```

---

### Manual Verification

- [ ] Open the app in the browser (`http://localhost`)
- [ ] Log in and click **UK** → page reloads in Ukrainian
- [ ] Refresh → still Ukrainian
- [ ] Click **FR** → switches to French and persists after refresh
