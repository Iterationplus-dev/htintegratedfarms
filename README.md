# H & T Integrated Farms — website

Marketing and enquiry website for H & T Integrated Farms Limited (Ogun State, Nigeria). It covers the farm's
operations and products, takes contact, quote and partnership enquiries, shows a photo gallery and farm updates,
and has a staff admin panel for content and enquiries.

**Stack:** Laravel 13 · Livewire 4 · Alpine.js (bundled with Livewire) · Tailwind CSS 4 · TypeScript (strict) ·
Vite · Filament 5 (admin) · Spatie Media Library · MySQL 8.4 · Redis.

| Document | Contents |
|---|---|
| [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) | Architecture, data model, decisions and assumptions |
| [docs/IMPLEMENTATION_CHECKLIST.md](docs/IMPLEMENTATION_CHECKLIST.md) | Each design-handoff screen and feature, with status |
| [docs/FRONTEND.md](docs/FRONTEND.md) | How TypeScript, Alpine and Livewire fit together |
| [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md) | VPS setup, CI/CD, deploys, migrations, rollback, backups, troubleshooting |
| [docs/ENVIRONMENT.md](docs/ENVIRONMENT.md) | Environment variables and GitHub secrets/variables |
| [docs/COMPLETION_REPORT.md](docs/COMPLETION_REPORT.md) | What was delivered, what was verified, open items |
| [docs/design_handoff_ht_farms_website/](docs/design_handoff_ht_farms_website/) | The approved design handoff (source of truth for visuals) |

## Local setup

Requirements: PHP 8.4+ (extensions: gd with WebP, intl, mbstring, pdo_sqlite or pdo_mysql, zip, exif),
Composer 2, Node 22+.

```bash
composer install
npm ci
cp .env.example .env
php artisan key:generate
touch database/database.sqlite        # default local DB is SQLite; MySQL settings are in .env.example
php artisan migrate --seed            # content from the handoff + dev accounts (≈1 min: generates image sizes)
php artisan storage:link
npm run build                         # or `npm run dev` for hot reload
php artisan serve                     # http://127.0.0.1:8000
```

On Laragon the site is also served at `http://htintegratedfarms.test` (the default `APP_URL`).

**Development accounts** (created only outside production; password `password`):
`admin@htintegratedfarms.test`, `sales@htintegratedfarms.test`, `editor@htintegratedfarms.test` → `/admin`.

Locally, mail is written to `storage/logs/laravel.log` (`MAIL_MAILER=log`). Queued notifications and image
conversions need a worker: `php artisan queue:work`.

## Quality checks

```bash
vendor/bin/pint --test                 # formatting
vendor/bin/phpstan analyse             # Larastan, level 6
npm run typecheck                      # tsc --noEmit (strict)
php artisan test                       # PHPUnit: feature, Livewire, unit, integration (fakes)
npm run build                          # production assets
npx playwright install chromium        # once
npm run test:browser                   # browser journeys + responsive overflow checks (needs a seeded app)
```

CI runs all of these on every pull request (`.github/workflows/ci.yml`). Pushing to `staging` or `main`
validates, builds and deploys (`.github/workflows/deploy.yml`).

## Roles

| Role | Admin access |
|---|---|
| Administrator | Everything, including staff accounts and site settings |
| Sales | Dashboard and enquiries (view, assign, update status) |
| Content editor | Products, categories, gallery, farm updates, homepage slides, headline figures |

Create a production account with `php artisan app:create-user`.
