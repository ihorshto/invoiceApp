# InvoiceApp — Redesign Design Spec

**Date:** 2026-05-15  
**Status:** Approved

---

## Overview

Full visual redesign of the InvoiceApp using a Design System–first approach. The goal is a consistent, modern SaaS aesthetic across all pages — Desktop full-screen layout with a Wide Sidebar, responsive Drawer navigation on mobile, Teal/Mint color palette, Inter font, and Clean/Bordered component style.

---

## Design Decisions

| Parameter | Decision |
|---|---|
| Style | Modern SaaS |
| Approach | Design System first, then apply to all pages |
| Layout | Full-screen, no max-width container |
| Navigation (desktop) | Wide Sidebar (220px) with icons + text labels |
| Navigation (mobile) | Drawer (slide-in overlay), triggered by ☰ hamburger button |
| Color palette | Teal / Mint — light green sidebar, teal accent, white content area |
| Font | Inter (Google Fonts) |
| Components | Clean / Bordered — 1px `#e2e8f0` borders, 6–8px border-radius, minimal shadows |
| Responsive breakpoint | `lg:` sidebar visible, below `lg`: sidebar hidden, drawer enabled |

---

## Color Palette

| Token | Value | Usage |
|---|---|---|
| `sidebar-bg` | `#f0fdfa` | Sidebar background |
| `sidebar-border` | `#ccfbf1` | Sidebar right border, dividers |
| `accent` | `#14b8a6` | Active nav item, primary buttons, links |
| `accent-hover` | `#0d9488` | Button hover, link hover |
| `accent-light` | `#f0fdfa` | Hover backgrounds in nav |
| `text-primary` | `#134e4a` | Headings, nav logo, key text |
| `text-secondary` | `#0d9488` | Inactive nav items |
| `text-muted` | `#64748b` | Subtitles, table secondary text |
| `content-bg` | `#ffffff` | Main content area background |
| `border` | `#e2e8f0` | Card borders, table dividers, inputs |
| `surface` | `#f8fafc` | Table header bg, input bg |

### Status Badge Colors

| Status | Background | Text | Border |
|---|---|---|---|
| Оплачено | `#dcfce7` | `#16a34a` | `#bbf7d0` |
| Відправлено | `#fef9c3` | `#854d0e` | `#fde68a` |
| Прострочено | `#fee2e2` | `#dc2626` | `#fecaca` |
| Чернетка | `#f1f5f9` | `#475569` | `#e2e8f0` |

---

## Typography

- **Font:** Inter (via Google Fonts CDN or local install)
- **Page title:** 18px, weight 700, color `#134e4a`
- **Section subtitle:** 12px, weight 400, color `#64748b`
- **Table header:** 11px, weight 600, uppercase, letter-spacing 0.04em, color `#64748b`
- **Table body:** 13px, weight 400, color `#1e293b`
- **Nav items:** 13px, weight 500 (inactive) / 600 (active)
- **Badges:** 11px, weight 600

---

## Layout

### Desktop (≥ lg)

```
┌─────────────────────────────────────────────────┐
│ Sidebar (220px) │ Main content (flex: 1)         │
│                 │                                 │
│  Logo           │  Topbar (title + action btn)    │
│  ─────────      │  ─────────────────────────────  │
│  Nav items      │  Page content (scrollable)      │
│                 │                                 │
│  [spacer]       │                                 │
│  User chip      │                                 │
└─────────────────────────────────────────────────┘
```

### Mobile (< lg)

- Sidebar hidden
- Topbar shows: ☰ hamburger | Page title | User avatar
- ☰ opens a Drawer overlay (slide-in from left, 220px wide, same sidebar content)
- Tapping outside the drawer closes it

---

## Pages & Sections

### Dashboard (existing page — populate with stats)

**Route:** `/dashboard` (already registered)  
**Nav label:** Dashboard (📊)  
**Purpose:** Financial overview — replaces the stats that were on the Invoices page. Currently the page is empty.

**Sections:**
1. **Stats row** — 4 cards in a grid (4 cols desktop, 2 cols mobile):
   - Всього виставлено (total amount + count)
   - Оплачено (paid amount + count + %)
   - Очікує оплати (pending amount + count + %)
   - Прострочено (overdue amount + count + %)
2. **Charts row** — 2 cards side by side (stack on mobile):
   - Bar chart: monthly revenue (last 6 months)
   - Donut chart: invoice status breakdown

### Invoices (updated)

**Route:** `/invoices`  
**Change:** Remove stats cards. Add toolbar row.

**Toolbar row** (single flex row):
- Search input (`flex: 1`) — full available width
- Filter buttons (fixed width, right side): Всі | Оплачені | Відправлені | Прострочені | Чернетки

**Table columns:** №, Клієнт, Дата, Сума, Статус, (action link)

On mobile: table becomes a list of cards (one card per invoice, showing №, client, amount, status badge).

### Clients, Products, Settings

Apply the same design system (sidebar, topbar, border/color tokens) without structural changes. Search + filter toolbar on Index pages follows the same pattern as Invoices.

---

## Component Library (Design System)

### AuthenticatedLayout.vue

Central layout component. Full rewrite — removes the current `max-w-7xl` container constraint so the layout is truly full-screen. Renders the sidebar on desktop and the drawer on mobile. All authenticated pages are wrapped in this layout.

**Sidebar structure:**
- Logo block (icon + "InvoiceApp" text)
- Nav section with `nav-label` group headers and `nav-item` links
- Footer with user chip (avatar + name + email)

### Shared Components to Update

| Component | Change |
|---|---|
| `PrimaryButton.vue` | `bg-teal-500`, `hover:bg-teal-600`, `rounded-md` (6px) |
| `SecondaryButton.vue` | `border border-gray-200`, `bg-gray-50`, `hover:bg-teal-50` |
| `TextInput.vue` | `border-gray-200`, `focus:border-teal-400`, `rounded-md`, `bg-gray-50` |
| `InputLabel.vue` | `text-xs font-semibold text-slate-600 uppercase tracking-wide` |
| `Modal.vue` | `rounded-lg`, border `border-gray-200`, no heavy shadow |

### New Component: `StatusBadge.vue`

Reusable badge that maps invoice status → color tokens above. Accepts `status` prop.

### New Component: `PageHeader.vue`

Renders topbar: title (left) + optional action slot (right). Used on every page.

---

## Responsive Breakpoints

| Breakpoint | Behavior |
|---|---|
| `lg` (1024px+) | Sidebar always visible, drawer hidden |
| `md` (768–1023px) | Sidebar hidden, drawer available, stats grid 2-col |
| `sm` (< 768px) | Single column, invoice table → card list, stats grid 2-col |

---

## Implementation Approach

**Design System first** — changes flow in this order:

1. Install Inter font (Google Fonts in `app.blade.php`)
2. Configure Tailwind custom colors (`teal`, `mint` tokens in `tailwind.config.js`)
3. Rewrite `AuthenticatedLayout.vue` — full sidebar + mobile drawer
4. Update shared components (`PrimaryButton`, `TextInput`, `InputLabel`, `Modal`)
5. Create `StatusBadge.vue` and `PageHeader.vue`
6. Create `Dashboard.vue` page with stats + charts
7. Update `Invoices/Index.vue` — remove stats, add toolbar row
8. Apply layout to `Clients`, `Products`, `Settings` pages
9. Responsive audit — test all breakpoints

---

## Out of Scope

- Dark mode
- Animations / transitions (beyond basic CSS hover)
- Chart library integration (charts are placeholders in this spec — can be added later with Chart.js or similar)
- Changes to PDF invoice design
- Changes to Auth pages (Login, OTP)