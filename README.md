<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Deploy to Vercel (serverless, no credit card)

The repo ships with a Vercel-ready setup: `vercel.json` + `api/index.php`
(vercel-php runtime) + environment template `deploy/.env.vercel.example`.

### 1. Database — Neon (free, email signup only)

1. Sign up at <https://console.neon.tech> (no credit card) and create a project.
   Pick a region close to the Vercel region (Frankfurt, Washington DC, or
   Singapore depending on where you sign up from).
2. Copy the **connection string** from *Connection Details* — it looks like
   `postgresql://user:password@host.neon.tech/neondb?sslmode=require`.

### 2. App — Vercel (free)

1. Push this repo to GitHub, then sign up at <https://vercel.com> (no card).
2. **Add New Project** → import the `ayyanet-ticketing` repo.
3. Do **not** pick a framework preset — `vercel.json` in the repo configures the
   PHP function and the `npm run build` step automatically.
4. In **Project → Settings → Environment Variables**, set (full list in
   `deploy/.env.vercel.example`):

   | Key | Value |
   | --- | --- |
   | `APP_KEY` | output of `php artisan key:generate --show` |
   | `APP_ENV` | `production` |
   | `APP_DEBUG` | `false` |
   | `APP_URL` | `https://<project>.vercel.app` |
   | `DB_CONNECTION` | `pgsql` |
   | `DB_URL` | the Neon connection string |
   | `SESSION_DRIVER` / `CACHE_STORE` / `QUEUE_CONNECTION` | `database` |
   | `LOG_CHANNEL` | `stderr` (the function disk is read-only) |
   | `APP_SEED` | `true` — **first deploy only**, then remove (seeder wipes data) |

5. **Deploy.** Migrations run automatically on every deploy via the composer
   `vercel` script (seeding only when `APP_SEED=true`). First login after
   seeding: `test@example.com` / `password` (admin).
6. After the first successful deploy, delete the `APP_SEED` variable and deploy
   again — never leave seeding on, it wipes tickets/customers.

### Serverless specifics

- The function filesystem is **read-only except `/tmp`**; the app already runs
  with `SESSION_DRIVER=database`, `CACHE_STORE=database`,
  `QUEUE_CONNECTION=database`, `LOG_CHANNEL=stderr` and compiled views in
  `/tmp` (via `VIEW_COMPILED_PATH`), so no disk writes are needed.
- Queues don't run on serverless — jobs that rely on the worker simply wait.
  This app's jobs only email notifications, so mail is unaffected until the
  worker is running elsewhere.
- Cold starts take a few seconds on the free tier; Neon free also sleeps after
  5 min idle and wakes on the first query.

## Alternative: Deploy to Koyeb (free, no credit card)

The repo ships with a Koyeb-ready Docker setup in `deploy/` (nginx + php-fpm +
queue worker in a single container; migrations run automatically on boot) and a
Neon Postgres reference in `deploy/.env.production.example`. A Render Blueprint
(`render.yaml`) is also included as an alternative if you later get a card.

### 1. Database — Neon (free, email signup only)

1. Sign up at <https://console.neon.tech> (no credit card) and create a project.
   Pick a region close to the Koyeb one you'll choose (Frankfurt or Washington DC).
2. Copy the **connection string** from *Connection Details* — it looks like
   `postgresql://user:password@host.neon.tech/neondb?sslmode=require`.

### 2. App — Koyeb (free)

1. Sign up at <https://app.koyeb.com> (no credit card) and link your GitHub.
2. **Create Web Service** → select the `ayyanet-ticketing` repo.
3. Builder: **Dockerfile** — set the Dockerfile path to `deploy/Dockerfile`.
4. Region: `Frankfurt` (or `Washington, D.C.`), instance type: **Free**.
5. Env vars (see `deploy/.env.production.example` for the full list):

   | Key | Value |
   | --- | --- |
   | `APP_KEY` | output of `php artisan key:generate --show` |
   | `APP_ENV` | `production` |
   | `APP_DEBUG` | `false` |
   | `APP_URL` | `https://<app>-<org>.koyeb.app` (what Koyeb assigns) |
   | `DB_CONNECTION` | `pgsql` |
   | `DB_URL` | the Neon connection string |
   | `SESSION_DRIVER` / `QUEUE_CONNECTION` / `CACHE_STORE` | `database` |
   | `APP_SEED` | `true` — first boot only, then remove (seeder wipes data) |

6. Deploy and watch the logs — migrations run automatically on boot.
   First login after seeding: `test@example.com` / `password` (admin).

### Free-tier limits to expect

- The Koyeb service **sleeps after 1 hour without traffic** and wakes on the
  next visit. Queue workers sleep too, so jobs only run while the site is awake.
- The **filesystem is ephemeral** (lost on restart/redeploy). This app keeps no
  local uploads, so it's safe here.
- Neon free: **0.5 GB storage, 100 compute-hours/month**, sleeps after 5 min
  idle (first query after idle takes a few seconds). Idle usage costs ~0 hours,
  so a small demo stays comfortably inside the limits.
- One free Koyeb web service per account; it can only run in Frankfurt or
  Washington, D.C. No card is required on the free tier.

### Email (optional)

Koyeb free instances allow outbound SMTP, so the standard Resend setup works:
`MAIL_MAILER=smtp`, `MAIL_HOST=smtp.resend.com`, `MAIL_PORT=587`,
`MAIL_USERNAME=resend`, `MAIL_PASSWORD=re_...`, `MAIL_ENCRYPTION=tls`, plus a
verified `MAIL_FROM_ADDRESS`. Until set, mail falls back to the `log` mailer.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://github.com/laravel/framework/blob/11.x/CODE_OF_CONDUCT.md).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
