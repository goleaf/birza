# Frontend Stack Compatibility Report - 2026-06-07

## Scope

This report checks the current frontend stack before any Tailwind, Vite, Livewire, Mary UI, WireUI, daisyUI, Flowbite, Alpine, Blade component, CSS, or JavaScript upgrade work.

No dependencies were changed.

Files and surfaces checked:

- `package.json`
- `package-lock.json`
- `composer.json`
- `composer.lock` through `composer show`
- `vite.config.js`
- `tailwind.config.js`
- `postcss.config.js`
- `resources/css/app.css`
- `resources/js/app.js`
- `resources/js/bootstrap.js`
- `resources/views/layouts/*`
- `resources/views/components/*`
- `resources/views/frontend/*`
- `resources/views/backend/*`
- `resources/views/livewire/*`
- `app/Livewire/*`
- `config/livewire.php`
- `config/mary.php`
- `config/wireui.php`
- `routes/*.php`

## Executive Decision

Do not upgrade Tailwind yet.

The current app is already on Livewire 4 and Mary UI 2, but the frontend is not stable enough for a Tailwind major-version upgrade:

- The homepage route returns `resources/views/frontend/welcome.blade.php` directly from `HomeController` without a layout, so the page does not load `@vite` assets and renders unstyled.
- Layouts still load `@wireUiStyles`, but WireUI 2.6.0 returns HTTP 500 for `/wireui/assets/styles` because `vendor/wireui/wireui/dist/wireui.css` is missing.
- Some auth views still contain unprefixed WireUI components such as `<x-link>`, `<x-input>`, `<x-password>`, and `<x-checkbox>`. Rendered HTML on buyer login still contains literal `<x-link :href="route(...)" :label="__(...)">`, which triggers Alpine expression errors.
- Flowbite is installed and registered as a Tailwind plugin, but no app usage was detected.

Primary UI direction:

- Mary UI is the primary Livewire component system.
- Tailwind is the base utility system.
- daisyUI is allowed as Mary UI's theme/component-class foundation, not as a separate page-level component system.
- Alpine is allowed only through Livewire/Mary/custom minimal interactions. Do not import/start a second Alpine instance.
- WireUI is temporary compatibility only and should be removed from page/layout runtime after replacements are verified.
- Flowbite should not be used for new UI and is a cleanup candidate after confirming no hidden dependency.
- Icons should standardize on Mary/Heroicons through `<x-ui.icon>` and `<x-mary-icon>`.

## Current Versions

### PHP / Composer

| Package | Current version | Notes |
| --- | ---: | --- |
| PHP | 8.5 | Reported by Laravel Boost. |
| Laravel | 12.61.1 | Current runtime. |
| Livewire | 4.3.1 | Already upgraded to Livewire 4. |
| Mary UI | 2.8.3 | Installed with prefix `mary-`. |
| WireUI | 2.6.0 | Installed; runtime issues found. |
| WireUI Heroicons | 2.10.0 | Installed. |
| WireUI Phosphoricons | 2.5.0 | Installed. |

### Node / NPM

| Package | Locked version | Notes |
| --- | ---: | --- |
| Node | 22.22.2 | Satisfies Vite 8 and Laravel Vite Plugin 3 engine range. |
| npm | 10.9.7 | Current local CLI. |
| Vite | 8.0.16 | Already upgraded. |
| laravel-vite-plugin | 3.1.0 | Peer requires Vite `^8.0.0`. |
| Tailwind CSS | 3.4.19 | Still Tailwind 3. |
| daisyUI | 4.12.24 | Still daisyUI 4. |
| Flowbite | 2.5.2 | Installed and registered, but no app usage detected. |
| Alpine.js | 3.15.3 | Installed in npm, but not imported by app JS. |
| PostCSS | 8.5.15 | Used by Tailwind 3 pipeline. |
| Autoprefixer | 10.4.23 | Used by PostCSS pipeline. |
| @tailwindcss/forms | 0.5.11 | Installed but not registered in `tailwind.config.js`. |
| @tailwindcss/typography | 0.5.19 | Registered and needed for `.prose` usage. |
| @tailwindcss/aspect-ratio | 0.4.2 | Registered but no `aspect-*` usage detected. Tailwind 4 peer support not declared. |
| @tailwindcss/line-clamp | 0.4.4 | Installed but not registered and no `line-clamp-*` usage detected. Tailwind 4 peer support not declared. |
| axios | 1.17.0 | Imported by `resources/js/bootstrap.js`. |

