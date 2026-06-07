---
quick_id: 260607-i18n
description: Improve multilingual system and translations
date: 2026-06-07
status: analysis_complete
---

# Multilingual System Analysis

## Scope

Audit target: user-facing text in Blade, Livewire, controllers, models, enums, helpers, services, notifications, emails, validation, routes, language switching, language files, database fields, seeders, and tests.

This report is intentionally written before implementation. The repository is currently dirty with unrelated/uncommitted quick-task work, including order-status workflow changes, security remediation, and product-filter work. Those changes must be preserved and not blindly reverted.

## Current Languages

- Supported locales are configured in `config/app.php` as `lt` and `en`.
- Default locale is `lt`.
- Fallback locale is `en`.
- Current language files are `lang/lt.json` and `lang/en.json`.
- Both JSON files currently contain `1617` top-level keys.
- Key parity is currently equal: `0` keys missing in `lt`, `0` keys missing in `en`.

## Current Language Switching

- Route: `GET /language/{locale}`, named `language.switch`.
- Controller: `App\Http\Controllers\Frontend\LocaleSwitchController`.
- Request validation: `App\Http\Requests\Frontend\SwitchLocaleRequest`.
- Middleware: `App\Http\Middleware\SetLocale`.
- Registration: `bootstrap/app.php` appends `SetLocale` to the web middleware group.

Current behavior:

- The selected locale is stored in `session('locale')`.
- `SetLocale` reads session locale and falls back to `config('app.locale')` if missing or invalid.
- The switch route redirects back with `redirect()->back()`.
- Guests are supported through the session.

Gaps:

- Invalid switch-route locale is rejected with a validation error instead of being replaced with a fallback locale.
- Locale is not persisted on authenticated buyer/seller/admin records because those tables have no `locale` column.
- Notification/mail locale preference is not implemented with `HasLocalePreference`.
- Redirect-back should be tested with query parameters.
- Language labels are generated as `strtoupper($locale)`, not translated names.

## Current Translation Storage

UI translations:

- Stored in JSON files: `lang/en.json`, `lang/lt.json`.
- Existing key style is underscore-based. There are `1588` underscore-only keys and `0` dot-based keys.
- Some dynamic keys are assembled in code, for example `common_months_...`, `units_unit_...`, `backend_countries_regions_...`.

Database content translations:

- Custom trait: `App\Models\Concerns\HasJsonTranslations`.
- JSON translated fields:
  - `categories.category_name`
  - `categories.slug`
  - `countries.country_name`
  - `countries.description`
  - `attributes.name`
  - `attribute_values.value`
  - `products.description`
- Product `name` is not translatable.
- Product `pack_type` is not translatable.
- No static-page/FAQ/email-template/notification-template tables were found in the live schema.

## Translation Key Problems

Hard standard problem:

- The requested standard is dot-based keys such as `ui.actions.save`, `marketplace.products.status.active`, and `orders.messages.created_successfully`.
- The project currently has no dot-based translation keys.
- Existing code uses keys such as `common_save`, `backend_common_save`, `orders_status_pending`, and `cart_messages_product_added`.

Duplicate value problem:

- `157` English values are duplicated across multiple keys.
- Examples:
  - `Title` appears under many backend title keys.
  - `Name` appears under auth, backend buyer/seller/category, common, and global keys.
  - `Status` appears under backend, common, global, order, and product keys.
  - `Create`, `Edit`, `Delete`, `Save`, `View`, and `Cancel` are duplicated across common/global/backend namespaces.

Generated/garbage key problem:

- Language files contain keys that look like extracted Blade/PHP syntax rather than intentional translation keys:
  - `forelse_*`
  - `include_*`
  - `endif*`
  - `endforeach*`
  - `endfor*`
  - `if_value_document_body_classlist_add_*`
  - `null_*`
  - `exists_*`
  - `product_gettranslation_description_app_getlocale`
  - `reseturl_reset_password_endcomponent_*`

Mixed-language value problem:

- `en.json` contains at least `143` values with Lithuanian/non-English content.
- `lt.json` contains at least `55` values that look English or generated.
- Examples in `en.json`: `cart_*` messages, dashboard labels, some product/order messages, and maintenance text.
- Examples in `lt.json`: `auth_verification_required`, buyer field labels, dashboard premium labels, and several validation messages.

Missing literal keys:

- A literal-key scan found `23` missing or suspicious keys. Some are dynamic-prefix false positives, but these are real gaps from current or dirty code:
  - `orders_notifications_status_changed_*`
  - `orders_status_reason_required`
  - `orders_status_transition_not_allowed`
  - `orders_status_unknown_actor_role`
  - `orders_transactions_seller_credit`
  - `orders_transactions_seller_debit`
  - the mail subcopy string in `resources/views/notifications/email.blade.php`

