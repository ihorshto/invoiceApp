# 📋 Cahier des charges — InvoiceApp

> Application web de gestion de factures / Додаток для управління рахунками-фактурами
> **Version 1.0 · Mai 2026**

---

## 1. Présentation du projet

| Élément | Détail |
|---|---|
| Nom du projet | InvoiceApp |
| Type | Application web de gestion de factures |
| Langues UI | Ukrainien 🇺🇦 / Français 🇫🇷 |
| Langue PDF | Choisie par facture (indépendant de l'UI) |
| Stack | Laravel 11 · Vue 3 · Inertia.js · Tailwind CSS · MySQL |
| Auth | Sanctum + OTP email (Brevo) |
| Génération PDF | barryvdh/laravel-dompdf |

---

## 2. Authentification

### Flux de connexion (OTP Email)

| Étape | Action | Détail |
|---|---|---|
| 1 | Email + Password | Saisie des identifiants |
| 2 | Vérification credentials | Laravel vérifie en BDD |
| 3 | Génération OTP | Code 6 chiffres, valable 10 min |
| 4 | Envoi email | Via Brevo SMTP |
| 5 | Saisie du code OTP | Page dédiée `/login/verify` |
| 6 | Session créée | Sanctum session-based |

### Règles de sécurité

- OTP expiré après **10 minutes**
- OTP invalidé après utilisation (usage unique)
- Maximum **3 tentatives** incorrectes → blocage 15 min
- Logout → destruction complète de la session Sanctum

---

## 3. Modules fonctionnels

### 3.1 — Paramètres société `/settings/company`

| Champ | Type | Requis      |
|---|---|-------------|
| Nom de la société | string | ✅           |
| Logo | image (png/jpg) | ❌ optionnel |
| Adresse | text | ✅           |
| Code postal + Ville | string | ✅           |
| Pays | string | ✅           |
| Email | email | ✅           |
| Téléphone | string | ❌           |
| Numéro TVA (FR) / ЄДРПОУ (UA) | string | ❌ optionnel |
| IBAN | string | ❌ optionnel |
| Mention légale pied de page | text | ❌           |

> Si logo absent → nom de la société affiché en texte dans le PDF

### 3.2 — Clients `/clients`

**Liste :** tableau paginé · recherche par nom/email · actions rapides

| Champ | Type | Requis    |
|---|---|-----------|
| Nom / Raison sociale | string | ✅         |
| Email | email | ✅         |
| Téléphone | string | ❌         |
| Adresse | text | ✅         |
| Code postal + Ville | string | ✅         |
| Pays | string | ✅         |
| Numéro TVA | string | ❌ |
| Notes internes | text | ❌         |

**Actions :** Créer · Modifier · Supprimer (si aucune facture liée) · Voir factures

### 3.3 — Produits / Services `/products`

**Liste :** tableau paginé · recherche par nom · filtre par catégorie

| Champ | Type | Requis |
|---|---|---|
| Nom | string | ✅ |
| Description | text | ❌ |
| Prix unitaire HT | decimal | ✅ |
| Taux TVA (%) | decimal | ✅ |
| Unité | enum (pièce, heure, jour, kg...) | ✅ |
| Catégorie | string | ❌ |

**Actions :** Créer · Modifier · Supprimer (si non utilisé) · Archiver

---

## 4. Factures

### 4.1 — En-tête de facture

| Champ | Type / Valeur | Requis |
|---|---|---|
| Numéro de facture | auto-généré (FAC-2026-0001) | ✅ |
| Date d'émission | date | ✅ |
| Date d'échéance | date | ✅ |
| Client | select depuis BDD | ✅ |
| Devise | enum (EUR, USD, UAH) | ✅ |
| Langue de la facture | enum (FR, UK) | ✅ |
| Notes / Conditions | text | ❌ |

### 4.2 — Lignes de facture

| Champ | Détail |
|---|---|
| Produit | select depuis BDD ou saisie libre |
| Description | text (pré-rempli depuis produit) |
| Quantité | decimal |
| Prix unitaire HT | decimal |
| Taux TVA (%) | decimal |
| Total HT | calculé automatiquement |

### 4.3 — Calculs automatiques

```
Sous-total HT  =  Σ (quantité × prix unitaire HT)
Total TVA      =  Σ (total HT ligne × taux TVA ligne)
Total TTC      =  Sous-total HT + Total TVA
```

### 4.4 — Statuts de facture

| Statut | Couleur | Description |
|---|---|---|
| `draft` | ⚫ Gris | Brouillon — non envoyé |
| `sent` | 🔵 Bleu | Facture envoyée au client |
| `paid` | 🟢 Vert | Paiement reçu |
| `overdue` | 🔴 Rouge | Échéance dépassée (automatique) |
| `cancelled` | ⚪ Gris clair | Facture annulée |

---

## 5. Génération PDF & Template

### 5.1 — Génération PDF

- Librairie : **barryvdh/laravel-dompdf**
- Font : **DejaVu Sans** (support ukrainien + latin complet)
- Endpoint : `GET /invoices/{id}/pdf`
- Nom fichier : `FAC-2026-0001_NomClient.pdf`
- Langue du PDF = langue choisie lors de la création (indépendant de l'UI)
- Si logo absent → nom de la société affiché en texte

### 5.2 — Template unique (V1)

Un seul design de template pour la V1. La configuration est stockée en JSON dans la table `invoice_templates` en BDD, ce qui permet une extension facile en V2 sans modification du code.

**Structure JSON config :**

```json
{
  "primary_color": "#1E40AF",
  "font":          "DejaVu Sans",
  "show_logo":     true,
  "show_iban":     true,
  "show_tva":      true,
  "show_footer":   true
}
```

---

## 6. Architecture technique

### 6.1 — Base de données

| Table | Clés | Description |
|---|---|---|
| `users` | user_id | Comptes utilisateurs |
| `companies` | company_id → user_id | Réquisits de la société |
| `clients` | client_id → company_id | Clients (CRUD) |
| `products` | product_id → company_id | Produits/services (CRUD) |
| `invoices` | invoice_id → company_id, client_id | Factures |
| `invoice_items` | item_id → invoice_id, product_id? | Lignes de facture |
| `invoice_templates` | template_id, config JSON | Modèles PDF |
| `login_otps` | otp_id → user_id, expires_at | Codes OTP email |

### 6.2 — Approche hybride Services + Actions

| Module | Pattern | Raison |
|---|---|---|
| Clients CRUD | `ClientService` | Simple, 4 méthodes CRUD |
| Products CRUD | `ProductService` | Simple, 4 méthodes CRUD |
| Auth / OTP | Actions | `GenerateOtpAction`, `VerifyOtpAction` |
| Créer facture | `CreateInvoiceAction` | Logique complexe, multi-usage |
| Génération PDF | `GeneratePdfAction` | Réutilisable (controller, job, CLI) |
| Marquer payée | `MarkAsPaidAction` | Déclenche events (future intégration) |

### 6.3 — Structure des dossiers

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
│
resources/
├── js/
│   ├── Pages/
│   │   ├── Auth/            # Login, VerifyOtp
│   │   ├── Invoices/        # Index, Create, Show
│   │   ├── Clients/         # Index, Create, Edit
│   │   ├── Products/        # Index, Create, Edit
│   │   └── Settings/        # Company
│   ├── Components/
│   │   ├── Invoice/
│   │   │   ├── InvoiceForm.vue
│   │   │   └── InvoiceLineItem.vue
│   │   └── UI/
│   │       ├── DataTable.vue
│   │       └── StatusBadge.vue
│   ├── Composables/
│   │   ├── useInvoice.js
│   │   └── useFormatCurrency.js
│   └── i18n/
│       ├── uk.json
│       └── fr.json
└── lang/
    ├── uk/
    └── fr/
```

---

## 7. Sécurité

- Authentification **Sanctum session-based** (adapté Inertia.js)
- OTP email obligatoire à chaque connexion
- Toutes les routes protégées par middleware `auth`
- Isolation des données par **company_id** via Global Scope Eloquent
- Validation côté serveur sur tous les formulaires (**Form Requests**)
- Protection **CSRF** native Inertia
- Blocage après 3 tentatives OTP incorrectes (15 min)

---

## 8. Internationalisation

| Élément | Outil | Fichiers |
|---|---|---|
| UI Vue.js | `vue-i18n` | `uk.json` / `fr.json` |
| Backend (emails) | Laravel lang files | `lang/uk/` · `lang/fr/` |
| PDF généré | Blade templates bilingues | Langue choisie par facture |
| Langue par défaut | Français | — |
| Persistance | `users.locale` (BDD) | Préférence sauvegardée |

---

## 9. Roadmap

### ✅ Version 1 — Scope actuel

- [x] Auth + OTP email (Brevo SMTP)
- [x] Paramètres société (logo optionnel, TVA/ЄДРПОУ)
- [x] CRUD Clients (recherche nom/email, sans filtre pays)
- [x] CRUD Produits/Services
- [x] Création et gestion des factures
- [x] Génération PDF — 1 template (config JSON en BDD)
- [x] UI bilingue Ukrainien / Français

### ◻ Version 2 — Évolutions futures

- [ ] Dashboard statistiques (CA mensuel, factures par statut, top clients)
- [ ] **IA : remplissage de facture par chat ou voix**
  - Whisper API → transcription voix
  - Claude API → extraction données structurées (Function Calling)
  - Pré-remplissage automatique du formulaire Vue.js
  - Utilisateur confirme → génération PDF
- [ ] Envoi facture par email au client
- [ ] Rappels automatiques — factures en retard
- [ ] Devis (quotes) → conversion en facture
- [ ] Multi-utilisateurs + rôles (admin, manager, viewer)
- [ ] Paiement en ligne (Stripe)
- [ ] Import / export Excel
- [ ] API REST publique

---

## 10. Livrables techniques V1

| Livrable | Description |
|---|---|
| Migrations | Toutes les tables avec indexes et foreign keys |
| Seeders | 1 société, 5 clients, 10 produits (données de test) |
| Form Requests | Validation serveur pour chaque module |
| Actions / Services | Architecture modulaire décrite §6.2 |
| Blade Templates PDF | 1 template bilingue UK/FR |
| Vue Pages | Auth, Invoices, Clients, Products, Settings |
| Composables Vue | `useInvoice`, `useFormatCurrency`, `useI18n` |
| Fichiers i18n | `uk.json` + `fr.json` (frontend) · `lang/uk/` + `lang/fr/` (backend) |