## Tailwind Dependencies

Packages and code depending on Tailwind:

- `tailwindcss` itself, currently the Tailwind 3 compiler.
- `daisyui`, registered in `tailwind.config.js`, provides classes such as `btn`, `card`, `badge`, `modal`, `tabs`, `drawer`, `menu`, `collapse`, `steps`, `stat`, and theme tokens.
- `robsontenorio/mary`, scanned by `tailwind.config.js` through `./vendor/robsontenorio/mary/src/View/Components/**/*.php`, relies on Tailwind and daisyUI classes.
- `wireui/wireui`, scanned through vendor PHP and TS paths and added as a Tailwind preset through `require("./vendor/wireui/wireui/tailwind.config.js")`.
- `flowbite`, registered through `require('flowbite/plugin')`, but no app templates or JS references were found.
- `@tailwindcss/typography`, needed because `resources/views/backend/products/show.blade.php` uses `prose`.
- `@tailwindcss/aspect-ratio`, registered but no app `aspect-*` usage was found.
- Custom Blade templates throughout `resources/views`.
- Custom CSS in `resources/css/app.css` using `@tailwind`, `@layer`, and `@apply`.

Tailwind control files:

- `tailwind.config.js`
- `postcss.config.js`
- `resources/css/app.css`
- `package.json`
- `package-lock.json`

## Alpine Dependencies

Alpine comes from Livewire 4 at runtime. `resources/js/app.js` intentionally does not import or start Alpine.

Code depending on Alpine behavior:

- Mary components such as modals, drawers, tabs, dropdowns, tables, date pickers, charts, image library, tags, choices, and file components.
- WireUI notification/dialog runtime.
- `resources/views/components/modal.blade.php`, a custom Alpine modal.
- `resources/views/layouts/backend/app.blade.php`, which has root `x-data`.
- Backend navigation search triggers using `@click.stop="$dispatch('mary-search-open')"`.
- Backend list pages opening Mary drawers with `@click="$wire.drawer = true"`.

Risk:

- The npm `alpinejs` package is installed but not imported. Importing or starting it would create a second Alpine instance and conflict with Livewire 4.

## Livewire Compatibility

Current Livewire configuration:

- `livewire/livewire` is 4.3.1.
- `config/livewire.php` uses Livewire 4 keys: `component_layout`, `component_placeholder`, `component_locations`, `component_namespaces`, and `smart_wire_keys`.
- `inject_assets` is `false`, so layouts must explicitly include `@livewireStyles` and `@livewireScripts`.
- `pagination_theme` is `tailwind`.
- `resources/js/app.js` correctly leaves Alpine boot to Livewire.

Layouts loading Livewire assets:

| Layout/view | Asset state |
| --- | --- |
| `resources/views/layouts/frontend/app.blade.php` | Loads `@vite`, `@livewireStyles`, `@wireUiStyles`, `@wireUiScripts`, `@livewireScripts`. |
| `resources/views/layouts/backend/app.blade.php` | Loads `@vite`, Livewire, WireUI, CropperJS, SortableJS, and Mary spotlight. |
| `resources/views/layouts/backend/auth.blade.php` | Loads `@vite`, Livewire, and WireUI. |
| `resources/views/layouts/blank.blade.php` | Loads `@vite`, Livewire, WireUI, and Google Fonts. |
| `resources/views/frontend/welcome.blade.php` | Directly includes footer, `@wireUiScripts`, and `@livewireScripts`, but when served by `HomeController` it is not wrapped in a layout and does not load `@vite`. |
| `resources/views/errors/maintenance.blade.php` | Uses CDN Tailwind, separate from app Vite build. |

Livewire components that depend on current UI behavior:

- WireUI confirmation/notification trait: `Backend/Attributes/Index`, `Backend/Attributes/Values/Index`, `Backend/Buyers/Index`, `Backend/Categories/Index`, `Backend/Countries/Index`, `Backend/Products/Index`, `Backend/Sellers/Index`, `Frontend/Buyer/Orders/Show`, `Frontend/Seller/Orders/Show`, `Frontend/Seller/Products/Index`.
- Mary table sorting trait: backend index pages for attributes, buyers, countries, orders, products, and sellers.
- File uploads: `Backend/Buyers/Credit`, `Backend/Products/Create`, `Backend/Products/Edit`, `Frontend/Seller/Products/Create`, `Frontend/Seller/Products/Edit`.
- Mary tabs: buyer/seller profile pages and translated admin forms.
- Mary drawers: backend index filters.
- Date picker wrappers: buyer orders, seller orders, seller transactions, seller product form.
- Cart UI: `Frontend/Buyer/Cart/Index` uses Livewire form submissions and loading states.

## Mary UI Compatibility

Current state:

- Mary UI 2.8.3 is installed.
- `config/mary.php` sets `'prefix' => 'mary-'`, avoiding collisions with project components and old WireUI component names.
- Many project wrappers already forward to Mary, including `x-ui.button`, `x-ui.card`, `x-ui.badge`, `x-ui.tabs`, `x-ui.icon`, `x-ui.datepicker`, `x-ui.progress`, `x-ui.rating`, and `x-ui.statistic`.
- Admin views heavily use direct `<x-mary-*>` components.

Mary should remain the primary direction, but direct migration should pause until these current failures are fixed:

- Homepage must load app assets through a proper layout.
- Unprefixed WireUI components in auth/flash pages must be converted to Mary or `x-ui.*` wrappers.
- WireUI runtime assets must be removed or isolated before global layouts stop throwing 500s.

## WireUI Compatibility

Current state:

- WireUI 2.6.0 is installed.
- Layouts load `@wireUiStyles` and `@wireUiScripts`.
- `config/wireui.php` uses `prefix => null`, so unprefixed components like `<x-input>` and `<x-link>` are intended to belong to WireUI.
- `app/Livewire/Concerns/InteractsWithWireUi.php` still uses `WireUi\Traits\WireUiActions` for notifications.

Problems found:

- `/wireui/assets/styles` returns HTTP 500.
- The package contains `vendor/wireui/wireui/dist/wireui.js` but no `vendor/wireui/wireui/dist/wireui.css`.
- Buyer login rendered literal `<x-link>` tags and produced Alpine errors for `route(...)` and `__(...)` expressions.
- WireUI 2.6.0 package metadata lists Livewire 3.6 in its dev requirements, while this app is already on Livewire 4.3.1.

Decision:

- Do not use WireUI for new UI.
- Migrate remaining unprefixed WireUI components to Mary or project wrappers.
- Remove `@wireUiStyles`, `@wireUiScripts`, `<x-notifications>`, and `<x-dialog>` only after notification/dialog replacements are in place and verified.

## daisyUI Compatibility

Current state:

- daisyUI 4.12.24 is installed and registered.
- Themes configured: `corporate`, `light`.
- Mary UI 2 components and project wrappers use daisyUI class names.

Decision:

- Keep daisyUI for now because Mary depends on daisyUI-style classes.
- Do not treat daisyUI as a separate direct component library for new project views.
- Upgrade daisyUI only with the Tailwind 4 step, not before current layout/WireUI/auth failures are fixed.

## Flowbite Compatibility

Current state:

- Flowbite 2.5.2 is installed and registered in `tailwind.config.js`.
- No Flowbite data attributes, JS initialization, or template usage were detected.

Decision:

- Flowbite is not part of the primary UI direction.
- Do not add new Flowbite components.
- Candidate for removal after one cleanup pass confirms no generated classes are required.

## Vite Compatibility

Current state:

- Vite 8.0.16 and `laravel-vite-plugin` 3.1.0 are installed.
- Local Node 22.22.2 satisfies both packages' engine range.
- `vite.config.js` has the standard Laravel input list:
  - `resources/css/app.css`
  - `resources/js/app.js`
- No frontend path aliases are configured.
- `npm run build` passes.

Build result:

- `public/build/manifest.json`
- `public/build/assets/app-BNAUV0KD.css`
- `public/build/assets/app-f1GSCnwV.js`

