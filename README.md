# 🎫 Ayyanet Ticketing System

Sistem manajemen tiket keluhan & bantuan pelanggan (Helpdesk / CRM) berbasis Laravel dengan dukungan antarmuka modern, manajemen peran (Admin/Staf/Pelanggan), notifikasi real-time, serta integrasi Bot Telegram interaktif.

---

## 🚀 Fitur Utama

- **Manajemen Tiket Lengkap**: Pembuatan tiket, penetapan teknisi/staf, update status (Open, In Progress, Resolved, Closed), serta prioritas tiket.
- **Dashboard & Analisis**: Ringkasan tiket, visualisasi grafik status, serta panel riwayat tiket terakhir diakses (*Recently Visited*).
- **Multi-Role & Akses**:
  - **Admin**: Akses penuh mengelola tiket, status banner, pengguna, dan konfigurasi sistem.
  - **Customer Service (CS)**: Mencatat tiket dari pelanggan (Email, WhatsApp, Live Chat, Telegram) dan memantau penyelesaiannya.
  - **Teknisi Lapangan**: Menangani dan merespon tiket yang ditugaskan.
  - Sistem bersifat internal/privat: hanya staf yang dapat masuk; pelanggan tidak memiliki akun atau akses.
- **Status Banner**: Pengumuman pemeliharaan atau gangguan layanan terpusat.
- **Integrasi Bot Telegram**:
  - Notifikasi otomatis ke grup/admin saat ada tiket baru atau pembaruan status.
  - Bot interaktif (polling / webhook) untuk cek tiket dan update status langsung via chat Telegram.
- **Pesan Tiket**: Catatan dan pesan antar staf di dalam detail tiket (tidak ada chat publik untuk pelanggan).

---

## 💻 Panduan Instalasi & Penggunaan Lokal

### 1. Prasyarat Sistem
- PHP >= 8.2 (dengan ekstensi `pdo`, `mbstring`, `openssl`, `curl`, dll)
- Composer
- Node.js & NPM
- SQLite / MySQL / PostgreSQL

### 2. Langkah Instalasi

1. **Clone Repository:**
   ```bash
   git clone https://github.com/Rey-CpuU/ayyanet-ticketing.git
   cd ayyanet-ticketing
   ```

2. **Install Dependensi:**
   ```bash
   composer install
   npm install
   ```

3. **Konfigurasi Environment:**
   Salin file `.env.example` menjadi `.env`:
   ```bash
   cp .env.example .env
   ```
   Generate application key:
   ```bash
   php artisan key:generate
   ```

4. **Migrasi Database & Seeder:**
   ```bash
   # Buat database sqlite jika menggunakan driver sqlite default
   touch database/database.sqlite
   
   # Jalankan migrasi dan seeder data awal
   php artisan migrate --seed
   ```
   > 🔑 **Akun Default Seeder:**
   > - **Email**: `test@example.com`
   > - **Password**: `password`
   > - **Role**: `Admin`

5. **Build Asset & Jalankan Aplikasi:**
   ```bash
   # Terminal 1: Vite Dev Server
   npm run dev

   # Terminal 2: Laravel Local Server
   php artisan serve
   ```
   Akses aplikasi di browser melalui: `http://localhost:8000`

---

## 🤖 Cara Menggunakan Bot Telegram

Aplikasi ini mendukung dua mode operasional bot Telegram:

### 1. Konfigurasi `.env`
Tambahkan konfigurasi bot pada file `.env`:
```dotenv
TELEGRAM_BOT_TOKEN="your_bot_token_from_botfather"
TELEGRAM_ALLOWED_CHAT_IDS="12345678,87654321"
TELEGRAM_DEFAULT_ADMIN_ID="12345678"

# Untuk Notifikasi Grup/Channel
TELEGRAM_NOTIF_BOT_TOKEN="your_notif_bot_token"
TELEGRAM_NOTIF_GROUP_ID="-100xxxxxxxxx"
TELEGRAM_NOTIF_ENABLED=true
```

### 2. Menjalankan Bot di Lingkungan Lokal (Polling Mode)
Untuk testing atau menjalankan bot di localhost tanpa perlu URL HTTPS publik:
```bash
php artisan telegram:poll
```

### 3. Menguji Notifikasi Telegram
Untuk memastikan konfigurasi notifikasi bot Telegram sudah benar:
```bash
php artisan telegram:notif-test
```

### 4. Mode Webhook (Production)
Set `TELEGRAM_WEBHOOK_SECRET` (string acak) terlebih dahulu: Telegram akan mengirimkannya di header
`X-Telegram-Bot-Api-Secret-Token` dan endpoint menolak request tanpa secret yang cocok. Lalu daftarkan
webhook bot ke domain publik (HTTPS):
```bash
php artisan telegram:webhook set https://domain-anda.com/telegram/webhook
```

### 5. Hak Akses Bot
- Hanya akun staf (`admin`, `cs`, `lapangan`) yang bisa login ke bot; percobaan password dibatasi
  5x per 15 menit per chat.
- Bot memakai policy yang sama dengan web: menambah customer dan membuat tiket hanya untuk `admin`/`cs`.
- Tiket dari bot dibuat lewat alur yang sama dengan form web (`TicketWorkflow`): nomor `TKT-0001`,
  SLA sesuai prioritas, activity log, serta notifikasi email/in-app/Telegram.

---

## 🔐 Peran, Akun & SLA

- **Registrasi publik ditutup.** Admin mengundang user dari menu *Users* (email + role); user mengisi
  nama & password sendiri lewat link undangan (berlaku 24 jam). Admin pertama bisa dipromosikan via CLI:
  ```bash
  php artisan user:make-admin email@contoh.com
  ```
- **Role:** `admin` (akses penuh), `cs` (tiket, customer, laporan, status banner), `lapangan`
  (hanya tiket yang ditugaskan/dibuat sendiri). Hak akses diatur oleh policy (`TicketPolicy`, `CustomerPolicy`).
- **Alur status:** Open → Checking / Waiting Customer / Escalated → Solved → Closed (reopen ke Open).
  Solved wajib catatan penyelesaian. Jam SLA berhenti saat *Waiting Customer*.
- **SLA:** batas waktu per prioritas diatur lewat `SLA_HIGH`, `SLA_MEDIUM`, `SLA_LOW` (default 5h/8h/24h).
  Tiket yang lewat batas ditandai oleh `php artisan tickets:check-sla`, dijadwalkan tiap 5 menit —
  jalankan `php artisan schedule:work` (image Docker sudah menjalankannya lewat supervisor).
- **Zona waktu:** timestamp disimpan dalam `APP_TIMEZONE` (default `UTC`, sama dengan data production).

---

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
