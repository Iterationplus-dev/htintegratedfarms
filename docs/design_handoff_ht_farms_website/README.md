# Handoff: H & T Integrated Farms Limited — Public Website (TALL + TypeScript)

## Overview
Marketing and enquiry website for **H & T Integrated Farms Limited**, an integrated agricultural enterprise in Ogun State, Nigeria (livestock, poultry, eggs, oil palm cultivation and processing). The site presents operations, lists products, collects quote/partnership/contact enquiries, and shows a photo gallery. 11 public screens are designed (desktop 1440 + mobile 390). Admin CMS and remaining public screens are **not yet designed** (see "Out of scope / pending").

Target stack: **Laravel 11+, Livewire 3, Alpine.js, Tailwind CSS (v3.4 or v4), TypeScript** (Vite-compiled, used for Alpine components and shared front-end utilities).

## About the Design Files
The files in this bundle are **design references created in HTML** — prototypes showing intended look, copy and behaviour, not production code. Do not ship the HTML. Recreate the designs as Blade views + Livewire components + Alpine/TS, styled with Tailwind utilities driven by the tokens below.

`HT Farms - Website UI.dc.html` opens directly in a browser (keep `support.js` and `assets/` beside it). All screens sit on one scrollable canvas, grouped in numbered sections; each artboard has a `data-screen-label` ("Homepage / Desktop", "Homepage / Mobile", …). Styles are inline — read them for exact values.

## Fidelity
**High-fidelity.** Final colours, typography, spacing, copy and imagery. Recreate pixel-accurately. Exceptions noted inline: some stats are placeholder figures supplied by the client for design purposes, and filters/forms are static in the mock.

---

## Design Tokens

### Colours
| Token | Hex | Use |
|---|---|---|
| `ink` | #17211B | Primary text, headings |
| `ink-2` | #4A5851 | Body / secondary text |
| `ink-3` | #7A8781 | Muted text, placeholders |
| `bg` | #F6F8F4 | Alternate section background |
| `surface` | #FFFFFF | Page / card surface |
| `sand` | #F0F1EA | Warm section background (stats band on Sustainability) |
| `line` | #E1E7DF | Borders, dividers |
| `input-border` | #CFD8CE | Form field borders |
| `g900` | #0E3B21 | Deep green: dark sections, hero panel, footer |
| `g800` | #14512C | |
| `g700` | #1B6236 | **Primary**: buttons, active nav, links |
| `g600` | #237A43 | Primary hover, eyebrow text |
| `g500` | #2E9150 | Active-nav underline, accents |
| `g100` | #E7F2E9 | Tinted fills, tags |
| `leaf` | #6DBE45 | Small highlights/icons |
| `navy` | #123C55 | Secondary brand (from logo), badges |
| `navy-600` | #1B5E86 | |
| `sky` | #3E9BD1 | Info accents |
| `navy-100` | #E7EFF4 | Navy tint fill |
| `stats-dark` | #07140C | Homepage stats strip background (near-black green) |
| `on-dark-muted` | #DCE8DE | Body text on g900 |
| `on-dark-accent` | #9FD8A9 | Eyebrows / stat labels on dark |
| `lightbox-bg` | rgba(9,20,13,.86) | Lightbox backdrop |
| Canvas only | #EDEFEA | Presentation canvas behind artboards — not part of the site |

Tailwind: extend `theme.colors` with these keys (e.g. `bg-g700`, `text-ink-2`, `border-line`) or define as CSS variables and map in config so the CMS could theme later.

### Typography
- Single family: **DM Sans** (Google Fonts, optical size axis `opsz 9..40`, weights 400/500/600/700/800). Self-host via `@fontsource-variable/dm-sans` or Google link.
- Headings: weight 700, `letter-spacing:-0.02em`, `line-height:1.1`, `text-wrap:balance`.
- Body: 400, `line-height:1.6–1.7`, `-webkit-font-smoothing:antialiased`.

