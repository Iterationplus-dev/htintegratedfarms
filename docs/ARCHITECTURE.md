# Architecture — H & T Integrated Farms website

Source of truth for visuals and documented interactions: `docs/design_handoff_ht_farms_website/`
(22 artboards: 11 screens × desktop 1440 / mobile 390, plus README tokens and behaviour notes).

## 1. Stack and versions

| Layer | Choice | Notes |
|---|---|---|
| Runtime | PHP 8.4 (prod/CI), 8.4+ local | `composer.json` pins `config.platform.php = 8.4.0` so the lockfile always resolves for production. |
| Framework | Laravel 13 | Current major. |
| Server-driven UI | Livewire 4 | The handoff names Livewire 3. Livewire 4 is the supported major and keeps the v3 API the handoff relies on (`#[Validate]`, `#[Url]`, `wire:loading`). Filament 5 requires it. |
| Client behaviour | Alpine.js (bundled with Livewire) + TypeScript modules | We use **Livewire's own Alpine** from `livewire.esm`, so Alpine starts once. |
| Styling | Tailwind CSS 4 (`@theme` tokens in `resources/css/app.css`) | Tokens are CSS variables, so a future CMS could re-theme. |
| Build | Vite 8 + `laravel-vite-plugin`, `tsc --noEmit` strict | |
| Admin | Filament 5 panel at `/admin` | The handoff suggests Filament 3; Filament 5 is the version that supports Livewire 4 and Laravel 13. Themed with the brand tokens. |
| Media | spatie/laravel-medialibrary 11 | WebP conversions at 400/800/1600 px plus a 1600 px JPEG fallback, queued in production. |
| DB | MySQL 8.4 (prod, CI), SQLite in-memory (local unit/feature tests) | |
| Cache / queue | Redis (prod), database (local default) | Sessions use the database driver everywhere. |
| Mail | SMTP via `MAIL_*` (any provider) | Notifications are queued and sent after the DB commit. |
| Anti-spam | Honeypot + minimum fill time + `RateLimiter`, optional Cloudflare Turnstile | `CaptchaVerifier` contract with `TurnstileVerifier` and a null implementation, chosen from config. |

No other frontend framework is added.

## 2. Application structure

```
app/
  Actions/Enquiries/        RecordEnquiry (transaction + idempotency + notifications)
  Actions/Quotes/           SaveQuoteDraft, ResumeQuoteDraft
  Contracts/                CaptchaVerifier
  Enums/                    UserRole, EnquiryType, EnquiryStatus, ProductAvailability,
                            QuoteFrequency, GalleryCategory, GallerySpan, PartnershipType, AreaOfInterest
  Filament/                 Resources, Pages (Settings), Widgets
  Http/Controllers/         PageController, ProductController, PostController, SitemapController, HealthController
  Livewire/                 ProductIndex, ContactForm, PartnershipForm, QuoteWizard, StockAlertForm
  Livewire/Concerns/        ProtectsAgainstSpam (honeypot, timing, rate limit, captcha)
  Models/                   User, Product, ProductCategory, GalleryImage, Post, Enquiry, QuoteItem,
                            QuoteDraft, StockAlert, Stat, Setting, HeroSlide
  Notifications/            NewEnquiryReceived, EnquiryReceivedConfirmation, QuoteDraftSaved, ProductBackInStock
  Observers/                ProductObserver (back-in-stock alerts)
  Policies/                 one per admin-managed model
  Rules/                    PhoneNumber
  Services/                 SiteSettings (cached key/value), Captcha/TurnstileVerifier, Captcha/NullCaptchaVerifier
resources/
  css/app.css               Tailwind 4 + design tokens
  ts/                       app.ts + components/{heroSlider,mobileNav,cookieConsent,gallery,turnstile}.ts
  views/components/         layout, header, footer, buttons, eyebrow, section, product-card, stat-band, field…
  views/pages/              one Blade view per public screen
  images/                   optimised static imagery (+ .webp variants) used by Blade sections and seeders
  svg/phosphor/             Phosphor icons (MIT) used by <x-icon>
deploy/                     nginx, php-fpm, supervisor, systemd timer, logrotate, sudoers, backup, remote scripts
.github/workflows/          ci.yml (reusable), deploy.yml, rollback.yml
```

Controllers and Livewire components stay small. Persistence, idempotency and notification logic lives in actions.

## 3. Data model

