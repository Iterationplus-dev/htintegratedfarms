# Front end: TypeScript, Alpine.js and Livewire

## One Alpine instance

Livewire 4 bundles Alpine. The public layout loads **`resources/ts/app.ts`** through Vite and prints
`@livewireScriptConfig` (not `@livewireScripts`), so Livewire does not inject its own copy:

```ts
import { Alpine, Livewire } from '@livewire';   // vendor/livewire/livewire/dist/livewire.esm.js (alias in vite.config.js)
Alpine.data('heroSlider', heroSlider);           // register typed components…
Alpine.store('consent', createConsentStore());
Livewire.start();                                // …then start Livewire, which starts Alpine once
```

Do not install `alpinejs` from npm or call `Alpine.start()`; doing so would start a second instance.
`@types/alpinejs` supplies types only. `resources/ts/types/livewire.d.ts` types the `@livewire` module.

Alpine plugins that come with Livewire are already available: `x-trap` (focus), `x-collapse`, `x-intersect`, `x-mask`, `x-anchor`.
Filament admin pages load Filament's own assets and do not include `app.ts`.

## Components (`resources/ts/components/`)

| Component | Used by | Notes |
|---|---|---|
| `heroSlider(count, intervalMs)` | Homepage hero | 6 s autoplay, timer reset on manual nav, pauses on hover/focus/hidden tab, no autoplay or zoom under `prefers-reduced-motion`. Inactive slides are `inert` + `aria-hidden`. |
| `mobileNav()` | Header drawer (<1280 px) | `x-trap.inert.noscroll`, Escape closes, auto-closes when the viewport reaches desktop. |
| `createConsentStore()` → `$store.consent` | Banner, preferences dialog, footer link, Cookie Policy | `ht_consent` cookie `{essential, analytics, v, ts}`, 180 days. Analytics script injected only after consent (`lib/analytics.ts`). |
| `gallery(items, category)` | `/gallery` | Client-side chip filter over server-rendered tiles (works without JS), lightbox with ←/→/Esc, swipe, focus trap; URL kept in sync with `history.replaceState`. |
| `turnstile(siteKey)` | Livewire forms when Turnstile is enabled | Loads Cloudflare's script once, renders explicitly, writes the token to the Livewire property with `$wire.$set(…, false)`, resets on the `captcha-reset` browser event. Lives in a `wire:ignore` element. |

Every component is written as `AlpineComponent<T>` so `this` and the magics are typed. `tsconfig.json` uses
`strict`, `noUncheckedIndexedAccess` and `exactOptionalPropertyTypes`. Check with `npm run typecheck`.

## Lifecycle and navigation

- Components clean up timers and listeners in `destroy()`; `init()` is safe to run more than once.
- `wire:navigate` is **not** enabled (full page loads keep third-party scripts simple). Because components already
  tear down cleanly, adding `wire:navigate` to links later is safe; analytics page views would then need a
  `livewire:navigated` listener.
- Livewire forms dispatch `enquiry-submitted`; `app.ts` moves focus to the success message (`[data-success-focus]`).

## Livewire model binding (v4 note)

In Livewire 4, `wire:model.blur` syncs **client** state on blur only. Use `wire:model.live.blur` when the server
must react (the quote summary and step indicator use it). The contact and partnership forms use deferred
binding on purpose, so values are sent with the submit.

## Styling

Design tokens are in `resources/css/app.css` (`@theme`): colours (`bg-g700`, `text-ink-2`, `border-line`…),
radii (`rounded-ui` 8 px, `rounded-card` 14 px), brand shadows, and the 2xl breakpoint at 1440 px. Shared
patterns (`.btn-*`, `.chip`, `.field-input`, `.eyebrow`, `.container-site`) are in `@layer components`.

Static imagery lives in `resources/images/` (JPEG plus 400/800/1600 WebP) and is referenced with `<x-static-picture>`.
CMS imagery goes through Media Library and `<x-picture>`. Icons are Phosphor SVGs used as `<x-ph name="…">`
(`<x-icon>` is taken by Filament's blade-icons).
