# Completion report — H & T Integrated Farms website

Date: 30 October 2026

## Delivered

- **Public site:** all 11 handoff screens at desktop and mobile, plus the screens the handoff left
  undesigned: Poultry, News list and article, Privacy, Terms, Cookie Policy, Sitemap and error pages. Built
  on the design tokens. See [IMPLEMENTATION_CHECKLIST.md](IMPLEMENTATION_CHECKLIST.md).
- **Enquiries:**
  - Contact, Partnership and the three-step Quote form (with save-and-resume drafts sent as signed links).
  - "Notify me" stock alerts.
  - Queued sales alerts and auto-replies.
  - Idempotent, transactional recording with human-readable references (`HTQ-2610-7K3F9`).
  - Abuse protection: honeypot, minimum fill time, rate limiting and optional Cloudflare Turnstile.
- **Admin (`/admin`, Filament 5):**
  - Enquiry inbox with assignment and status workflow.
  - Products and categories, gallery, farm updates, homepage slides, headline figures.
  - Site settings (contact details, WhatsApp, map, notification recipients) and staff accounts.
  - Role-based access (admin / sales / editor) enforced by policies.
- **Data:** 12 models with migrations, indexes, factories and an idempotent `ContentSeeder` holding all
  handoff content and imagery (optimised from ~30 MB to web sizes, with WebP 400/800/1600 conversions).
- **Front end:** strict TypeScript Alpine components (hero slider, mobile nav, consent store and analytics
  loader, gallery and lightbox, Turnstile), registered on Livewire's bundled Alpine so it starts only once.
- **CI/CD:** `ci.yml`, `deploy.yml`, `rollback.yml`. All actions are pinned to commit SHAs, permissions are
  `contents: read`, and each environment has its own concurrency group. Releases are SHA-versioned artifacts
  with checksums, deployed over SSH with a verified host key.
- **Server:** the `deploy/` directory holds:
  - provisioning script;
  - Nginx vhost;
  - PHP-FPM pool and OPcache ini;
  - Supervisor workers;
  - systemd scheduler and backup timers;
  - logrotate config;
  - restricted sudoers rule;
  - deploy, rollback and backup scripts.
- **Docs:** README (local setup), ARCHITECTURE, FRONTEND, DEPLOYMENT (setup, deploy, migration strategy,
  rollback, backups and restore, troubleshooting), ENVIRONMENT (variables, GitHub secrets and variables).

## Checks performed (all passing on the final code)

| Check | Result |
|---|---|
| PHPUnit (feature, Livewire, unit, integration with HTTP/notification/queue fakes) | **100 tests, 463 assertions passed** (SQLite locally; CI runs the same suite on MySQL 8.4) |
| Playwright on desktop 1440 and mobile 390: consent, carousel, drawer focus trap, gallery lightbox, product filters, notify-me, contact and quote journeys, no horizontal overflow on 13 pages at 390/768/1280/1440 | **28 passed**, 4 skipped by design (viewport-specific) |
| Pint | passed |
| Larastan level 6 | 0 errors |
| `tsc --noEmit` (strict) | passed |
| `npm run build` | passed |
| `php artisan optimize` + `filament:optimize` (production caches) | passed, site served correctly with caches |
| Visual comparison against the artboards (homepage, quote; desktop and mobile) | matches layout, tokens and copy |

## Not verified (missing infrastructure or credentials)

- **No deployment has run yet.** There is no VPS or GitHub access from this environment. The workflows and
  scripts are complete, and the shell scripts pass `bash -n` locally and ShellCheck in CI, but none has run
  against a server. Prerequisites are in [DEPLOYMENT.md §1–2](DEPLOYMENT.md).
- **The GitHub workflows have not run.** They are written for `ubuntu-24.04` runners and will run on the
  first push or PR.
- **Real email delivery, Turnstile and analytics** are covered only by fakes and mocks. They need SMTP
  credentials, Turnstile keys and an analytics ID.
- **MySQL** was not available locally (Laragon's MySQL wasn't running), so the suite ran on SQLite here.
  CI runs it on MySQL 8.4.

## Assumptions and deviations (details in ARCHITECTURE.md §4)

1. **Livewire 4 / Filament 5** instead of the handoff's Livewire 3 / Filament 3: they are the current
   supported majors for Laravel 13 and keep the same component API.
2. **Container gutter:** 120 px applies at ≥1440 px and 40 px between 768 and 1439. At 1280 px the designed
   nav does not fit inside 120 px gutters; this matches the handoff's "margins 120 / 40 / 20" note.
3. **Quote layout:** on desktop all three sections are visible as drawn; mobile is the stepped wizard.
   Mobile step 1 contains products only (the mock also showed delivery fields on step 1).
4. **Phones:** Nigerian numbers or any international E.164 number are accepted (the business has a UK line).
5. **Mobile pages** show every section (the mobile artboards omit some) and every form field, so validation
   is the same on all devices.
6. **WhatsApp** uses +234 810 780 7012 (editable).
7. **Map** loads only on request (privacy); it needs an embed URL in settings.
8. **Footer** has extra links (Sustainability, Gallery, Quote, Cookie Policy, Cookie settings) so no page is
   orphaned.

## Open items for the client

- Confirm or replace the **placeholder content**:
  - About "Our Story";
  - farm street address and LGA;
  - "Animals managed" figure;
  - sustainability figures (85 % / 120 / 64 / 48);
  - minimum order and lead time per product;
  - the three seeded farm-update articles.

  All are editable in the admin panel, except the About story and the legal pages, which are in Blade
  templates.
- **Imagery:**
  - Replace `grove.jpg` and `avenue.jpg` (date palms, not oil palms).
  - Supply a "transport" photo, and a higher-resolution Palm Kernels photo (the current one is 640 px wide).
  - Confirm licences for the AI-generated and Unsplash images.
  - Provide an SVG logo.
- **Legal review** of the Privacy, Terms and Cookie Policy pages (written against the NDPA 2023).
- **Choices to confirm:** the phone validation rule, the WhatsApp number, and the enquiry notification
  recipients.
- Not built (no design or entry point): site-wide search, the Palm Processing page, the Farm Operations page.