## Hardcoded User-Facing Text

Confirmed hardcoded or directly formatted user-facing text:

- `app/Notifications/ResetSellerPassword.php`
  - Subject is hardcoded: `Reset Seller Password`.
- `resources/views/emails/seller/reset-password.blade.php`
  - Full email body is hardcoded English.
- `resources/views/notifications/email.blade.php`
  - Mail subcopy uses the English sentence as the translation key.
- `app/Livewire/Frontend/Seller/Orders/Show.php`
  - `abort(403, 'Unauthorized access to order')`.
  - Transaction description string is built as `Order #... confirmed - Balance added`.
- `database/seeders/test_information/ProductSeeder.php`
  - Product names are generated as `Seed product ...`.
  - Pack labels are generated as `Standard pack ...`.
  - Seeder failure message is hardcoded English.
- `database/seeders/test_information/TestUsersSeeder.php`
  - Demo buyer/seller names and company names are English-only.
  - Demo addresses are Lithuanian-only strings mixed into generated content.
- `app/Console/Commands/RefreshCommand.php` and `app/Console/Commands/SystemCommand.php`
  - Console output is hardcoded English.
- Date/price formatting is hardcoded throughout views and models:
  - `format('Y-m-d H:i')`
  - `format('d F, Y')`
  - `number_format(...).' €'`
  - `€'.number_format(...)`
- Dashboard demo/static marketplace content remains in seller/buyer dashboard views, including hardcoded amounts and mock rows.

Likely acceptable dynamic content:

- User/product/company names from the database are displayed directly because they are content, not UI chrome.
- `config('app.name')` is displayed directly as brand text.
- SVG path data, CSS classes, route names, and component option labels like `option-label="name"` are not translation issues.

## Status Translation Review

Orders:

- `App\Enums\OrderStatus` centralizes labels through `orders_status_*`.
- `OrderStatusHelper` outputs translated labels.
- Many views now call `paymentStatusLabel()`/badge helpers.
- Existing keys are old style, for example `orders_status_pending`, not `orders.status.pending`.

Payments:

- `App\Enums\OrderPaymentStatus` has raw values but no label method.
- Payment and lifecycle are currently blended in places.

Users/buyers/sellers:

- Active/inactive and verified/not-verified labels use mixed keys such as `common_active`, `sellers_field_active`, and `buyers_field_verified`.
- Some `lt.json` buyer/seller field labels remain English.

Products:

- Boolean `is_active` and `is_organic` display through `common_yes/no` and active/inactive keys in most places.
- There is no product status enum; product active/draft-like states are represented as booleans or soft-delete state.
- Product soft delete/restore messages use old keys.

Moderation/reviews:

- Review model exists in the dirty worktree, but no implemented review status workflow was found.

Notifications:

- `OrderStatusChanged` has translation-key usage but its keys are missing from JSON files.
- `ResetSellerPassword` is not translated.

## Validation Review

Current validation behavior:

- Most Livewire components use inline `$this->validate([...])`.
- Form Requests exist for API product search and locale switching.
- Validation messages are mostly default Laravel messages through translated `validation_*` JSON keys.
- Some custom validation messages use old-style custom keys, for example `validation_category_required`.

Gaps:

- There is no centralized validation attribute-name map for fields such as `product_title`, `company_code`, `bank_account`, `selectedCategories.*`, `temperature_conditions_from`, etc.
- Livewire components rarely define translated attribute names, so errors can expose raw property names.
- Some Lithuanian validation strings are still English.
- `SwitchLocaleRequest` currently creates a validation error for invalid locale; requested behavior is fallback locale.

## Notification And Email Review

Notifications/emails found:

- `App\Notifications\OrderStatusChanged`
- `App\Notifications\ResetSellerPassword`
- `resources/views/emails/seller/reset-password.blade.php`
- `resources/views/notifications/email.blade.php`
- Auth Livewire components use `Mail::raw()` for password reset and verification messages.

Gaps:

- Seller reset password notification and template are hardcoded.
- Auth reset/verification messages use translated subject/body keys, but body content is very thin and URL concatenation is not ideal.
- Notifiable models do not implement `HasLocalePreference`.
- There are no tests asserting notification/mail content is localized in both languages.

## Database Content Translation Review

Needs multilingual fields:

- Categories: current `name`/`slug` yes; missing `description`, SEO title, SEO description if those fields are introduced.
- Product attributes/properties: current attribute names and values yes.
- Products: description yes; name no; pack type no. Product names are marketplace content and should be translatable if the app owns seeded/demo products. User-generated seller product names may remain seller content unless product localization is explicitly required.
- Countries: name/description yes, but description is not seeded.
- Static pages/FAQ: currently file-based homepage/FAQ copy, no DB structure.
- Email/notification templates: no DB template structure currently; use translation files for transactional messages unless stored templates are intentionally introduced.
- Reviews/moderation: models exist in dirty worktree but no implemented translatable status/content structure was found.