Warnings:

- Browserslist/caniuse-lite data is stale.
- Mary emits a Tailwind warning for `h-[length:var(--border)]`, which can become `h-[var(--border)]`.

Decision:

- Do not change Vite config now.
- Vite is already on the current major used by the Laravel Vite plugin.
- Stabilize asset loading and UI runtime before touching Vite again.

## Custom CSS And JavaScript

CSS:

- `resources/css/app.css` uses Tailwind 3 directives: `@tailwind base`, `@tailwind components`, `@tailwind utilities`.
- Custom CSS covers `x-cloak`, order calendar event colors, EasyMDE, markdown content typography, code blocks, and links.
- Tailwind 4 migration will require converting this file to the CSS-first import/source model.

JavaScript:

- `resources/js/app.js` imports `./bootstrap` and intentionally avoids Alpine import.
- `resources/js/bootstrap.js` configures axios.
- CDN scripts are loaded by Blade components/layouts:
  - CropperJS
  - SortableJS
  - Chart.js
  - Vanilla Calendar Pro
  - Flatpickr
  - EasyMDE
  - PhotoSwipe

Risk:

- Components using pushed CDN scripts must be tested with Livewire navigation/re-rendering. If `wire:navigate` is used, scripts relying on first-load behavior can fail on subsequent navigations.

## Duplicated Component Categories

| Category | Current sources | Standard |
| --- | --- | --- |
| Buttons | `x-ui.button`, direct `x-mary-button`, legacy `primary-button`, `secondary-button`, WireUI button config, daisyUI `btn-*` classes | Use `x-ui.button` in project views. Direct `x-mary-button` is allowed inside wrappers/layout internals. Retire legacy buttons when unused. |
| Inputs | Direct Mary inputs, unprefixed WireUI `<x-input>`, legacy `text-input` | Use Mary inputs or future `x-ui.input` wrappers. Replace unprefixed WireUI auth inputs. |
| Selects | Mary select, native selects, WireUI select config | Use Mary select for admin/filter forms. Native select is allowed only for simple custom-styled forms until wrapped. |
| Checkboxes | Mary checkbox/toggle, WireUI checkbox in auth | Use Mary checkbox/toggle. Replace WireUI checkbox in auth. |
| Dropdowns | Mary dropdown/menu, `x-ui.popover`, Flowbite installed but unused | Use Mary dropdown/menu or `x-ui.popover`. Do not use Flowbite. |
| Modals | Mary modal, backend confirm modal, custom Alpine `x-modal`, WireUI dialog | Use Mary modal via `x-backend.confirm-modal` or a shared project modal wrapper. Retire custom Alpine and WireUI dialog after parity. |
| Tabs | `x-ui.tabs`, direct Mary tabs | Use `x-ui.tabs` in project views; direct Mary tabs allowed inside wrappers/admin forms. |
| Tables | Mary table, custom HTML tables, Livewire pagination | Use Mary table for admin CRUD surfaces. Commerce/order tables can stay custom until migrated. |
| Cards | `x-ui.card`, direct Mary card, raw Tailwind cards | Use `x-ui.card` for project views. Convert raw cards gradually. |
| Alerts | `x-ui.flash-messages`, unprefixed WireUI `<x-alert>`, Mary alert | Use a Mary-backed `x-ui.flash-messages`/alert wrapper. Replace unprefixed WireUI alerts. |
| Badges | `x-ui.badge`, direct Mary badge, order status badge | Use `x-ui.badge` or domain wrappers such as `x-order.status-badge`. |
| Pagination | Livewire Tailwind pagination and manual links | Keep Livewire Tailwind pagination until Mary pagination is intentionally introduced and tested. |
| Date pickers | `x-ui.datepicker`, Mary datepicker, Flatpickr CDN | Use `x-ui.datepicker`. Later bundle assets or use Livewire `@assets` consistently. |
| File uploads | Mary image library, Livewire `WithFileUploads`, native file inputs | Use Mary file/image-library wrappers backed by Livewire `WithFileUploads`. |
| Icons | `x-ui.icon`, direct Mary icon, WireUI heroicons/phosphoricons | Use `x-ui.icon`/Mary Heroicons. Avoid WireUI icon packages in new UI. |

