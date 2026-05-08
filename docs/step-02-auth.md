# Крок 2 — Auth + OTP

## Що зроблено

### Флоу авторизації
```
POST /login (email + password)
  → LoginController: перевірка credentials через Hash::check
  → GenerateOtpAction: генерує 6-значний код, хешує (bcrypt), зберігає в login_otps
  → SendOtpEmailJob → Brevo SMTP → OtpMail
  → session('otp_user_id') → redirect /login/verify

POST /login/verify (code)
  → OtpController → VerifyOtpAction
  → перевірка Redis-блокування (3 спроби → 15 хв)
  → перевірка expires_at (10 хв)
  → Hash::check(code, otp->code)
  → otp->used_at = now()
  → Auth::login($user) → session regenerate → /dashboard
```

### Файли
| Файл | Роль |
|---|---|
| `app/Modules/Auth/Actions/GenerateOtpAction.php` | Генерує і хешує OTP, dispatches Job |
| `app/Modules/Auth/Actions/VerifyOtpAction.php` | Перевіряє OTP, Redis throttle |
| `app/Jobs/SendOtpEmailJob.php` | Queue job для відправки email |
| `app/Mail/OtpMail.php` | Mailable з шаблоном |
| `app/Models/LoginOtp.php` | Модель з isExpired/isUsed |
| `app/Http/Controllers/Auth/LoginController.php` | Крок 1: credentials |
| `app/Http/Controllers/Auth/OtpController.php` | Крок 2: OTP verify |
| `resources/js/Pages/Auth/Login.vue` | Сторінка входу |
| `resources/js/Pages/Auth/VerifyOtp.vue` | Сторінка вводу коду |

### Безпека
- OTP зберігається **хешованим** (bcrypt)
- OTP одноразовий (`used_at` після успіху)
- Блокування після 3 невірних спроб — Redis lock 15 хв
- Сесія регенерується після успішної авторизації

### Тести
- 8 feature tests, 34 assertions — всі проходять
- Примітка: `PDO::MYSQL_ATTR_SSL_CA` deprecation warning (PHP 8.3 + MySQL 8.4) — не впливає на функціональність

## Що потрібно зробити після деплою
1. Додати `MAIL_USERNAME` і `MAIL_PASSWORD` (Brevo credentials) у `.env`
2. Запустити queue worker: `sail artisan queue:work`
