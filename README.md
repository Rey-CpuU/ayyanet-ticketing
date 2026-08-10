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

## Deploy to Render (free)

The repo ships with a `render.yaml` Blueprint (web service + Postgres, both on
Render's free tier) and a Docker setup in `deploy/` (nginx + php-fpm + queue
worker in a single container; migrations run automatically on boot).

### One-time setup

1. Push this repo to GitHub (already linked at `github.com/Rey-CpuU/ayyanet-ticketing`).
2. In the Render Dashboard: **New + → Blueprint** → pick the repo. Render reads
   `render.yaml` and provisions a free Postgres + free web service.
3. When prompted for **APP_KEY**, paste the output of:

   ```bash
   php artisan key:generate --show
   ```

   The key must stay stable across restarts, so don't use `generateValue`.
4. If Render assigns a different subdomain than `ayyanet-ticketing.onrender.com`,
   update `APP_URL` on the service in the dashboard.
5. For demo data on the first boot, add `APP_SEED=true` as an environment
   variable, deploy, then **remove it** (the seeder wipes tickets/customers on
   every run). After that, log in with `test@example.com` / `password` (admin).

### Free-tier limits to expect

- The service **spins down after 15 min of inactivity**; the next visit takes
  ~1 minute to wake. Queue workers sleep too, so mail/jobs only run while the
  site is awake.
- The **filesystem is ephemeral** (lost on restart/redeploy). This app keeps no
  local uploads, so it's safe here.
- The free Postgres is **1 GB and expires 30 days after creation** (14-day
  grace to upgrade, then it's deleted). Upgrade it in the dashboard to keep data.
- 750 free instance-hours/month per workspace; the queue worker shares the web
  instance, so no extra hours are consumed.

### Email (optional)

Render free web services cannot send SMTP on ports 25/465/587. Resend offers
alternate ports: use `MAIL_PORT=2587` with `MAIL_ENCRYPTION=tls` (or `2465`
with `ssl`). Until set, mail falls back to the `log` mailer. See
`deploy/.env.render.example` for the full reference.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://github.com/laravel/framework/blob/11.x/CODE_OF_CONDUCT.md).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