| Role | Desktop | Mobile |
|---|---|---|
| Hero H1 (slider) | 56px / 1.06 | 30px / 1.1 |
| Page H1 | 46–52px | 31px |
| Section H2 | 40px (34–42 variants) | 27px |
| Card H3 | 17–22px | 17–19px |
| Lead paragraph | 17–18px / 1.7 | 15px / 1.65 |
| Body | 14–15px / 1.6–1.7 | 14–15px |
| Eyebrow | 600 12px, `letter-spacing:.16–.18em`, uppercase, g600 (or #9FD8A9 on dark) | 600 11px |
| Form label | 600 11px, `.1em`, uppercase, ink-2 | same |
| Big stat | 800 40px (light bands) / 800 30px (dark bands), `-0.03em` | 800 22–30px |
| Nav link | 500 15px ink-2; active 600 g700 | — |
| Buttons | 600 14–15px | 600 15px |

### Spacing & layout
- Desktop artboard 1440px; content gutter **120px** left/right (content width 1200px). Header padding `16px 120px`.
- Section vertical padding: 70–96px desktop; 34–56px mobile. Mobile gutter **20px**.
- Common gaps: 8, 10, 12, 14, 16, 18, 22, 24, 28, 32, 44px. Card padding 20–28px.
- Grids: 2-col (1fr 1fr) feature blocks; 3- and 4-col card grids; gallery 4-col × 210px rows.
- Breakpoints for build: **320+ base, 768 (md), 1280 (xl)**; the mobile artboard represents <768, desktop represents ≥1280. Tablet interpolates (2-col grids, 40px gutters).

### Radius
`r` 8px (buttons, inputs, images, small cards) · `r-lg` 14px (cards, panels, cookie banner) · 99px pills (filter chips, tags) · 22px mobile device frame (mock only).

### Shadows
- `sh-sm`: `0 1px 2px rgba(14,59,33,.06), 0 1px 1px rgba(14,59,33,.04)`
- `sh-md`: `0 4px 14px rgba(14,59,33,.07), 0 1px 3px rgba(14,59,33,.05)`
- `sh-lg`: `0 18px 44px rgba(14,59,33,.10), 0 2px 8px rgba(14,59,33,.05)`
- Active-nav glow: `box-shadow: 0 6px 10px -4px rgba(46,160,90,.75), 0 2px 0 0 rgba(46,160,90,.35)` with `border-bottom:3px solid #2E9150; padding-bottom:3px`.

---

## Global Components

### Header (desktop)
- Flex row, `padding:16px 120px`, `border-bottom:1px solid line`, white.
- Logo `assets/ht-logo-v2.png` at **140×52**, `object-fit:contain; object-position:left; mix-blend-mode:multiply`.
- Nav pushed right (`margin-left:auto`), `gap:24px`, nowrap. Items, in order: **Home · About Us · Livestock · Poultry · Palm Oil · Products · Gallery · Partners · Contact**.
  - Default: 500 15px ink-2, `padding-bottom:6px; border-bottom:2px solid transparent`; hover colour g700.
  - Active (current route): 600 g700 + glow underline (see Shadows). Use `request()->routeIs()`.
- CTA button **Request a Quote**: solid g700, white 600 14px, `padding:12px 20px`, min-height 44, radius 8. Hover g600.
- Sustainability page exists but is intentionally **not in the nav** (link from footer/homepage only). "Poultry" route has no screen yet.
- Below 1280px collapse to the mobile header.

### Header (mobile)
Logo 110×40 left; 44×44 hamburger button (1px line border, radius 8, three 18×2 ink bars, gap 4) right. `padding:12px 20px`. Menu drawer not designed — build as full-height slide-over (Alpine `x-show` + focus trap) listing the same nav items + Request a Quote button.

### Buttons
- Primary: bg g700, text white, radius 8, `padding:12–15px 20–28px`, min-height 44–48. Hover g600.
- Secondary/outline: transparent, 1px g700 border, g700 text. Hover bg `bg`.
- On dark: white fill with g900 text (primary) / transparent with `1px rgba(255,255,255,.55)` border (secondary).
- Text link: 600 g700, often trailing "→".
- Focus: `outline:2px solid #2E9150; outline-offset:2px` (`focus-visible` only).

### Filter chips
Pill 99px, `padding:9px 18px`, 600 13px. Active: bg g700 white text. Inactive: 1px line border, ink-2.

### Form fields
Label above (600 11px uppercase ink-2, `margin-top:7px` to field). Field: 1px #CFD8CE border, radius 8, `padding:13px 14px`, 400 14px. Textarea 120px tall. Selects show ▾. Required marked "*" in label. Error/focus states not drawn — use focus border g600 + ring `0 0 0 3px #E7F2E9`; error border #B42318 with 13px message below.

### Cookie consent (Homepage, shown on first visit site-wide)
- Desktop: floating card `left/right/bottom:24px`, surface, 1px line, radius 14, sh-lg, `padding:24px 28px`, flex row gap 32. Title "We value your privacy" (700 16px). Copy (14px/1.6 ink-2, max-width 760): *"We use essential cookies to run this website. With your permission, we also use analytics cookies to understand how visitors use it. You can accept all, reject non-essential cookies, or choose your settings. See our Cookie Policy."* Actions: **Manage preferences** (underlined text button) · **Reject non-essential** (outline) · **Accept all** (primary).
- Mobile: card at 12px inset, buttons "Reject all" / "Accept all" in 2-col grid, "Manage preferences" full-width underneath.
- Behaviour: persist choice in a first-party cookie `ht_consent` (`{essential:true, analytics:bool, v:1, ts}`) for 180 days; don't load analytics until granted. "Manage preferences" opens a modal with toggles (Essential – locked on, Analytics) — modal not designed; use dialog pattern: surface, radius 14, sh-lg, backdrop rgba(9,20,13,.6).

### Footer
Present on the canvas per screen as the final g900 block — follow the Homepage footer for content/layout (logo, nav columns, three phone numbers, Ogun State address, © line).

---

## Screens / Views

### 01 Homepage (`/`)
1. **Hero slider** — 620px tall desktop, bg g900, overflow hidden. 3 slides, cross-fade.
   - Image occupies the **right 64%** (`object-fit:cover`), text on the **left**.
   - Overlay: `linear-gradient(90deg, #0E3B21 0%, #0E3B21 36%, rgba(9,32,18,.7) 52%, rgba(9,32,18,.25) 75%, rgba(9,32,18,.1) 100%)`.
   - Text block: `padding:0 120px 40px`, max-width 820, vertically centred. Eyebrow (#9FD8A9) → H1 56px white max 640 → paragraph 18px/1.7 #DCE8DE max 560 → buttons (gap 14, mt 34): **Explore Our Farm** (white/g900) + **Our Operations** (outline white).
   - Slides:
     1. `hero-palm.png` · eyebrow "Integrated Agriculture · Ogun State, Nigeria" · H1 "Building Sustainable Livestock and Palm Production" · "H & T Integrated Farms combines responsible livestock management, modern farming practices, palm cultivation and agricultural processing to deliver sustainable food production and long-term agricultural value."
     2. `hero-poultry-field.png` · "Poultry Production" · "50,000 birds raised under managed care" · "Scheduled feeding, clean water and veterinary oversight across our poultry houses and open runs."
     3. `hero-poultry-house.jpg` · "Egg Production" · "Consistent egg supply for commercial buyers" · "Daily collection, grading and packing for retailers, distributors and food businesses that need volume on schedule."
   - Controls row at `bottom:40px`, left/right 120: counter "01 / 03" (600 14px, "/ 03" at 50% white) · 3 progress bars (24px hit area, 3px bar, radius 2; active 56px wide white, inactive 28px rgba(255,255,255,.3); width transition .4s) · prev/next 44×44 outline-white buttons pushed right.
   - Mobile: 520px tall, image full-bleed, overlay `linear-gradient(180deg, rgba(9,32,18,.2) 0%, rgba(9,32,18,.75) 45%, rgba(9,32,18,.96) 100%)`, text bottom-anchored (`padding:24px 20px 76px`), H1 30px, short copy, single full-width **Explore Our Farm** button; bars + arrows at bottom 20px.
2. **Stats strip** — bg **#07140C**, `padding:44px 120px`, 4-col grid, white 800 numbers + #9FD8A9 labels. Includes **240 acres**, **50,000 birds**, **15k+ Palm trees cultivated**, **36 Active farm operations**. Mobile 2×2.
3. **Who We Are** — 2-col, `padding:96px 120px`. Left: eyebrow, paragraph, two feature notes, **About H & T** button. Right: H2 "An integrated agricultural enterprise, managed professionally" (40px, max 520) **above** a 2×2 image grid (rows 200px, gap 16; first image spans 2 rows) — cattle, pullets, seedlings.
4. Livestock Production & Management (cards: Cattle Production, Poultry Production, Animal Health & Feed) → "View livestock operations →".
5. Oil Palm Cultivation and Processing → **Palm Production** button.
6. Agricultural Products / Featured Products (Table Eggs, Broiler Chickens, Palm Oil, Market-Weight Cattle) with "Request quote" per card, "All products →".
7. Livestock Production Lifecycle (step strip).
8. Sustainability teaser "Farming that the land can carry" → **Our commitments**.
9. Partnerships teaser "Growing Agricultural Value Through Strategic Partnerships" → **Become a Partner**.
10. Farm Gallery teaser → "Open gallery →".
11. Latest Farm Updates — 3 article cards ("What consistent lay rates actually require", "Planning harvest rounds around bunch maturity", "Biosecurity routines for a growing herd") → "All insights →".
12. CTA band "Talk to us about supply, partnership or a farm visit" → **Visit Our Farm**.
13. Footer.

### 02 About Us (`/about`)
H1 "An agricultural company built for the long term"; hero image `farm.jpg`; history "From a single production unit to an integrated farm"; Vision / Mission / Core Values cards; management "Recorded, scheduled, supervised" with `team.jpg`; CTA "Come and see the operation for yourself" → **Request a Farm Visit**.

### 03 Livestock Production (`/livestock`)
H1 "Professional Livestock Production & Management"; "Six areas we manage on the herd" — Breeding, Nutrition, Housing, Health, Biosecurity, Growth monitoring (3×2 grid); poultry block "Broilers, layers and table eggs"; biosecurity "Disease prevention is a routine, not a response"; "Livestock Production Cycle" steps; CTA "Need livestock supplied on a schedule?".

### 04 Palm Production (`/palm-oil`)
H1 "Oil Palm Cultivation, Harvesting and Processing"; hero `grove.jpg`; "The palm production lifecycle" cards: Palm Nursery, Plantation Management, Harvesting; "From fresh fruit bunch to packaged oil" with dark stats band: **10M+ FFB processed / season**, **240+ Hectares in production**; CTA "Buying palm oil, kernels or fresh fruit bunches?".

### 05 Products (`/products`)
H1 "Agricultural Products" + lead; filter-chip bar (bg `bg`, top border) starting "All products"; 4-col product card grid: Table Eggs, Broiler Chickens, Point-of-Lay Birds, Market-Weight Cattle, Palm Oil, Fresh Fruit Bunches, Palm Kernels (image pending), Processed Poultry. Card CTA **Request quote**; out-of-stock card shows **Notify me**. Mobile: list with compact **Quote** buttons.
- Livewire `ProductIndex` with `#[Url] public string $category`; chips filter server-side.

### 06 Product Detail (`/products/{slug}`)
Example: Table Eggs. Gallery: main image + thumbnails (gather, pullets, cage; 4th "transport" pending). Specs, pricing-on-request, **Order Inquiry** (primary) and **Call** actions; "Related products" (Point-of-Lay Birds, Broiler Chickens, Processed Poultry, Palm Oil).

### 07 Sustainability (`/sustainability`, not in nav)
H1 "Responsible farming, stated plainly"; commitment cards: Animal welfare, Sustainable feed practice, Waste management, Soil protection, Water management, Local employment. Sand stats band (800 40px g900): **85%** Farm waste returned to land · **120** People employed locally · **64** Hectares under cover management · **48** Veterinary reviews per year *(placeholder figures — confirm with client)*.

### 08 Gallery (`/gallery`)
H1 "Farm Gallery" + lead "Photographs from the livestock units, the plantation and the processing floor."; chips: All, Cattle, Poultry, Palm Plantation, Processing, Equipment, Farm Team (mobile: All, Cattle, Poultry, Palm — horizontal scroll).
- Desktop grid: 4 cols, `grid-auto-rows:210px`, `grid-auto-flow:dense`, gap 16, 20 unique images. Spans: farm 2×2; avenue, hero-palm, grove, hero-poultry-field 2×1; chicks 1×2. Tiles radius 8, `cursor:zoom-in`, image hover `scale(1.04)` over .4s.
- Mobile: 2 cols, 130px rows, gap 10, 11 images.
- **Lightbox** (shown open in mock): backdrop rgba(9,20,13,.86), panel 900px, bg #0E1A12, radius 14; image 500px tall cover; caption bar `padding:20px 26px` — title (600 15px white), meta "Farm · 1 of 20" (13px #7E9585); prev / next / close 44×44 outline buttons. Keyboard: ←/→/Esc; focus trap; swipe on touch.

### 09 Partnerships (`/partners`)
H1 "Growing Agricultural Value Through Strategic Partnerships"; partner-type grid: Distributors, Retailers, Agricultural suppliers, Veterinary services, Feed suppliers, Processors, Financial partners, Investors, Government programmes, Institutions. "Become a Partner" form: Organisation, Contact person, Email, Phone, Partnership type (select), Proposal (textarea) → **Submit Partnership Inquiry** (mobile: **Submit Inquiry**, fewer fields: Organisation, Email, Partnership type, Proposal).

### 10 Contact (`/contact`)
H1 "Contact Us"; contact details (three phone numbers, Ogun State address), **Chat on WhatsApp**; "Send a message" form: Full name*, Organisation, Phone*, Email*, Area of interest* (single-select chips: Livestock purchase, Poultry, Eggs, …), Message* → **Send Message**. Mobile: Call / WhatsApp quick actions; fields Full name*, Phone*, Area of interest*, Message*.

### 11 Request a Quote (`/quote`)
3-step form: **1 · What do you need?** (product line items with thumbnail 52×52, name, category, Quantity, Frequency select e.g. "Weekly"; **+ Add another product**) → **2 · Delivery** (Preferred delivery location*, Required by*) → **3 · Your details** (Full name*, Organisation, Phone*, Email*) → **Submit Quote Request**; secondary **Save and continue later**. Mobile is a stepped wizard with **Continue**.

---

## Interactions & Behaviour
- **Hero slider** (Alpine + TS `heroSlider()`): autoplay every **6000ms**; manual nav resets the timer. Slide opacity transition `.9s ease`; active image `transform:scale(1.06)` over `6.5s ease-out` (Ken Burns), inactive `scale(1)`. Inactive slides `pointer-events:none` and `aria-hidden`. Pause on hover/focus and when `prefers-reduced-motion` (also disable zoom). Region `aria-roledescription="carousel"`, live region announces "Slide n of 3".
- **Nav**: hover colour only; active derived from route.
- **Gallery**: chip filter (Livewire or Alpine client-side over a JSON list), lightbox as above, lazy-load images (`loading="lazy"`, responsive `srcset`).
- **Forms** (Livewire 3 with `#[Validate]`): required fields as marked; email `email:rfc,dns`; phone Nigerian format (`/^(\+234|0)[789][01]\d{8}$/` — confirm); quantity integer ≥1; required-by date ≥ today. Show inline errors, disable submit while `wire:loading`, success state replaces form with confirmation message. Rate-limit (Laravel `RateLimiter`) + honeypot/Turnstile. Queue notification email to sales + auto-reply.
- **Quote wizard**: `public int $step`, per-step validation before advancing; "Save and continue later" stores draft (session or signed URL emailed).
- **Cookie consent**: see Global Components.
- Transitions elsewhere: 150–200ms colour/background on hover.

## State Management / Data
Suggested models (to back the future CMS): `Product` (name, slug, category enum [livestock, poultry, eggs, palm], summary, body, specs JSON, images, in_stock, sort), `ProductCategory`, `GalleryImage` (path, alt, category, featured span: `wide|tall|large|null`, sort), `Post` (news), `Enquiry` (type enum [contact, quote, partnership], payload JSON, status, assigned_to), `QuoteItem`, `Stat` (key, value, label, page) so the homepage/palm/sustainability figures are editable, `Setting` (phones, address, WhatsApp number, social), `HeroSlide` (image, eyebrow, title, body, cta labels/urls, sort).
Use Spatie Media Library (or equivalent) for image conversions: 1600w, 800w, 400w WebP + JPEG fallback.

TypeScript: `resources/ts/` with typed Alpine components (`heroSlider`, `lightbox`, `cookieConsent`, `mobileNav`), registered in `app.ts` via `Alpine.data()`. Enable `strict`.

## Assets
- `assets/ht-logo-v2.png` — primary logo (trimmed). `ht-logo.png` / `ht-logo-src.png` = earlier/source versions. Request SVG from client.
- `assets/hero-palm.png`, `hero-poultry-field.png`, `hero-poultry-house.jpg` — hero slides.
- `assets/img/*.jpg` (resized to ≤1600px long edge, JPEG q≈82): avenue, broilers, cage, cattle, chicks, eggs, farm (≤2000px), ffb, gather, grove, harvest, hens, oil (H&T branded palm oil containers), processed, pullets, seedlings, team, whitehens. Client-supplied/AI-generated and Unsplash imagery — confirm licences and replace with real farm photography where possible.
- **Known image issues**: `avenue.jpg` and `grove.jpg` show date palms, not oil palms — replace. Empty slots: Palm Kernels product, Product Detail "transport" thumbnail, two Quote line-item thumbnails.
- Icons: none drawn beyond ←/→/✕/▾ glyphs — use **Phosphor** (`@phosphor-icons/web` or Blade icons) for arrows, close, caret, phone, WhatsApp, menu.

## Out of scope / pending design
Poultry Production, Palm Processing, Farm Operations, News list + Article Detail, Search Results, Privacy Policy, Terms & Conditions, Cookie Policy, mobile nav drawer, cookie preferences modal, form error/success states, and admin CMS (Dashboard, Product, Enquiry, Gallery, Content management). Build these on the same tokens/components; admin can use Filament 3 themed with these tokens.

## Files
- `HT Farms - Website UI.dc.html` — all screens (open in browser).
- `support.js` — runtime needed to open the design file.
- `assets/` — logo, hero and content images.
