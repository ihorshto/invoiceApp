# Крок 1 — Project Setup

## Що зроблено

- Laravel 11.51 + Breeze (Vue 3 + Inertia.js, без SSR)
- Docker Compose (`compose.yaml`) з 4 сервісами:
  - `app` — PHP 8.5 + Node 20 (порти 80, 5173)
  - `mysql:8.4` — основна БД на порті **3308** (уникає конфлікту з іншими контейнерами)
  - `mysql_test:8.4` — тестова БД на порті 3309 (tmpfs / RAM)
  - `redis:7-alpine` — черги, кеш, сесії на порті 6379
- `APP_SERVICE=app` (перейменовано з `laravel.test`)
- `phpunit.xml` — тести використовують `mysql_test`
- `vite.config.js` — HMR з Docker (host: 0.0.0.0, usePolling: true)
- `.env` — Redis для queue/cache/session, Brevo SMTP

## Команди для запуску

```bash
# Запустити контейнери
./vendor/bin/sail up -d

# Зупинити контейнери
./vendor/bin/sail down

# Frontend в режимі розробки (HMR)
./vendor/bin/sail npm run dev

# Запустити тести
./vendor/bin/sail artisan test
```

## Важливо

- MAIL_USERNAME і MAIL_PASSWORD у `.env` залишені порожніми — додати Brevo credentials
- Порт MySQL: **3308** (не 3306, щоб не конфліктувати з іншими локальними контейнерами)