| Model | Key columns | Relationships / notes |
|---|---|---|
| `User` | name, email, password, `role` (admin, sales, editor), `is_active` | Admin panel access needs `is_active` and any role. Policies check the role. |
| `ProductCategory` | name, short_name, slug, sort | hasMany Product. Seeded: Livestock, Farm Produce, Palm Products. |
| `Product` | category_id, name, slug, summary, featured_summary, description, `specs` JSON `[{label,value}]`, `availability` (in_stock, seasonal, on_request, out_of_season), unit, is_featured, is_published, sort | belongsTo category; media collection `images` (first image is the cover); hasMany StockAlert. Index on (is_published, category_id, sort). |
| `GalleryImage` | title, `category` enum, `span` (wide, tall, large, null), sort, is_published | media `image`. |
| `Post` | title, slug, excerpt, body (sanitised HTML), category_label, topic, reading_minutes, published_at, author_id | belongsTo User; media `cover`. Scope `published()`. |
| `Enquiry` | reference (unique, e.g. `HTQ-2610-7K3F9`), `type` (contact, quote, partnership), `status` (new, in_progress, responded, closed, spam), name, organisation, email, phone, subject (area of interest or partnership type), message, `payload` JSON (delivery location, required-by date, etc.), assigned_to, `idempotency_key` (unique), ip_hash, user_agent, responded_at | hasMany QuoteItem; belongsTo assignee (User). Indexes on (type, status, created_at). |
| `QuoteItem` | enquiry_id, product_id (nullable, nullOnDelete), product_name (snapshot), quantity (unsigned int ≥ 1), unit, `frequency` (one_off, weekly, fortnightly, monthly) | |
| `QuoteDraft` | uuid, email, `data` JSON, expires_at, resumed_at | Prunable after expiry. The resume link is a temporary signed URL. |
| `StockAlert` | product_id, email, notified_at | unique (product_id, email). Sent once when a product goes back in stock. |
| `Stat` | key (unique), page, value, label, short_label, sort | Editable homepage, about, palm and sustainability figures. |
| `Setting` | key (unique), value | Phones, emails, WhatsApp, address, hours, map URL, sales recipients. Cached by `SiteSettings`. |
| `HeroSlide` | eyebrow, mobile_eyebrow, title, body, mobile_body, primary/secondary CTA label + URL, image_position, sort, is_active | media `image`. |

## 4. Key decisions and assumptions

1. **Desktop quote form shows all three sections; mobile is stepped.** This follows the artboards. `$step` drives the mobile wizard (per-step validation on **Continue**). On desktop, **Submit** validates every section. Both step indicators come from the same server state.
2. **Phone validation** accepts the Nigerian format from the handoff (`^(\+234|0)[789][01]\d{8}$`) **or** any E.164 international number, because the business lists a UK line and has UK partners. Spaces, dashes and brackets are stripped before checking.
3. **Email DNS validation** (`email:rfc,dns`) is on in production. `ENQUIRY_VERIFY_EMAIL_DNS=false` turns it off for tests and CI, so the suite never makes DNS lookups.
4. **WhatsApp** uses the primary Nigerian number (+234 810 780 7012) until the client confirms. It is editable in Settings.
5. **Map embed** is click-to-load (Google Maps sets cookies). If no embed URL is set, the designed placeholder stays and links to directions.
6. **Analytics** is a configurable provider (`ANALYTICS_PROVIDER=none|plausible|ga4`). The script is injected only after analytics consent is given.
7. **Placeholder copy and figures from the handoff** (About story, "XX" minimum order and lead time, sustainability figures, farm street address) are seeded as editable content and listed in the completion report. "XX" becomes "On request". Nothing is hardcoded in templates.
8. **Images with known issues** (date palms in `avenue.jpg` and `grove.jpg`) are kept as supplied and listed for replacement. The empty "transport" thumbnail slot renders a designed "Image coming soon" placeholder.
9. **Undesigned screens** (Poultry, News list and article, Privacy, Terms, Cookie Policy, Sitemap, mobile nav drawer, cookie preferences modal, form success and error states, 404/500, admin) are built from the same tokens and components.
10. **Site search** (pending design) is not built. Product search exists on `/products`.
11. **`wire:navigate` is not enabled.** The site is content-led and loads third-party scripts (analytics, Turnstile), so full page loads are safer. Every Alpine/TS component still tears down in `destroy()` and is idempotent on init, so turning on SPA navigation later only needs the link attribute.

## 5. Delivery phases

1. Foundation: dependencies, tokens, layout, header/footer, icons, optimised images.
2. Data: migrations, models, enums, factories, seeders, settings service.
3. Content pages: Home, About, Livestock, Poultry, Palm, Sustainability, legal pages, sitemap, errors.
4. Catalogue and media: Products (Livewire filter/search), Product detail, Gallery and lightbox, News.
5. Enquiries: Contact, Partnership, Quote wizard with drafts, stock alerts, notifications, anti-spam.
6. Admin: Filament panel, resources, policies, settings page, dashboard widgets.
7. Quality: PHPUnit feature/unit/Livewire tests, Playwright browser checks, Pint, Larastan, `tsc`.
8. Delivery: GitHub Actions CI/CD, VPS templates, deploy/rollback scripts, documentation.