Seeder gaps:

- Product demo names and package labels are English-only.
- Test users/demo companies are English-only.
- Country descriptions are not seeded.
- Product descriptions are multilingual.
- Categories and slugs are multilingual.
- Attributes and values are multilingual.

## Formatting Review

Gaps:

- Currency and numbers use raw `number_format` and literal `€` in many views/models/actions.
- Dates use raw `format(...)` strings in many views.
- Pluralization uses simple counts and labels; no `trans_choice` standard exists for item/order/product counts.
- `Number::format()` is used only on the welcome page stats.

Recommended standard:

- Add a small locale-aware formatter for money, numbers, dates, and compact counts.
- Use `Number::currency(..., 'EUR', locale: app()->getLocale())` where Laravel supports it.
- Use `isoFormat`/translated month labels or a formatter helper for dates.
- Add plural keys such as `cart.items.count`, `orders.count`, and `marketplace.products.count`.

## Tests Missing

Missing or incomplete coverage:

- Translation parity test across all supported JSON files.
- Test that translation keys follow the dot-based standard for new keys.
- Test that main pages do not render raw translation keys.
- Invalid locale fallback behavior.
- Guest locale persistence.
- Authenticated user locale persistence/preference, if a DB locale column is added.
- Redirect-back preserves query parameters.
- Validation attributes are human-readable in localized validation messages.
- Order status labels are translated and raw values are not shown.
- Product status labels are translated.
- Order status notification is translated.
- Seller reset password notification/email is translated.
- Missing translation keys are detected.
- Fallback locale works for UI and JSON-translated model content.
- Locale-aware dates/prices/counts are formatted.

## Implementation Standard

Keep JSON translation files for now because the project already uses `en.json` and `lt.json`.

New keys must be dot-based and grouped by domain:

- `ui.actions.save`
- `ui.actions.cancel`
- `ui.actions.delete`
- `ui.actions.edit`
- `ui.actions.view`
- `ui.actions.search`
- `ui.actions.filter`
- `ui.actions.reset`
- `ui.actions.confirm`
- `ui.status.active`
- `ui.status.inactive`
- `ui.status.enabled`
- `ui.status.disabled`
- `marketplace.products.title`
- `marketplace.products.description`
- `marketplace.products.price`
- `marketplace.products.status.active`
- `marketplace.products.status.inactive`
- `marketplace.products.status.deleted`
- `marketplace.products.status.draft`
- `orders.status.pending`
- `orders.status.paid`
- `orders.status.failed`
- `orders.status.processing`
- `orders.status.shipped`
- `orders.status.delivered`
- `orders.status.cancelled`
- `orders.status.refunded`
- `orders.actions.cancel`
- `orders.messages.created_successfully`
- `orders.messages.cancelled_successfully`
- `cart.title`
- `cart.empty`
- `cart.actions.add`
- `cart.actions.remove`
- `cart.messages.added_successfully`
- `checkout.title`
- `checkout.steps.review`
- `checkout.steps.address`
- `checkout.steps.confirmation`
- `checkout.messages.order_created`
- `auth.login.title`
- `auth.register.title`
- `auth.fields.email`
- `auth.fields.password`
- `validation.attributes.email`
- `validation.attributes.password`
- `notifications.orders.status_changed.subject`
- `emails.seller.password_reset.subject`

Old underscore keys should be migrated incrementally with aliases during the refactor to avoid breaking the existing UI in one unsafe sweep. Once all code paths use dot keys, garbage and unused underscore keys can be removed.

## Refactor Plan From This Analysis

1. Add canonical dot-based keys to `lang/en.json` and `lang/lt.json`.
2. Add a translation parity test that fails if supported JSON files have different key sets.
3. Add tests for locale switch fallback, redirect-back query preservation, and JSON model fallback.
4. Improve `LocaleSwitchController`/middleware so invalid locales fall back instead of producing visible validation errors.
5. Add locale preference support only if schema changes are acceptable; otherwise document session-only behavior and keep guest/auth behavior consistent.
6. Translate seller password reset notification/template.
7. Add missing order notification/transaction/status keys used by dirty workflow code.
8. Add translated label methods for `OrderPaymentStatus` and product active/deleted statuses.
9. Add a locale-aware formatter helper/service and replace the most visible date/currency formatting in order/product/cart/dashboard pages.
10. Convert high-traffic Blade/Livewire surfaces to dot keys first: welcome, auth, catalog, cart, order pages, dashboards, admin order/product/category pages.
11. Clean garbage keys after tests prove they are unused.
12. Update docs/changelog, run migrations from zero if DB translations change, seeders, full tests, translation check, build, and page smoke checks.

