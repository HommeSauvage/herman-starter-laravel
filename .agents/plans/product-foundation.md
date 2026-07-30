# Product foundation: make the starter worth cloning

**Status:** Ready for implementation
**Author:** Research session (2026-07-29), grounded in this repo's actual code
**Audience:** Implementation agent working in `herman-starter-laravel`. Read this
file fully before touching code. This repo's own `AGENTS.md` (boost guidelines)
applies throughout — pint, pest, tsc, eslint, prettier stay green.

---

## 0. Why this plan exists

Herman's rookie wizard clones this repo for every Laravel-family project
(`laravel`, `saas`, `blog`, `store`, `landing` templates). Three wizard phases
consume this repo directly:

1. **The design phase** reads it to learn structure and retheme it (tokens,
   fonts, layout direction per project).
2. **The coding milestones** copy its patterns to build features — whatever this
   repo demonstrates is the ceiling of what generated projects look like.
3. **The QA phase** walks the routes the design derived from what exists here.

The templates already ask for things this starter has no foundation for. From
the desktop templates (informational — do not edit them from here):

- `saas.HERMAN.md`: *"Prefer a clear split between marketing (public) pages and
  the authenticated app. Public: landing/pricing."* — **this starter has no
  public layout, no marketing sections, no pricing anything.**
- `blog.HERMAN.md`: *"Make the UI for the blog posts … mimic the structure and
  layouts of Ghost or Medium blogs"* and *"Change the dashboard to show blog
  statistics instead of what's in there"* — **this starter's only public page
  is Laravel's own marketing page, and the dashboard is placeholder boxes.**
- Both: *"If the starter ships a reference Note module marked for deletion,
  remove it…"* — the REFERENCE MODULE convention is a real, working contract.
  This plan extends it.

Every weakness below is amplified into every generated project. Every building
block added here is one less thing a milestone agent (possibly a weak model) has
to invent — **assembly beats generation**.

## 0.5 Contracts that must NOT break (the wizard depends on them)

The desktop manifest (`apps/desktop/templates/laravel.HERMAN.md` in the
~/Projects/clique-herman repo) runs and gates on these; changing them breaks every wizard:

- Setup commands must keep working: `composer install`,
  `touch database/database.sqlite && php artisan migrate --force`,
  `php artisan db:seed`, `bun install`. Dev server: `composer run dev`.
