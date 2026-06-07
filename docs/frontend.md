# Frontend Guide

Birza is a Blade and Livewire application. The frontend is server-rendered and uses Vite, Tailwind CSS, Alpine.js, maryUI, and existing Blade components.

## Commands

```bash
npm install
npm run dev
npm run build
```

There is no React, Vue, Inertia, or SPA frontend.

## Asset Entrypoints

- CSS: `resources/css/app.css`
- JavaScript: `resources/js/app.js`
- Vite config: `vite.config.js`
- Tailwind config: `tailwind.config.js`

Vite inputs:

```js
'resources/css/app.css'
'resources/js/app.js'
```

## UI Stack

Current installed UI/frontend packages:

- Tailwind CSS `3.4.19`
- Alpine.js `3.15.3`
- maryUI `2.8.3`
- WireUI `2.6.0`
- daisyUI `4.12.24`
- Flowbite `2.5.2`

maryUI is the target primary UI system. WireUI, daisyUI, and Flowbite remain during migration and should be reduced or isolated over time.

## Blade And Livewire Files

| Path | Purpose |
| --- | --- |
| `resources/views/layouts` | Public/frontend/backend shells and navigation. |
| `resources/views/components` | Reusable Blade components. |
| `resources/views/frontend` | Buyer/seller/public Blade views. |
| `resources/views/backend` | Admin/backend Blade views. |
| `resources/views/livewire` | Livewire component views. |
| `app/Livewire` | Livewire component classes. |

## Livewire Rules

- Keep state server-side.
- Validate and authorize inside Livewire actions.
- Use `wire:key` for loops and nested components.
- Keep queries out of Blade templates.
- Do not call relationships or aggregates inside view loops unless already eager-loaded.
- Use loading/disabled states for mutating actions.
- Use translated strings for visible labels, buttons, errors, and notifications.

## Styling Rules

- Use Tailwind utility classes consistently.
- Prefer gap utilities for spacing between siblings.
- Keep cards, form controls, buttons, modals, and alerts going through shared wrappers where possible.
- Do not mix multiple UI libraries for the same component type unless this is an intentional migration bridge.
- Keep mobile layouts functional for catalog, cart, checkout, dashboard, and admin table pages.

## JavaScript

`resources/js/app.js` imports bootstrap behavior and intentionally avoids starting a second Alpine instance when Livewire already manages Alpine integration.

Add custom JavaScript only when Blade/Livewire/Alpine cannot cover the interaction.

## Build Verification

Run:

```bash
npm run build
```

If Vite manifest errors appear in the browser, run the build or keep the dev server running.

## Related Docs

- [Frontend compatibility report](frontend-stack-compatibility-2026-06-07.md)
- [Architecture guide](architecture.md)
- [Testing guide](testing.md)
