# Production Notes

This guide documents production-safe defaults and deployment checks. It does not replace a hosting-specific runbook.

## Environment

Required production values:

```env
APP_ENV=production
APP_DEBUG=false
DEBUGBAR_ENABLED=false
APP_KEY=<secret-generated-key>
APP_URL=https://your-domain.example
```

Do not commit `.env` or secrets.

## Install And Build

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

## Database

```bash
php artisan migrate --force
```

Do not run:

```bash
php artisan migrate:fresh
```

in production.

## Storage

```bash
php artisan storage:link
```

Verify:

- `storage/` is writable by the web server user.
- `bootstrap/cache/` is writable by the web server user.
- public uploads are backed up.
- private files are not exposed through public paths.

## Cache Optimization

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Use `php artisan optimize:clear` during deployment troubleshooting or before rebuilding caches.

## Queue

Current safe default:

```env
QUEUE_CONNECTION=sync
```

If changing to an async driver, run a supervised worker:

```bash
php artisan queue:work
```

Recommended process managers:

- Supervisor
- systemd

Laravel Horizon is not installed and should not be documented as available unless Redis/Horizon are intentionally added.

## Mail

Use a real production mail transport and keep credentials in `.env` or hosting secrets:

```env
MAIL_MAILER=smtp
MAIL_HOST=<smtp-host>
MAIL_PORT=587
MAIL_USERNAME=<secret>
MAIL_PASSWORD=<secret>
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=<verified-from-address>
```

## Scheduler

No production scheduler requirement was confirmed in this documentation pass. If scheduled jobs are added later, document the cron entry and commands here.

## Security Checklist

- [ ] `APP_ENV=production`.
- [ ] `APP_DEBUG=false`.
- [ ] `DEBUGBAR_ENABLED=false`.
- [ ] Real `APP_KEY` is set and secret.
- [ ] HTTPS is configured.
- [ ] Admin/buyer/seller auth routes work.
- [ ] File uploads are validated and stored through Laravel Storage.
- [ ] Backups are configured for database and uploaded files.
- [ ] Logs are monitored.
- [ ] Queue worker is supervised if async queues are enabled.
- [ ] Error pages do not expose internals.

## Release Checklist

- [ ] Tests pass.
- [ ] `npm run build` passes.
- [ ] Migrations tested from zero in a non-production environment.
- [ ] Seeders tested where relevant.
- [ ] README/docs updated.
- [ ] `CHANGELOG.md` updated.
- [ ] Release notes created.
- [ ] Production environment values reviewed.
- [ ] Version tag created after verification.
