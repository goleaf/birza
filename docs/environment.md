# Environment Guide

Birza reads environment values through Laravel config. Do not read `env()` directly from runtime code outside config files.

## Local Defaults

The safe local defaults in `.env.example` are:

```env
APP_NAME=Birza
APP_ENV=local
APP_DEBUG=true
APP_URL=https://birza.test
DB_CONNECTION=sqlite
DB_DATABASE=birza.sqlite
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
MAIL_MAILER=log
DEBUGBAR_ENABLED=false
```

## Application

| Variable | Required | Notes |
| --- | --- | --- |
| `APP_NAME` | yes | App and mail display name. |
| `APP_ENV` | yes | `local`, `testing`, or `production`. |
| `APP_KEY` | yes | Generate with `php artisan key:generate`. |
| `APP_DEBUG` | yes | `true` locally, `false` in production. |
| `APP_URL` | yes | Used for links, storage URLs, CORS, and Sanctum defaults. |
| `ASSET_URL` | no | Optional asset CDN/base URL. |
| `MAINTENANCE_BYPASS_SECRET` | no | Required before using the custom maintenance close command. |
| `CORS_ALLOWED_ORIGINS` | yes | Defaults to `APP_URL`. |
| `VAT_RATE` | no | Defaults to `0.21` in `config/app.php`. |

Configured locales currently live in `config/app.php`, not `.env`:

```php
'locale' => 'lt',
'locales' => ['lt', 'en'],
'fallback_locale' => 'en',
```

## Database

Local SQLite:

```env
DB_CONNECTION=sqlite
DB_DATABASE=birza.sqlite
DB_FOREIGN_KEYS=true
```

MySQL example:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=birza
DB_USERNAME=root
DB_PASSWORD=
```

## Storage

| Variable | Default | Notes |
| --- | --- | --- |
| `FILESYSTEM_DISK` | `local` | Laravel default disk. Product image actions use the `public` disk where required. |
| `AWS_*` | empty | Only needed if S3 is intentionally enabled. |

Run:

```bash
php artisan storage:link
```

## Queue

| Variable | Default | Notes |
| --- | --- | --- |
| `QUEUE_CONNECTION` | `sync` | Local default. |
| `QUEUE_FAILED_DRIVER` | config default | Optional if changing queue failure storage. |

With `sync`, no worker is required. If production uses an async driver, run a supervised queue worker.

## Mail

Local log mail:

```env
MAIL_MAILER=log
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

Mailpit SMTP:

```env
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
```

Never commit real SMTP credentials.

## Sanctum And Vite

Sanctum is installed. Current app routes are mostly web-guard based, but these variables are safe placeholders:

```env
SANCTUM_STATEFUL_DOMAINS=birza.test,localhost,127.0.0.1
SANCTUM_TOKEN_PREFIX=
```

Vite variables:

```env
VITE_APP_NAME="${APP_NAME}"
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

Pusher values are placeholders only unless realtime broadcasting is intentionally enabled.

## Marketplace

| Variable | Default | Notes |
| --- | --- | --- |
| `MARKETPLACE_LOW_STOCK_THRESHOLD` | `5` | Seller low-stock alert threshold. |
| `MARKETPLACE_ALLOW_GUEST_PRODUCT_REPORTS` | `true` | Allows guest reports with email address. |
| `MARKETPLACE_PRODUCT_REPORT_RATE_LIMIT_PER_HOUR` | `5` | Per-reporter/product report throttle. |

## Production-Safe Values

```env
APP_ENV=production
APP_DEBUG=false
DEBUGBAR_ENABLED=false
MAIL_MAILER=smtp
QUEUE_CONNECTION=sync
```

Use a real mail transport in production. Use an async queue worker only if the queue connection is changed from `sync`.