- Gate checks must pass: `vendor/bin/pint --test`, `bunx tsc --noEmit`.
- `.env.example` keys stay compatible (`APP_NAME`, `APP_KEY`, `DB_*`, `APP_URL`).
- `REFERENCE MODULE` marker comments and the README deletion guide stay accurate
  (the wizard's milestone text tells agents to follow them).
- Token *names* in `resources/css/app.css` (`--background`, `--primary`,
  `--font-sans`, `--radius`, the `@theme` `--color-*` mapping, …) are the
  retheme API: the design phase rewrites their **values**, never their names.
- `bun run build:ssr` keeps working (SSR-capable build).
- Wayfinder keeps regenerating (`resources/js/actions`, `resources/js/routes`
  are generated — never hand-edit).

## 1. Diagnosis (what's actually in this repo today)

Verified against the code on 2026-07-29 (commit `55baa3c`).

**D1 — The only public page is Laravel's own marketing page.**
`resources/js/pages/welcome.tsx` (390 lines) is the unmodified stock Laravel
welcome: Laravel logo SVGs, links to `laravel.com/docs`, Laracasts, and a
"Deploy now" button to `cloud.laravel.com`. It hardcodes hex colors throughout
(`#FDFDFC`, `#1b1b18`, `#f53003`, `#161615` — zero design tokens), so it also
*teaches agents the worst possible pattern*. Generated projects either ship
Laravel-branded home pages or replace it with no in-repo reference for what a
good public page looks like.

**D2 — No public layout, and the layout resolution is a trap.**
`resources/js/app.tsx` maps `welcome` → no layout, `auth/*` → AuthLayout,
`settings|teams/*` → AppLayout+SettingsLayout, and **`default:` → AppLayout**.
Any new public page an agent creates silently gets wrapped in the authenticated
sidebar shell. Layouts today: `layouts/app/*`, `layouts/auth/*`,
`layouts/settings/*` — nothing for public pages.

**D3 — The dashboard is placeholder filler.**
`resources/js/pages/dashboard.tsx` renders four `PlaceholderPattern` boxes.
It's the first thing users see after login, and the template manifests
(blog/saas) already instruct agents to replace it — every project reinvents
"admin home" from nothing.

**D4 — The only quality reference (Notes) is team-scoped app CRUD.**
Notes (`pages/notes/*`, `components/notes/*`, `NoteController`, policy, factory,
Pest tests, empty state, search, pagination) is genuinely good — but it's
behind auth. There is **no reference for public content**: published scopes,
slug routing, public cards/detail pages, SEO heads. Agents copy Notes for
public features and produce admin-flavored public pages.

**D5 — Clash points for parallel work.**
`routes/web.php` is one file (the `require __DIR__.'/settings.php'` split is
the precedent to generalize). Nav entries are hardcoded in
`resources/js/components/app-sidebar.tsx` (Dashboard/Notes/"Open website").
When Herman's coding lanes (or any two agents) add routes/nav concurrently,
these two files are where they collide.

**D6 — The retheme surface is real but undocumented.**
Tokens: `resources/css/app.css` `@theme` + `:root`/`.dark` (shadcn neutral,
oklch). Fonts: `vite.config.ts` `laravel-vite-plugin/fonts` `bunny('Instrument
Sans')` + `@fonts` in `resources/views/app.blade.php` — fetched at **build
time**, self-hosted at runtime (no external font requests — keep it that way).
Radius: `--radius`. This is a clean retheme API; nothing tells the design
phase it exists or what it's allowed to touch, and nothing forbids hardcoded
colors in components (welcome.tsx is the offender-in-chief).

**D7 — Missing primitives and state conventions.**
`components/ui/` has 26 shadcn components but no `table`, `accordion`,
`aspect-ratio`, `tabs` — admin list UIs and FAQ/pricing sections get invented
per project. Empty state exists only inline inside Notes index; no shared
`EmptyState`, no `LoadingState`/skeleton convention doc (`ui/skeleton.tsx`
exists), no error pages (Inertia v3 supports custom error pages — unused).

**D8 — Thin first-run experience.**
Seeder: test user + 3 notes. Public home = Laravel marketing. After login =
placeholder boxes. Nothing demonstrates the stack to the rookie on first
preview.

**Keep and preserve (the good parts):** the tooling story (`composer test` =
pint + phpstan + pest; `ci:check` adds eslint/prettier/tsc), Boost skills in
`.agents/skills/`, Wayfinder typed routes, Fortify auth incl. passkeys/2FA,
teams with invitations/roles, dark-mode appearance system, Notes as the app-CRUD
quality bar, the build-time font pipeline, the REFERENCE MODULE convention.

---

## 2. Work items

Implement in order. Each item lists **Files** and **Acceptance**. Per repo
rules: add Pest tests for behavior, run `vendor/bin/pint --dirty --format agent`
after PHP changes, `bun run format` after frontend changes, and keep
`composer run ci:check` green at every commit. Work on a feature branch; the
deliverable is a PR against `master`.

### W1. Public layout + a layout rule that can't trap agents

**Fix:**
- New `resources/js/layouts/public-layout.tsx`: sticky responsive header —
  brand (shared `name` prop from `HandleInertiaRequests`, already shared),
  desktop nav from the W9 registry, mobile menu via the existing `sheet`
  component, auth-aware actions (Dashboard when logged in, else Log in /
  Register — use the existing `auth` shared prop and Wayfinder route helpers);
  `<main>` slot; footer (brand, nav columns, copyright).
- Fix the trap in `resources/js/app.tsx`: add
  `case name.startsWith('public/')` → `PublicLayout`, **before** the `default`
  case, and keep `default → AppLayout` for the authenticated app. Move
  `welcome.tsx` to `resources/js/pages/public/home.tsx` (the W4 rewrite lands
  there) and update `routes/web.php`: `Route::inertia('/', 'public/home')->name('home')`.
  Delete the `name === 'welcome'` special case.
- Document the rule in `AGENTS.md`: "Public pages live in
  `resources/js/pages/public/` and get `PublicLayout` automatically. Never put
  a public page outside `public/`; never point `default:` away from AppLayout."

**Files:** new `layouts/public-layout.tsx`; `resources/js/app.tsx`;
`routes/web.php`; `resources/js/pages/` (move); `AGENTS.md`.
**Acceptance:** a probe page `pages/public/probe.tsx` renders with public
header/footer and no sidebar with zero edits to `app.tsx` (verify, then delete
the probe); `pages/dashboard.tsx` still renders in the app shell; Pest feature
test: `GET /` returns 200 and contains the app name.

### W2. `Seo` component + per-page titles everywhere

**Fix:** `resources/js/components/seo.tsx` wrapping Inertia `<Head>`:
`<Seo title description?>`. The `title` callback in `app.tsx` already appends
the app name (`VITE_APP_NAME`) — `Seo` only standardizes usage and adds
`<meta name="description">` + `og:title`/`og:description`. Adopt it in every
starter page (public home, dashboard, notes, auth pages minimum). `AGENTS.md`
rule: "Every page renders `<Seo>`; never raw `<Head>` for titles."
**Files:** new `components/seo.tsx`; all pages under `resources/js/pages/`;
`AGENTS.md`.
**Acceptance:** every file in `resources/js/pages/**` imports `Seo` (grep);
`GET /` HTML contains `<title>` with the configured `APP_NAME` (Pest test with
`APP_NAME` overridden in the test env).

### W3. Sections library + missing shadcn primitives

**Fix:**
- Add via the shadcn CLI (vendored source, no services): `accordion`,
  `aspect-ratio`, `table`, `tabs`.
- New `resources/js/components/sections/` — typed, **token-only** (no hex, no
  arbitrary color values; `bg-background`, `text-muted-foreground`,
  `bg-primary`, `font-sans`, radii from `--radius`), each accepting
  `className`, composed on a shared `Section` wrapper (vertical rhythm +
  container widths):
  - `hero` (variants: `centered`, `split` with media slot)
  - `feature-grid` (icon+title+text cards, 2–4 col responsive)
  - `feature-rows` (alternating media/text)
  - `cta-band`
  - `stats-band`
  - `testimonial-band`
  - `pricing-table` (tiers prop, one highlighted)
  - `faq` (uses the new `accordion`)
  - `card-grid` (media+title+excerpt cards; uses W8's `MediaImage`)
  - `page-header` (eyebrow/title/lede)
- List-driven sections render an empty state (W7's `EmptyState`) when their
  items array is empty.
**Files:** new `resources/js/components/sections/*.tsx` (one file per section +
`section.tsx`); `components/ui/{accordion,aspect-ratio,table,tabs}.tsx` via CLI.
**Acceptance:** `bunx tsc --noEmit` clean; no raw color values anywhere in
`sections/` (grep for `#[0-9a-fA-F]{3,8}`, `oklch(`, `rgb(` must be empty);
each section renders populated and empty states (exercise via W4 home + the
W8 posts pages).

### W4. Replace the Laravel marketing page with a real home page

**Fix:** rewrite `resources/js/pages/public/home.tsx` (W1 moved it) as a
composition of W3 sections with believable, brand-neutral demo copy driven by
the shared `name` prop (hero → feature-grid → stats-band → testimonial-band →
faq → cta-band). Requirements:
- **Zero** hardcoded colors (grep-clean), zero Laravel logo/marketing SVGs,
  zero external links — CTAs route to `login()`/`register()`/`dashboard()`.
- Mobile-correct at 375 px and 1440 px.
- This page is deliberately *itself a reference*: it's the first public-page
  pattern agents will copy. Keep it readable — composition over cleverness.
**Files:** `resources/js/pages/public/home.tsx`; delete the old
`resources/js/pages/welcome.tsx`.
**Acceptance:** page-source contains no `laravel.com`, no hex colors; renders
inside PublicLayout; Pest feature test asserts 200 + app name + no
`laravel.com` in the rendered HTML (SSR or via a browser smoke if SSR isn't on
in tests).

### W5. Error pages (403 / 404 / 500)

**Fix:** Inertia v3 custom exception handling: in `bootstrap/app.php`
`withExceptions`, render `resources/js/pages/error.tsx` for 403/404/500 (and a
generic fallback), passing `status`. `error.tsx` uses `PublicLayout` for
guests (app shell otherwise — resolve via the shared `auth` prop), with a
status-appropriate message and a home link. **HTTP status codes must stay
correct** (404 page with a 404 status — Herman's QA gate probes a canary path).
**Files:** `bootstrap/app.php`; new `resources/js/pages/error.tsx`;
`tests/Feature/ErrorPagesTest.php`.
**Acceptance:** Pest: `GET /definitely-not-a-page` → 404 status, Inertia
component `error`; a 403 case (e.g. another team's resource) renders the error
page with 403.

### W6. A dashboard that isn't placeholder boxes

**Fix:** replace `resources/js/pages/dashboard.tsx` content with a real admin
home that becomes the extendable pattern: stat cards (team members count,
notes count, pending invitations), a "recent notes" list (reuses W7 list
pieces, links into Notes), quick actions (new note, invite member), and empty
states when there's nothing to show. `DashboardController` supplies real data
(eager, counted — no N+1). Keep `PendingInvitationsModal` behavior.
**Files:** `resources/js/pages/dashboard.tsx`;
`app/Http/Controllers/DashboardController.php`; `tests/Feature/DashboardTest.php`
(update — do not delete, extend).
**Acceptance:** dashboard renders real counts from the DB (Pest: seed → assert
Inertia props), zero `PlaceholderPattern` imports remain in the file
(component itself may stay in `components/ui/`).

### W7. Shared state components: `EmptyState`, `Pagination`, `SearchInput`, `LoadingState`

**Fix:** extract, don't reinvent:
- `resources/js/components/empty-state.tsx` (icon, title, description, optional
  action) — extract Notes index's inline empty state into it and re-adopt.
- `resources/js/components/pagination.tsx` — the prev/next + "Page X of Y"
  pattern from Notes index, driven by the existing `PaginatedData<T>` type
  (`resources/js/types/pagination.ts`).
- `resources/js/components/search-input.tsx` — icon input + GET form pattern
  from Notes index.
- `resources/js/components/loading-state.tsx` — spinner/skeleton convention
  (document in AGENTS.md: deferred props → skeleton, form submits →
  `processing` state).
Re-adopt all of them in Notes (the reference module must keep demonstrating
the current best pattern).
**Files:** new components; `resources/js/pages/notes/index.tsx` (adopt);
`AGENTS.md` (list-page recipe).
**Acceptance:** Notes index contains no inline empty-state/pagination markup
(uses the shared components); Notes Pest tests still pass unchanged.

### W8. Public reference module: Posts (+ `MediaImage`)

**Fix:** the public-content counterpart to Notes — read-side only (Notes already
teaches app-side CRUD):
- `posts` table: `title`, `slug` (unique), `excerpt`, `body` (markdown),
  `image_path?`, `published_at?`, timestamps. Model `Post` with `published()`
  scope. Factory + seeder (3–6 posts, realistic content lengths — see W11).
- `resources/js/components/media-image.tsx`: fixed-aspect container
  (`ratio?: "square"|"video"|"wide"`), `object-cover`, `loading="lazy"`, and a
  token-styled **inline-SVG placeholder** (data URI — zero network) when `src`
  is missing or errors.
- Public pages: `pages/public/posts/index.tsx` (paginated `card-grid` +
  `page-header`, `Seo`, empty state) and `pages/public/posts/show.tsx`
  (markdown body via `react-markdown` like Notes' `markdown-body.tsx`, `Seo`
  with excerpt, 404 when unpublished).
- Routes in `routes/web/public.php` (W9's file — if W9 not done yet, land this
  after): `GET /posts`, `GET /posts/{post:slug}` via a
  `app/Http/Controllers/Public/PostController.php`.
- Mark every file with `// REFERENCE MODULE — Public content quality-bar
  example. Delete this resource when unused.` (same convention as Notes) and
  add a "Removing the Posts module" section to the README mirroring the Notes
  one.
- Pest feature tests mirroring `tests/Feature/Notes/*`: index 200, show 200,
  unpublished 404, pagination, slug binding.
**Files:** migration, `app/Models/Post.php`, `app/Http/Controllers/Public/PostController.php`,
`database/factories/PostFactory.php`, `DatabaseSeeder.php`,
new `components/media-image.tsx`, `pages/public/posts/*`, `routes/web/public.php`,
`tests/Feature/Posts/*`, `README.md`.
**Acceptance:** `/posts` renders seeded posts as cards; unpublished → 404 with
the W5 error page; `php artisan test` green; REFERENCE MODULE markers present.

### W9. Route-file split + nav registry (parallel-agent clash removal)

**Fix:**
- `routes/web.php` becomes a loader: glob-require every `routes/web/*.php`
  (sorted) with a comment header ("one file per domain — add files, don't edit
  this one"). Move current routes: public home → `routes/web/public.php`;
  team-scoped dashboard/notes + invitations → `routes/web/app.php`; keep
  `routes/settings.php` loading as today (or fold into the glob as
  `routes/web/settings.php` — your call, keep it working). Regenerate Wayfinder
  output after the move.
- New `resources/js/lib/nav.ts`: `publicNav` (consumed by PublicLayout
  header/footer/mobile) and `appNav` (consumed by `app-sidebar.tsx`/`nav-main.tsx`),
  typed `NavItem = { title: string; href: string; icon?: LucideIcon }`.
  Refactor the sidebar to render from `appNav` (Dashboard, Notes, "Open
  website" move into the registry).
- Update the README project-structure section and the Notes deletion guide
  (the nav item now lives in `lib/nav.ts`, the route in `routes/web/app.php`).
**Files:** `routes/web.php`, new `routes/web/{public,app}.php`,
`resources/js/lib/nav.ts`, `app-sidebar.tsx`, `nav-main.tsx`,
`layouts/public-layout.tsx` (W1), `README.md`, `AGENTS.md` (convention note).
**Acceptance:** `php artisan route:list` before/after is identical except
provenance; adding `routes/web/tmp-probe.php` registers a route with no edit
to `web.php` (verify, delete probe); adding a `publicNav` entry appears in
header + footer + mobile menu with no component edits; Notes deletion drill
(§3) still works following the updated README.

### W10. Retheme contract + building-blocks catalog in AGENTS.md

**Fix:** append two sections to `AGENTS.md` (do not touch the boost sections):
1. **"Design tokens & retheming"** — the allowed retheme surface, written for
   the design-phase agent: token *values* in `resources/css/app.css`
   (`:root`/`.dark`, `--radius`), fonts via the `bunny(...)` declaration in
   `vite.config.ts` + `--font-sans` (fonts are fetched at build time and
   self-hosted — never add a runtime font CDN link), and the explicit bans:
   no hardcoded hex/rgb/oklch in components, no editing `components/ui/*`
   primitives (compose instead), no renaming token variables.
2. **"Building blocks (compose, don't invent)"** — ≤60 lines, one line each:
   PublicLayout + the `pages/public/` rule, every W3 section, Seo, MediaImage,
   EmptyState/LoadingState/Pagination/SearchInput, nav registry, route-file
   convention, the two reference modules + their deletion convention. Header
   rule: **"Before writing new public UI, check this list and compose from it;
   extend a block rather than creating a near-duplicate."**
**Files:** `AGENTS.md`.
**Acceptance:** every block added in W1–W9 appears in the catalog; a fresh
agent can find any block with one grep.

### W11. Seeder content quality

**Fix:** keep the Notes seed block (didactic — leave as is); write the Posts
seed content as believable, brand-neutral articles (realistic title lengths,
multi-paragraph markdown bodies with headings/lists/code where natural — the
W8 show page should look good without any wizard involvement). No lorem ipsum,
no `fake()->paragraph()` in committed seeders (factories may use faker for
tests). If post cards reference images, use `MediaImage`'s inline placeholder —
no storage writes, no external URLs.
**Files:** `database/seeders/DatabaseSeeder.php`.
**Acceptance:** after `php artisan db:seed`, `/posts` and one post detail look
presentable at 1440 px with zero external requests (DevTools network tab
filtered to non-local hosts is empty).

---

## 3. Verification protocol (all of it, in order)

1. **Gates:** `composer run ci:check` green (eslint, prettier, tsc, pint,
   phpstan, pest).
2. **Fresh-clone boot:** `git clone <branch> /tmp/starter-verify &&
   composer run setup && composer run dev` → home, `/posts`, a post, a 404,
   login, dashboard, notes all render; no console errors.
3. **Offline check:** DevTools network tab filtered to non-local hosts must be
   empty on every page above (fonts included — they must come from the build).
4. **Responsive sweep:** 375 px and 1440 px on home, `/posts`, dashboard — no
   overflow, no broken states.
5. **Deletion drill (scratch clone):** follow the README deletion guides to
   remove Notes **and** Posts completely — the app must still boot and
   `composer run ci:check` stays green. If the drill fails, the guides or the
   markers are wrong: fix them, not the drill.
6. **Retheme drill (scratch clone):** change only token values in `app.css` +
   the font in `vite.config.ts` — home and posts must visibly restyle with
   zero component edits. If they don't, a component is hardcoding values: find
   and fix it.
7. Open the PR against `master` on `HommeSauvage/herman-starter-laravel`.
   Nothing reaches wizard users until merge — say so in your final report.

## 4. Boundaries (do NOT do these)

- No CMS, page builder, Filament, Livewire, or other heavy runtime deps.
  shadcn CLI additions (accordion/aspect-ratio/table/tabs) are fine — they
  vendor source into `components/ui/`.
- No admin CRUD for Posts — Notes already teaches app-side CRUD; Posts teaches
  the public read side. Resist scope creep.
- Do not restyle the auth/settings/teams chrome beyond what's listed; this
  plan is additive.
- Do not touch the boost guidelines in `AGENTS.md` (append only), Wayfinder-
  generated files, `boost.json`, or `components.json` (except via the CLI).
- No external runtime URLs anywhere in generated output (fonts, images, CDNs,
  laravel.com marketing).
- Do not rename design-token variables or the `@theme` mapping in `app.css` —
  the wizard's design phase rewrites their values.
- Do not delete existing tests (repo rule) — extend them.
- Keep files focused (the desktop repo's guideline: split around 500 lines);
  one component per file in `sections/`.
- Git: feature branch + PR. Never force-push, never amend `master`.

## 5. Downstream handoff (informational — not this repo's work)

After merge, the desktop repo (clique-herman) needs: (a) the wizard's design
prompt pointed at the new AGENTS.md building-blocks catalog when writing
DESIGN.md's component catalog; (b) the wizard's "delete reference modules"
milestone text updated to mention Posts alongside Notes; (c) template manifests
(`blog`, `saas`, `store`, `landing`) can then drop their "invent marketing
pages from scratch" instructions in favor of referencing the sections library.
Leave a note about all three in your final report.
