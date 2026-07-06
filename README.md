# CRM AI Platform

> **Nexus CRM** — A predictive sales platform that automates follow-ups and identifies opportunities.

⚠️ **This is proprietary software. See [LICENSE](./LICENSE) for details. No copying, modification, or distribution is permitted.**

---

## Tech Stack

| Layer       | Technology                                              |
| :---------- | :------------------------------------------------------ |
| Backend     | PHP 8.3+, Laravel 12, Inertia.js v2                    |
| Frontend    | Vue 3, TypeScript, Tailwind CSS v4                     |
| Database    | SQLite (default) / MySQL / PostgreSQL                  |
| Dev Tools   | Vite 7, Laravel Pint, Pest v3, ESLint 9, Prettier 3   |
| Auth        | Laravel Socialite (Google OAuth), Session-based        |

---

## Prerequisites

- **PHP** >= 8.3 (with `exif` and `gd` extensions enabled)
- **Composer** v2
- **Node.js** >= 20 & **npm**
- **SQLite** (default) or MySQL/PostgreSQL

---

## First-Time Setup

```bash
# 1. Clone the repository
git clone <your-repo-url>
cd ai-crm-demo

# 2. Copy environment configuration
cp .env.example .env

# 3. Install PHP dependencies
composer install

# 4. Generate application key
php artisan key:generate

# 5. Create SQLite database & run migrations with seed data
# (Creates demo users, customers, leads, deals, and tasks)
php artisan migrate --seed

# 6. Install Node.js dependencies
npm install
```

---

## Starting the Application

### Full Stack (Recommended)
Starts the PHP server, queue worker, log watcher, and Vite dev server simultaneously:

```bash
composer run dev
```

This runs all services concurrently on:
- **Backend**: `http://127.0.0.1:8000`
- **Vite Dev Server**: `http://127.0.0.1:5173`

### Individual Services

```bash
# Backend only
php artisan serve

# Frontend only (requires backend running)
npm run dev

# Production build
npm run build
```

---

## Demo Credentials

After running `php artisan migrate --seed`, the following demo accounts are available:

| Role          | Email                | Password   |
| :------------ | :------------------- | :--------- |
| Super Admin   | admin@admin.com      | 12345678   |
| Sales Manager | manager@admin.com    | 12345678   |
| Sales Rep     | employee@crm.com     | 12345678   |

### Login URLs
- **Admin Panel**: `/admin/login`
- **Customer Portal**: `/login`

---

## Project Structure

```
├── app/                    # Laravel backend (Models, Controllers, etc.)
├── config/                 # Configuration files
│   └── branding/           # Theme & color customization
├── database/
│   ├── factories/          # Model factories for testing
│   ├── migrations/         # Database schema migrations
│   └── seeders/            # Demo data seeders
├── resources/
│   └── js/
│       ├── Components/     # Reusable Vue components
│       ├── Core/           # Core utilities
│       ├── Layouts/        # Page layouts (Admin, Guest, Customer, Public)
│       ├── Pages/          # Vue page components (Inertia)
│       │   ├── Admin/      # Admin panel pages
│       │   ├── Customer/   # Customer portal pages
│       │   ├── Guest/      # Public-facing pages
│       │   └── Public/     # Shared public pages
│       ├── Shared/         # Shared modules
│       ├── lib/            # Utility libraries
│       └── types/          # TypeScript type definitions
├── routes/                 # Laravel route definitions
├── tests/                  # Pest test suite
├── design.md               # Frontend design specifications
└── vite.config.ts          # Vite bundler configuration
```

---

## Useful Commands

```bash
# Run tests
php artisan test --compact

# Format PHP code
vendor/bin/pint --dirty

# Format frontend code
npm run format

# Lint frontend
npm run lint

# Fresh database reset with seed data
php artisan migrate:fresh --seed
```

---

## Environment Variables

All secrets are stored in `.env` (git-ignored). Key variables:

| Variable               | Purpose                        |
| :--------------------- | :----------------------------- |
| `APP_KEY`              | Application encryption key     |
| `DB_CONNECTION`        | Database driver (sqlite/mysql) |
| `GOOGLE_CLIENT_ID`     | Google OAuth Client ID         |
| `GOOGLE_CLIENT_SECRET` | Google OAuth Client Secret     |
| `MAIL_*`               | Email configuration            |
| `AWS_*`                | AWS S3 storage (optional)      |

---

## License

**Proprietary — All Rights Reserved.**
See [LICENSE](./LICENSE) for the full terms. This software may not be copied, modified, or distributed without explicit written permission.
