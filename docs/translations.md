# Translation Guide

## Supported Languages

The application currently supports:

- `lt` - Lithuanian, configured as the default locale.
- `en` - English, configured as the fallback locale.

Supported locales live in `config/app.php` under `locales`. UI translations live in `lang/lt.json` and `lang/en.json`.

## Key Standard

Use dot-based keys for new and refactored text. Do not use the visible English text as the key.

Examples:

- `ui.actions.save`
- `marketplace.products.status.active`
- `orders.status.pending`
- `orders.messages.created_successfully`
- `orders.payment_status.paid`
- `cart.messages.added_successfully`
- `auth.fields.email`
- `validation.attributes.locale`
- `notifications.orders.status_changed.subject`

Old underscore keys still exist for legacy screens, but new code should not add more.

## Adding A Translation

1. Add the same key to every JSON file in `lang/`.
2. Use `__($key)` or `@lang($key)` in Blade, Livewire, notifications, emails, models, actions, and tests.
3. Run:

```bash
php artisan test --compact tests/Feature/Translations/TranslationFilesTest.php
```

## Adding A Language

1. Add the locale to `config/app.php`.
2. Create `lang/{locale}.json`.
3. Copy the complete key set from `lang/en.json`.
4. Translate every value.
5. Verify with `TranslationFilesTest`.

## Status Labels

Order lifecycle labels come from `App\Enums\OrderStatus` and use:

- `orders.status.{status}`
- `orders.status.{status}.description`

Payment labels come from `App\Enums\OrderPaymentStatus` and use:

- `orders.payment_status.{status}`

Product active/deleted labels come from `App\Models\Product::statusLabel()` and use:

- `marketplace.products.status.active`
- `marketplace.products.status.inactive`
- `marketplace.products.status.deleted`

Do not display raw values such as `pending`, `active`, `cancelled`, or `draft`.

## Validation

Use translated attribute names so errors are human-readable. Form Requests should define `attributes()` when field names are not already clear.

Example:

```php
public function attributes(): array
{
    return [
        'locale' => __('validation.attributes.locale'),
    ];
}
```

## Notifications And Emails

Notification subjects, greetings, lines, action labels, and Markdown mail templates must use translation keys. The shared mail notification template uses `emails.action_url_trouble` for the action URL fallback text.

## Database Content

Database-backed content uses JSON translations where the model declares `HasJsonTranslations`.

Current translated content fields include category names/slugs, country names/descriptions, attribute names, attribute values, and product descriptions. Product names and seller-entered names are currently stored as user-generated content, not UI translation keys.

Seeders should populate all configured locales for translatable database fields.

## Locale Formatting

Use `App\Support\LocaleFormatter` for new currency and date-time display:

- `LocaleFormatter::currency($amount)`
- `LocaleFormatter::dateTime($date)`

The `SetLocale` middleware applies the session locale to Laravel and Carbon on every web request.