## Dependencies To Remove Later

Do not remove these in the compatibility-report step. Remove only after focused checks.

| Dependency | Reason it looks unnecessary | Removal condition |
| --- | --- | --- |
| `flowbite` | Registered but no app usage detected. | Remove plugin/package after build and visual smoke confirm no class loss. |
| `alpinejs` npm package | Not imported; Livewire 4 bundles Alpine. | Remove after confirming no direct `import Alpine` is needed. |
| `@tailwindcss/forms` | Installed but not registered in Tailwind config. | Remove or intentionally register, but do not leave unused. |
| `@tailwindcss/line-clamp` | Installed but not registered and no usage detected. | Remove unless a hidden usage is found. |
| `@tailwindcss/aspect-ratio` | Registered but no usage detected; does not declare Tailwind 4 peer support. | Remove before Tailwind 4 unless usage appears. |
| WireUI packages | Primary runtime is moving to Mary and current WireUI styles fail. | Remove only after notifications/dialogs/forms/icons are replaced and tested. |

## Dependencies Risky To Upgrade Now

| Dependency | Risk |
| --- | --- |
| Tailwind CSS 4 | Current app has asset-loading failures and plugin cleanup needed first. |
| daisyUI 5 | Should move with Tailwind 4 only; visual drift likely. |
| Flowbite 4 | Latest Flowbite depends on Tailwind 4, but Flowbite is unused. Upgrade is unnecessary. |
| WireUI | Current WireUI runtime is broken; upgrading keeps a competing UI system alive. |
| Mary UI minor updates | Mary is already current enough. Stabilize wrappers/layouts before changing. |
| Alpine npm | App should not start a separate Alpine instance. |

## Tailwind Upgrade Path

Stay on Tailwind 3 for now.

Tailwind 4 can be a separate step only after:

1. Homepage loads the standard frontend layout/assets.
2. WireUI global style/script includes are removed or fixed.
3. Auth pages no longer render literal WireUI component tags.
4. Flowbite is removed or explicitly justified.
5. Unused Tailwind plugins are removed or replaced.
6. Mary/daisyUI source scanning is documented for Tailwind 4.
7. Desktop and mobile screenshots are captured for homepage, auth, catalog, product, cart, checkout, buyer dashboard, seller dashboard, admin dashboard, product create/edit, order pages, and profile pages.

Tailwind 4 checks when that step starts:

- Replace `@tailwind` directives with Tailwind 4 CSS import/source configuration.
- Add explicit Mary source paths.
- Recheck WireUI source paths only if WireUI remains temporarily.
- Check daisyUI 5 compatibility.
- Check Flowbite only if it remains, otherwise remove it before the upgrade.
- Keep typography support because `.prose` is used.
- Remove or replace aspect-ratio and line-clamp plugins unless current usage appears.
- Verify dark mode/theme behavior for `data-theme="corporate"` and `light`.

## Important Page Verification Matrix

Use these pages for every frontend dependency or UI-library change.

| Surface | Path / route | Current smoke result |
| --- | --- | --- |
| Homepage | `/` | Loads but lacks `@vite` CSS through `HomeController`; visual fail on desktop/mobile. |
| Buyer auth | `/buyer/login`, `/buyer/register`, reset pages | Route tests pass, but buyer login has WireUI/Alpine console errors and literal `<x-link>` output. |
| Seller auth | `/seller/login`, `/seller/register`, reset pages | Same component family as buyer auth; needs same checks. |
| Admin auth | `/admin/login` | Loads but WireUI stylesheet 500 occurs. |
| Catalog | `/buyer/products` | Auth-required; verify after seeded buyer login. |
| Product page | `/buyer/products/{product}` | Auth-required; verify with product fixture. |
| Cart | `/buyer/cart` | Auth-required; test cart item rendering, quantity form, checkout button. |
| Checkout | Current cart component checkout flow | No separate checkout page detected; verify cart checkout action. |
| Buyer dashboard | `/buyer/dashboard` | Auth-required. |
| Seller dashboard | `/seller/dashboard` | Auth-required. |
| Seller product create/edit | `/seller/products/create/{categoryId}`, `/seller/products/{product}/edit` | File uploads and Mary image library are sensitive. |
| Orders | `/buyer/orders`, `/buyer/orders/{order}`, `/seller/orders`, `/seller/orders/{order}`, `/admin/orders` | Confirm modals, statuses, tables, pagination. |
| Admin dashboard | `/admin/dashboard` | Auth-required. |
| Admin CRUD | `/admin/products`, `/admin/categories`, `/admin/attributes`, `/admin/buyers`, `/admin/sellers`, `/admin/settings` | Mary tables/drawers/forms. |
| Profile pages | `/buyer/profile`, `/seller/profile`, `/admin/profile` | Tabs, validation, password forms. |

