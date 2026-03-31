# External Integrations

**Analysis Date:** 2026-04-01

## APIs & External Services

**Email / Transactional Messaging:**
- Laravel mail delivery is actively used for seller verification and password-reset flows.
  - SDK/Client: Laravel `Mail` facade in `app/Livewire/Frontend/Auth/Register.php`, `app/Livewire/Frontend/Auth/ForgotPassword.php`, `app/Livewire/Frontend/Auth/VerificationNotice.php`, and `app/Livewire/Frontend/Auth/RegisterSuccess.php`.
  - Auth: `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`, and `MAIL_FROM_NAME` from `config/mail.php`.
  - Provider options: Mailgun, Postmark, and AWS SES credentials are configurable in `config/services.php`; mail transports are defined in `config/mail.php`.
  - Templates: The active auth flows send raw text emails with `Mail::raw(...)`; reusable mail view scaffolding also exists under `resources/views/mail/` and `resources/views/notifications/email.blade.php`.

**External APIs:**
- No outbound REST, GraphQL, or SDK-based third-party API integrations were detected in `app/`, `routes/`, `resources/js/`, or `tests/`.
  - Integration method: Not detected.
  - Auth: Not applicable.
  - Notes: `guzzlehttp/guzzle` is installed in `composer.lock`, but no direct `Http::...`, Guzzle client, or external API call sites were found in application code.

**CDN / Hosted Content Dependencies:**
- Google Fonts - remote font loading is used in `resources/views/layouts/blank.blade.php` and `resources/views/errors/maintenance.blade.php`.
  - Integration method: direct `<link>` tags to `fonts.googleapis.com`.
  - Auth: None.
- Tailwind CDN - the maintenance page loads Tailwind from `cdn.tailwindcss.com` in `resources/views/errors/maintenance.blade.php`.
  - Integration method: direct `<script>` tag.
  - Auth: None.
- Unsplash and placeholder image hosts - marketing/dashboard visuals depend on remote image URLs in `app/Actions/Frontend/BuildWelcomePageDataAction.php` and `resources/views/frontend/buyer/dashboard/index.blade.php`.
  - Integration method: static external image URLs.
  - Auth: None.

## Data Storage

**Databases:**
- SQLite is the currently active application database in the checked local environment.
  - Connection: `config/database.php` defaults to `sqlite`, and `php artisan about --json` reports the active database driver as `sqlite`.
  - File path: `database/birza.sqlite` when `DB_CONNECTION=sqlite`.
  - Client: Laravel Eloquent models in `app/Models/`.
  - Migrations: schema files live in `database/migrations/`.
- MySQL, PostgreSQL, and SQL Server connections are also configured in `config/database.php`, but they were not observed as the active local driver.
- Tests use a separate in-memory SQLite setup through `phpunit.xml`.

**File Storage:**
- Local filesystem storage is the only storage backend actively used in application code.
  - SDK/Client: Laravel `Storage` facade with the `public` disk from `config/filesystems.php`.
  - Active code paths: product upload and replacement flows in `app/Livewire/Frontend/Seller/Products/Create.php`, `app/Livewire/Frontend/Seller/Products/Edit.php`, and `app/Livewire/Backend/Products/Edit.php`; credit attachment upload/download flows in `app/Livewire/Backend/Buyers/Credit.php` and `app/Livewire/Backend/Buyers/CreditHistory.php`.
  - Public URL mapping: `config/filesystems.php` maps the `public` disk to `APP_URL/storage`.
- S3 storage is configurable in `config/filesystems.php`, but no `Storage::disk('s3')` usage was detected in `app/`.

**Caching:**
- The checked local environment reports `database` as the active cache driver via `php artisan about --json`.
  - Connection: active driver is environment-dependent; fallback config in `config/cache.php` is `file`.
  - Client: Laravel cache facade and store config in `config/cache.php`.
  - App usage: `app/Providers/GlobalSettingsServiceProvider.php` caches the `portal_additional_price` value with `Cache::remember(...)`.
- Redis, Memcached, and DynamoDB cache stores are configurable in `config/cache.php`, but no active code path requires them.

## Authentication & Identity

**Auth Provider:**
- Authentication is custom and Laravel-native rather than delegated to an external identity provider.
  - Implementation: multi-guard session authentication is configured in `config/auth.php` for `web`, `admin`, `buyer`, and `seller`.
  - Entry points: route groups live in `routes/admin.php`, `routes/buyer.php`, and `routes/seller.php`.
  - UI flows: Livewire auth components live in `app/Livewire/Backend/Auth/Login.php` and `app/Livewire/Frontend/Auth/`.
  - Session management: `php artisan about --json` reports the active session driver as `file`; driver options are configured in `config/session.php`.

