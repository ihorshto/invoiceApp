# 🧾 InvoiceApp

> Application web de gestion de factures / Додаток для управління рахунками-фактурами

![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?style=flat&logo=laravel&logoColor=white)
![Vue.js](https://img.shields.io/badge/Vue.js-3-42B883?style=flat&logo=vue.js&logoColor=white)
![Inertia.js](https://img.shields.io/badge/Inertia.js-1.x-7C3AED?style=flat)
![Tailwind CSS](https://img.shields.io/badge/Tailwind-3-0EA5E9?style=flat&logo=tailwindcss&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8-00758F?style=flat&logo=mysql&logoColor=white)

---

## 📋 Description

InvoiceApp est une application web bilingue (🇫🇷 Français / 🇺🇦 Ukrainien) permettant de créer, gérer et exporter des factures professionnelles au format PDF.

**Fonctionnalités principales (V1) :**
- Authentification sécurisée avec OTP par email
- Gestion des clients et produits/services
- Création de factures avec calcul automatique (HT, TVA, TTC)
- Génération PDF avec support des caractères ukrainiens
- Interface bilingue FR/UK avec `vue-i18n`

---

## 🛠 Stack technique

| Couche | Technologie |
|--------|-------------|
| Backend | Laravel 11 |
| Frontend | Vue 3 + Inertia.js |
| Style | Tailwind CSS |
| Base de données | MySQL 8 |
| Auth | Laravel Sanctum + OTP email |
| PDF | barryvdh/laravel-dompdf |
| Email | Brevo (SMTP) |
| I18n Frontend | vue-i18n |

---

## 🚀 Installation

### Prérequis

- PHP 8.2+
- Composer
- Node.js 20+
- MySQL 8+

### 1. Cloner le projet

```bash
git clone https://github.com/your-username/invoice-app.git
cd invoice-app
```

### 2. Installer les dépendances

```bash
composer install
npm install
```

### 3. Configuration de l'environnement

```bash
cp .env.example .env
php artisan key:generate
```

Éditer le fichier `.env` :

```env
APP_NAME=InvoiceApp
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=invoice_app
DB_USERNAME=root
DB_PASSWORD=

# Brevo SMTP
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your@email.com
MAIL_PASSWORD=your_brevo_key
MAIL_FROM_ADDRESS=noreply@invoiceapp.com
MAIL_FROM_NAME="InvoiceApp"

# OTP
OTP_EXPIRES_MINUTES=10
OTP_MAX_ATTEMPTS=3
OTP_BLOCK_MINUTES=15

# Langue par défaut
APP_LOCALE=fr
```

### 4. Base de données

```bash
php artisan migrate
php artisan db:seed
```

### 5. Démarrer l'application

```bash
# Terminal 1 — Laravel
php artisan serve

# Terminal 2 — Vite (hot reload)
npm run dev
```

---

## 🗂 Structure du projet

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
│   │   ├── Auth/           # Login, VerifyOtp
│   │   ├── Invoices/       # Index, Create, Show
│   │   ├── Clients/        # Index, Create, Edit
│   │   ├── Products/       # Index, Create, Edit
│   │   └── Settings/       # Company
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

## 🗃 Base de données

```
users            Comptes utilisateurs
companies        Réquisits de la société (→ user_id)
clients          Clients (→ company_id)
products         Produits / services (→ company_id)
invoices         Factures (→ company_id, client_id)
invoice_items    Lignes de facture (→ invoice_id, product_id?)
invoice_templates Modèles PDF avec config JSON
login_otps       Codes OTP (→ user_id, expires_at)
```

---

## 🔐 Authentification

Flux de connexion en deux étapes :

```
Email + Password  →  OTP généré (6 chiffres, 10 min)
                  →  Envoyé par email (Brevo)
                  →  Saisie sur /login/verify
                  →  Session Sanctum créée
```

Règles :
- OTP valable **10 minutes**, usage unique
- Blocage après **3 tentatives** incorrectes (15 min)

---

## 🧾 Statuts de facture

| Statut | Description |
|--------|-------------|
| `draft` | Brouillon — non envoyé |
| `sent` | Envoyée au client |
| `paid` | Paiement reçu |
| `overdue` | Échéance dépassée (automatique) |
| `cancelled` | Annulée |

---

## 🌍 Internationalisation

| Élément | Outil | Fichiers |
|---------|-------|---------|
| UI Vue | `vue-i18n` | `resources/js/i18n/uk.json`, `fr.json` |
| Backend / emails | Laravel lang | `resources/lang/uk/`, `lang/fr/` |
| PDF | Blade bilingue | Langue choisie par facture |

Changer la langue dans l'interface → préférence sauvegardée en BDD (`users.locale`).

---

## 📄 Génération PDF

```bash
GET /invoices/{id}/pdf
```

- Font **DejaVu Sans** (support ukrainien + latin)
- Nom fichier : `FAC-2026-0001_NomClient.pdf`
- Logo optionnel — si absent, nom de la société en texte
- Config du template stockée en JSON dans `invoice_templates`

---

## 🏗 Architecture

### Approche hybride Services + Actions

```
Services  →  CRUD simple (Clients, Products)
Actions   →  Logique métier complexe (Invoices, Auth)
```

### Events disponibles

```php
InvoiceCreated   // après création
InvoiceUpdated   // après modification
MarkAsPaid       // après paiement
```

---

## 🗺 Roadmap

### ✅ V1 — En cours

- [x] Auth + OTP email
- [x] Paramètres société
- [x] CRUD Clients
- [x] CRUD Produits
- [x] Création / gestion factures
- [x] Génération PDF (1 template, config JSON)
- [x] UI bilingue UK / FR

### ◻ V2 — Futures évolutions

- [ ] Dashboard statistiques
- [ ] **Remplissage facture par IA** (chat ou voix)
  - Whisper API → transcription voix
  - Claude API → extraction données structurées
  - Pré-remplissage automatique du formulaire
- [ ] Envoi facture par email au client
- [ ] Rappels automatiques (factures en retard)
- [ ] Devis → conversion en facture
- [ ] Multi-utilisateurs + rôles (admin, manager, viewer)
- [ ] Paiement en ligne (Stripe)
- [ ] API REST publique

---

## 📜 Licence

Projet privé — tous droits réservés.