## Verification Performed

- `npm run build` passed with Vite 8.0.16.
- `php artisan test --compact tests/Feature/Controllers/Backend/Auth/LoginControllerTest.php tests/Feature/Controllers/Frontend/Auth/BuyerAuthControllerTest.php` passed: 11 tests, 37 assertions.
- `php artisan route:list --except-vendor` only listed controller/logout/API routes; named Livewire routes still resolved through `route(...)` and focused tests passed. Treat `route:list` visibility as a tooling caveat.
- `php artisan tinker --execute` confirmed important named URLs resolve for buyer, seller, and backend pages.
- Playwright desktop smoke:
  - `/` loads but renders unstyled because `@vite` is missing from the direct homepage response.
  - `/buyer/login` loads with WireUI stylesheet 500 and Alpine expression errors.
  - `/admin/login` loads with WireUI stylesheet 500.
- Playwright mobile smoke:
  - `/` reaches the page but is not a responsive pass because app CSS is missing.

## Safe Next Steps

1. Stabilize asset/layout loading:
   - Route `/` through `layouts.blank` or `layouts.frontend.app`, or make `frontend.welcome` a proper full document that loads `@vite` exactly once.
   - Remove duplicate `@wireUiScripts`/`@livewireScripts` from partial-style views.
2. Replace unprefixed WireUI auth components:
   - Convert `<x-input>`, `<x-password>`, `<x-checkbox>`, `<x-link>`, and `<x-alert>` in auth/flash views to Mary or `x-ui.*` wrappers.
3. Replace WireUI global runtime:
   - Move notifications/dialogs to Mary toast/modal or project wrappers.
   - Remove `@wireUiStyles`, `@wireUiScripts`, `<x-notifications>`, and `<x-dialog>` once no page depends on them.
4. Remove Flowbite if build and visual smoke stay green.
5. Clean unused Tailwind plugins/packages.
6. Only then open a separate Tailwind 4 + daisyUI 5 upgrade step.

## How To Add New UI Components

- Prefer `resources/views/components/ui/*` wrappers for app-level components.
- Use direct `x-mary-*` only inside wrappers, layouts, or admin pages already following Mary.
- Do not add unprefixed vendor components.
- Do not add Flowbite components.
- Do not add a second Alpine runtime.
- Keep visible text translatable through existing localization patterns.
- Add Livewire tests for forms, validation, pagination, file uploads, and actions.

## How To Test Frontend Changes

Minimum for UI/layout/runtime changes:

```bash
npm run build
php artisan optimize:clear
php artisan test --compact tests/Feature/Controllers/Backend/Auth/LoginControllerTest.php tests/Feature/Controllers/Frontend/Auth/BuyerAuthControllerTest.php
```

Then manually check desktop and mobile:

- layout spacing
- buttons
- forms
- dropdowns
- modals
- tables
- cards
- responsive behavior
- loading states
- validation states
- dark/theme behavior if the page uses `data-theme`
- browser console
- Laravel logs

Run the full suite only after focused checks pass:

```bash
php artisan test --compact
```

## Production Asset Verification

For production-style asset checks:

```bash
npm run build
php artisan optimize:clear
php artisan view:cache
```

Then check:

- `public/build/manifest.json` exists.
- Layouts use `@vite(['resources/css/app.css', 'resources/js/app.js'])`.
- Livewire assets are present on every Livewire layout because `inject_assets` is disabled.
- No page-specific view includes duplicate global scripts.
- Browser console has no missing CSS/JS, Alpine, Livewire, or WireUI errors.
