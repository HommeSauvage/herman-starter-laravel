# AGENTS.md

Operating contract for coding agents in this repository. Every rule here is load-bearing — follow them exactly, even when a one-off alternative looks easier.

## How we work

- **Scaffold, don't invent.** Never open a blank controller, model, migration, page or test. Copy the closest reference module (Notes for app CRUD, Posts for public pages) and rename — that is how the codebase stays one codebase. New shared building blocks are a last resort; extend an existing one.
- **The gate is the contract.** `composer run ci:check` green is what "done" means — not "the code looks right".
- Framework conventions live in `.agents/skills/**` and Boost — consult those instead of guessing; this file records project invariants only.
- Don't change dependencies, don't create new base folders, don't create documentation files — unless the user asks.
- Be concise in replies; focus on what matters, skip the obvious.

## Stack & tools

- Laravel 13 / PHP 8.5 / Inertia v3 + React 19 / Tailwind v4 / Pest 4; versions in `composer.json` / `package.json`.
- Boost MCP (`search-docs`, `database-schema`, `database-query`, `browser-logs`) is the source of truth for framework APIs and DB state — prefer it over guessing or grepping `vendor/`; create files with `php artisan make:* --no-interaction`.

## Quality gates

- `composer run ci:check` — ESLint, Prettier, `tsc`, fallow, Pint, PHPStan, Pest. The lefthook pre-commit hook runs exactly this; never use `--no-verify` to dodge a red gate.
- `composer run agent:report` prints one JSON line (tests, soak, routes, boot memory) — use it to verify work instead of pasting raw test output. `composer run soak` proves requests retain no memory: no mutable static state in `app/`, no per-request growth.
- Tests: feature tests by default, models via factories; never delete tests without approval. Prove behavior with a test, not a throwaway script.

## Routes & frontend wiring

- `routes/web.php` is only a loader: it globs every `routes/web/*.php` in sorted order. One file per domain (`public.php`, `app.php`, `settings.php`) — add/edit files, never the loader.
- After adding routes or controllers, regenerate Wayfinder helpers: `php artisan wayfinder:generate --with-form`. `resources/js/actions/` and `resources/js/routes/` are generated — never hand-edit them.
- The frontend calls the backend through Wayfinder functions (`@/actions/...`, `@/routes/...`) — never hardcoded URLs. In PHP, prefer named routes and `route()`.
- New API routes default to Eloquent API Resources and versioning.

## Pages, layouts & SEO

- Public pages live in `resources/js/pages/public/` and get `PublicLayout` automatically (resolver in `resources/js/app.tsx`). Never put a public page outside `public/`; never point `default:` away from `AppLayout`.
- Navigation lives in the registry `resources/js/lib/nav.ts` (`publicNav`, `appNav()`, `appFooterNav`) — header, mobile menu, footer and sidebar all render from it. Add entries there, not in components.
- Every page renders `<Seo title … description? />` (`resources/js/components/seo.tsx`); never raw `<Head>` — the app name is appended automatically.

## Design tokens

- Token values live in `resources/css/app.css` (`:root`, `.dark`, `--radius`). Change values; never rename token variables or the `@theme` `--color-*` mapping.
- Fonts come from the `bunny(...)` declaration in `vite.config.ts` and are self-hosted at build time — never add a runtime font CDN link.
- Never hardcode colors in components — use token utilities (`bg-background`, `text-muted-foreground`, …).
- Never edit `components/ui/*` primitives (shadcn) — compose them (see `components/sections/`). Add a primitive with `bunx shadcn add <component>` only when composition genuinely cannot cover it.

## Building blocks (compose, don't invent)

- `layouts/public-layout.tsx` — public chrome (sticky header, mobile menu, footer); auto-applied to `pages/public/**`.
- `components/sections/*` — hero, page-header, feature-grid/rows, stats-band, testimonial-band, pricing-table, faq, cta-band, card-grid (token-only; list-driven ones render `EmptyState` when empty).
- Shared: `empty-state`, `loading-state`, `pagination`, `search-input`, `stat-card`, `media-image`, `markdown-body` (`.tsx`).
- List pages recipe: compose like `resources/js/pages/notes/index.tsx`. Loading convention: deferred props → skeleton, form submits → the form's `processing` state, everything else → spinner.

## Reference modules

<!-- @chisel-notes -->
- **Notes** (`pages/notes`, `components/notes`, `NoteController`) — team-scoped app CRUD quality bar. When a feature is "a list with detail and create/edit", make it look like Notes.
<!-- @end-chisel-notes -->
<!-- @chisel-posts -->
- **Posts** (`pages/public/posts`, `Public/PostController`) — public read-side content quality bar (pagination, markdown, empty states).
<!-- @end-chisel-posts -->
- Removable wholesale: `composer run chisel` deletes files, routes, tests and optional deps. Never half-delete one.

## Misc

- Change dev port: `SERVER_PORT=8001 APP_URL=http://localhost:8001 composer run dev`.
- Frontend changes not showing, or a Vite "unable to locate file in manifest" error? The user needs `bun run dev` / `bun run build` — ask them.
- Tooling looks wrong (PHP extension, bun, missing dirs)? `composer run preflight` prints a one-line-per-fact checklist.
