# Fix: Language Switcher — Middleware Order Bug

**Date:** 2026-05-11  
**Status:** Approved

## Problem

Clicking the FR/UK language switcher does nothing — the interface stays in the same language.

**Root cause:** In `bootstrap/app.php`, `HandleInertiaRequests` is appended before `SetLocale`. Since `HandleInertiaRequests::share()` calls `app()->getLocale()` before `SetLocale` has had a chance to run, the frontend always receives the application default locale (`fr`), regardless of what is saved in the user's `locale` column.

**Execution order (broken):**
```
Request → HandleInertiaRequests::share() [locale = app default] → SetLocale [locale set too late] → Controller
```

## Solution

Swap the two middleware entries in `bootstrap/app.php` so `SetLocale` runs first:

**Execution order (fixed):**
```
Request → SetLocale [reads user.locale from DB, sets app locale] → HandleInertiaRequests::share() [locale = correct] → Controller
```

## Scope

- **Change:** `bootstrap/app.php` — swap `SetLocale` and `HandleInertiaRequests` in the `append` array.
- **No changes** to `LanguageController`, `SetLocale`, `HandleInertiaRequests`, `useI18n.js`, or routes — all are correct.

## Verification

1. Log in, click UK button → page reloads in Ukrainian.
2. Refresh the page → still Ukrainian.
3. Click FR → switches back to French and persists.
