# Installation Guide

This guide installs Birza from zero for local development.

## Requirements

- PHP 8.3 or newer.
- Composer 2.
- Node.js 20+ recommended and npm.
- Git.
- SQLite with `pdo_sqlite`, or a configured MySQL/PostgreSQL/SQL Server database.
- Writable `storage/` and `bootstrap/cache/`.
- Laravel Herd is the easiest local environment for this repository.

## Clone And Install

```bash
git clone <repository-url> birza
cd birza
composer install
npm install
cp .env.example .env
php artisan key:generate
```

## Local SQLite Setup

`.env.example` is configured for SQLite:

```env
DB_CONNECTION=sqlite
DB_DATABASE=birza.sqlite
```

Create the local database file:

```bash
touch database/birza.sqlite
```

Laravel resolves that filename to `database/birza.sqlite`.

## Database And Storage

```bash
php artisan migrate
php artisan db:seed
php artisan storage:link
```

For a full local reset:

```bash
php artisan migrate:fresh --seed
```

`migrate:fresh --seed` deletes local data. Do not use it in production.

## Frontend Assets

Development:

```bash
npm run dev
```

Production build check:

```bash
npm run build
```

## Open The App

With Laravel Herd:

```text
https://birza.test
```

Without Herd:

```bash
php artisan serve
```

If using `php artisan serve`, set a matching URL in `.env`:

```env
APP_URL=http://127.0.0.1:8000
SESSION_SECURE_COOKIE=false
```

## Verify The Install

```bash
php artisan route:list --except-vendor
php artisan migrate:status --no-interaction
php artisan test --compact tests/Feature/Translations/TranslationFilesTest.php
npm run build
```

## Troubleshooting

- Missing Vite manifest: run `npm run build`, or keep `npm run dev` running while developing.
- Missing images: run `php artisan storage:link`.
- SQLite database not found: create `database/birza.sqlite`.
- Login redirects unexpectedly: confirm the account is active, verified where required, and using the correct guard area.
- Mail not visible locally: keep `MAIL_MAILER=log`, or switch to Mailpit SMTP and inspect Mailpit.
