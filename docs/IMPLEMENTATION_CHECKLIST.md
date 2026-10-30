# Design-handoff implementation checklist

Legend: ✅ done and verified (automated test and/or browser check) · ☑️ done, checked visually or by smoke test ·
⚠️ done with a documented deviation or open client input · ⏳ not done (out of scope or blocked)

## Screens (desktop 1440 + mobile 390)

| # | Screen | Route | Status | Notes |
|---|---|---|---|---|
| 01 | Homepage | `/` | ✅ | Hero slider (DB slides), stats strip (DB), Who We Are, Division A/B, featured products (DB), lifecycle, sustainability + gallery teaser (DB), partnerships, latest updates (DB), CTA. On mobile, all sections are shown (the mobile artboard omits some), stacked. |
| 02 | About Us | `/about` | ⚠️ | "Our Story" is placeholder text in the handoff; a neutral version is in place pending the founding account. The About "15k+ Animals managed" figure is a placeholder (editable in Admin → Headline figures). |
| 03 | Livestock | `/livestock` | ✅ | |
| 04 | Palm Production | `/palm-oil` | ⚠️ | Hero and plantation images (`grove.jpg`, `avenue.jpg`) show date palms; replace with oil-palm photos. |
| 05 | Products | `/products` | ✅ | Livewire `ProductIndex`: `#[Url]` category, search, availability filter, pagination, empty and loading states. |
| 06 | Product Detail | `/products/{slug}` | ⚠️ | "Minimum order" and "Lead time" were "XX" and are seeded as "On request". The 4th thumbnail ("transport") renders the designed placeholder. |
| 07 | Sustainability | `/sustainability` | ⚠️ | Not in the main nav (as designed); linked from the homepage and footer. The four figures are client placeholders (editable). |
| 08 | Gallery | `/gallery` | ✅ | Chip filter, dense grid with spans, lightbox (←/→/Esc, swipe, focus trap), lazy loading, responsive srcset. |
| 09 | Partnerships | `/partners` | ✅ | |
| 10 | Contact | `/contact` | ⚠️ | Farm street address is a placeholder ("Farm address, LGA"), editable in Admin → Site settings. The map is click-to-load and needs a Google Maps embed URL in settings. |
| 11 | Request a Quote | `/quote` | ✅ | Desktop shows all sections with a live summary; mobile is a stepped wizard. "Save and continue later" emails a signed link valid for 14 days. |

## Global components

| Item | Status | Notes |
|---|---|---|
| Design tokens (colour, type, radius, shadow, breakpoints) | ✅ | `resources/css/app.css` `@theme`; DM Sans variable (self-hosted). |
| Desktop header + active-nav glow | ✅ | Active state comes from the route (`aria-current="page"`). |
| Mobile header + slide-over drawer | ✅ | Drawer not drawn in the handoff; built with a focus trap, Escape to close and scroll lock. |
| Buttons, chips, form fields, focus ring | ✅ | Error and focus states follow the handoff text (no drawn state). |
| Cookie banner (desktop/mobile) + preferences modal | ✅ | `ht_consent` cookie for 180 days; analytics loaded only after consent. |
| Footer | ⚠️ | Sustainability, Gallery, Request a Quote, Cookie Policy and "Cookie settings" links added so no page is orphaned. |
| Icons (Phosphor) | ✅ | Inline SVG `<x-ph>`. |
| Images (WebP 400/800/1600 + JPEG fallback, lazy loading) | ✅ | Media Library conversions for CMS images; pre-built variants for static imagery. |

## Interactions and behaviour

| Behaviour | Status |
|---|---|
| Hero: 6 s autoplay, manual nav resets the timer, 0.9 s fade, Ken Burns 1.06 over 6.5 s, pause on hover/focus/hidden tab, reduced motion, carousel ARIA, live region | ✅ |
| Form validation: required fields, `email:rfc,dns`, Nigerian/E.164 phone, quantity ≥ 1, date ≥ today, inline errors with `aria-invalid`/`aria-describedby` | ✅ |
| Submit disabled while `wire:loading`; success replaces the form; focus moves to the confirmation | ✅ |
| Rate limiting, honeypot, minimum fill time, optional Turnstile | ✅ |
| Queued sales alert + auto-reply, sent after commit | ✅ |
| Duplicate-submission protection (idempotency key, unique index) | ✅ |
| "Notify me" for out-of-season products → one email when back in stock | ✅ |

## Screens marked "pending design" in the handoff (built from existing tokens)

| Item | Route | Status |
|---|---|---|
| Poultry Production | `/poultry` | ☑️ Composed from handoff copy and sections |
| News list + Article detail | `/news`, `/news/{slug}` | ✅ HTML sanitised |
| Privacy Policy, Terms, Cookie Policy | `/privacy-policy`, `/terms`, `/cookie-policy` | ⚠️ Written against the NDPA 2023; **needs legal review** |
| HTML sitemap + `sitemap.xml` | `/sitemap`, `/sitemap.xml` | ✅ |
| Error pages (404/419/429/500/503) | — | ✅ |
| Form error/success states, mobile drawer, cookie preferences modal | — | ✅ |
| Admin CMS (dashboard, products, enquiries, gallery, content, settings, users) | `/admin` | ✅ Filament 5 themed with brand colours |
| Palm Processing, Farm Operations | — | ⏳ No content or route in the handoff; Palm Production covers processing |
| Site-wide search results | — | ⏳ No entry point in the design; product search exists on `/products` |

## Data, roles and administration

| Item | Status |
|---|---|
| Models: Product, ProductCategory, GalleryImage, Post, Enquiry, QuoteItem, QuoteDraft, StockAlert, Stat, Setting, HeroSlide, User | ✅ |
| Migrations with FKs and indexes; factories; idempotent `ContentSeeder`; dev-only accounts | ✅ |
| Roles: admin / sales / editor; policies enforced server-side; inactive accounts blocked | ✅ |
| Enquiry inbox: filters, "Assign to me", status workflow, mark as spam, reply link | ✅ |
| Editable headline figures, homepage slides and site settings (phones, WhatsApp, address, map, recipients) | ✅ |
| `app:create-user` command for production accounts | ✅ |