**API Token Auth:**
- Laravel Sanctum is present for token-aware API authentication.
  - Implementation: `routes/api.php` exposes `/api/user` behind `auth:sanctum`.
  - Token model support: `app/Models/Users/Buyer.php` uses `Laravel\Sanctum\HasApiTokens`.
  - Config: `config/sanctum.php`.

**OAuth Integrations:**
- No OAuth or social-login providers were detected in `composer.json`, `config/services.php`, `routes/`, or `app/`.

## Monitoring & Observability

**Error Tracking:**
- Flare support is configurable but not proven active from repository code alone.
  - DSN/Key: `FLARE_KEY` in `config/flare.php`.
  - Middleware/report enrichment: `config/flare.php` adds git, environment, logs, queries, jobs, and context to Flare reports.
- Ignition is installed for local exception rendering and debugging.
  - Config: `config/ignition.php`.

**Analytics:**
- No product analytics, event analytics, or marketing analytics integrations were detected.

**Logs:**
- Laravel logging is configured centrally in `config/logging.php`.
  - Current local environment: `php artisan about --json` reports the active log channel stack as `single`.
  - Available external sinks: Slack webhook, Papertrail, syslog, stderr, and errorlog channels are defined in `config/logging.php`.
  - Auth: `LOG_SLACK_WEBHOOK_URL`, `PAPERTRAIL_URL`, and `PAPERTRAIL_PORT` when those channels are enabled.
- Local developer observability also includes Debugbar through `config/debugbar.php`.

## CI/CD & Deployment

**Hosting:**
- No deployment platform is explicitly defined in-repo.
  - No deployment manifests were found: no `Dockerfile`, `docker-compose*`, `render.yaml`, `vercel.json`, `netlify.toml`, or GitHub Actions workflow files are present.
  - Local development URL: `php artisan about --json` reports `birza.test`.
  - Operational clue: `app/Console/Commands/SystemCommand.php` prints a maintenance bypass URL on `https://birza.prus.dev/{secret}`. This confirms an external host exists, but the hosting provider is not specified by repository files.

**CI Pipeline:**
- No CI workflow definitions were detected in `.github/`; the directory currently contains skills and reference assets rather than workflow YAML.

## Environment Configuration

**Development:**
- Required env groups are defined by config files rather than hard-coded constants.
  - Core app: `APP_NAME`, `APP_ENV`, `APP_URL`, `APP_KEY` in `config/app.php`.
  - Database: `DATABASE_URL` or `DB_*` in `config/database.php`.
  - Sessions/cache/queue: `SESSION_*`, `CACHE_*`, `QUEUE_*`, `REDIS_*` in `config/session.php`, `config/cache.php`, and `config/queue.php`.
  - Mail: `MAIL_*`, plus provider-specific `MAILGUN_*`, `POSTMARK_TOKEN`, and `AWS_*` in `config/mail.php` and `config/services.php`.
  - Storage/cloud: `FILESYSTEM_DISK`, `AWS_*` in `config/filesystems.php`.
  - API/session auth: `SANCTUM_STATEFUL_DOMAINS` in `config/sanctum.php`.
  - Observability: `FLARE_KEY`, `DEBUGBAR_*`, `LOG_*` in `config/flare.php`, `config/debugbar.php`, and `config/logging.php`.
- Secrets location: `.env` is present in the project root and `.env.example` is present for non-secret defaults.
- Local driver snapshot from `php artisan about --json`: SQLite database, database cache, file sessions, sync queue, SMTP mail, and single-file logs.

**Staging:**
- Not explicitly defined by repository files.

**Production:**
- Not explicitly defined by repository files.
- The production-like integrations are all environment-driven through `config/*.php`; switching between local-only and cloud-backed services is controlled by env vars rather than separate config trees.

## Webhooks & Callbacks

**Incoming:**
- No webhook endpoints or signed callback handlers were detected in `routes/api.php`, `routes/web.php`, `routes/admin.php`, `routes/buyer.php`, or `routes/seller.php`.
- Broadcast channels exist in `routes/channels.php`, but websocket broadcasting is not actively wired to a provider in application code.

**Outgoing:**
- SMTP mail delivery is the only active outbound integration detected in application code.
- No outbound webhook dispatchers, queued webhook jobs, or third-party callback clients were detected in `app/` or `routes/`.

---

*Integration audit: 2026-04-01*
