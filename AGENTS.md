# AGENTS.md

Operating contract for coding agents in this repository. Every rule here is load-bearing — follow them exactly, even when a one-off alternative looks easier.

## How we work

- **Compose, don't invent.** The reference modules (Notes, Posts) and the building blocks below set the quality bar. Extend an existing block before creating a new one; check sibling files for conventions before writing anything.
- **The gate is the contract.** `composer run ci:check` green is what "done" means — not "the code looks right".
- Don't change dependencies, don't create new base folders, don't create documentation files — unless the user asks.
- Be concise in replies; focus on what matters, skip the obvious.

## Stack & tools

- Laravel 13 / PHP 8.5 / Inertia v3 + React 19 / Tailwind CSS v4 / Pest 4 / Fortify / Wayfinder — exact versions in `composer.json` / `package.json`.
- Domain skills live in `**/skills/**` (fortify, inertia-react, pest, tailwind, wayfinder, laravel). Activate the matching one whenever you work in that domain — don't wait until you're stuck.
- Prefer the Boost MCP tools over manual alternatives: `search-docs` (version-specific docs — use it instead of guessing framework APIs), `database-schema` before migrations, `database-query` instead of raw SQL, `browser-logs`, `get-absolute-url`.
- Create files with `php artisan make:* --no-interaction`. Inspect with artisan directly (`route:list`, `config:show`, `--help`).

## Quality gates

- `composer run ci:check` is the single source of truth: ESLint, Prettier, `tsc`, fallow, Pint, PHPStan, Pest. A lefthook pre-commit hook runs exactly this — never use `--no-verify` to dodge a red gate.
- **fallow** (`bunx fallow --format json`, config `.fallowrc.json`)
- After modifying PHP files, run `vendor/bin/pint --dirty --format agent` before finalizing. Code style is enforced by Pint + PHPStan — let the gate judge it.
- Tests: `php artisan make:test --pest Name` (no suite directory in the name), feature tests by default, models via factories. Run `php artisan test --compact` (`--filter=` to narrow). Never delete tests without approval.
- Don't write throwaway verification scripts or tinker snippets when a test can prove the behavior — write the test.

## Routes & frontend wiring

- `routes/web.php` is only a loader: it globs every `routes/web/*.php` in sorted order. One file per domain (`public.php`, `app.php`, `settings.php`) — add/edit files, never the loader.
- After adding routes or controllers, regenerate Wayfinder helpers: `php artisan wayfinder:generate --with-form`. `resources/js/actions/` and `resources/js/routes/` are generated — never hand-edit them.
- The frontend calls the backend through Wayfinder functions (`@/actions/...`, `@/routes/...`) — never hardcoded URLs. In PHP, prefer named routes and `route()`.
- If API routes are added, default to Eloquent API Resources and versioning, unless existing routes do otherwise.

## Pages, layouts & SEO

- Public pages live in `resources/js/pages/public/` and get `PublicLayout` automatically (layout resolver in `resources/js/app.tsx`). Never put a public page outside `public/`; never point `default:` away from `AppLayout`.
- Navigation lives in the registry `resources/js/lib/nav.ts` (`publicNav`, `appNav()`, `appFooterNav`) — header, mobile menu, footer, and sidebar render from it. Add entries there, not in components.
- Every page renders `<Seo title … description? />` (`resources/js/components/seo.tsx`). Never use raw `<Head>` for titles — the app name is appended automatically.

## Design tokens

- Token values live in `resources/css/app.css` (`:root`, `.dark`, `--radius`). Change values; never rename token variables or the `@theme` `--color-*` mapping.
- Fonts come from the `bunny(...)` declaration in `vite.config.ts` and are self-hosted at build time — never add a runtime font CDN link.
- Never hardcode colors (`#hex`, `rgb(`, `oklch(`) in components — use token utilities (`bg-background`, `text-muted-foreground`, `bg-primary`, …).
- Never edit `components/ui/*` primitives — compose them (see `components/sections/`).

## Building blocks (compose, don't invent)

Before writing new UI, compose from this list; extend a block rather than creating a near-duplicate:

- `layouts/public-layout.tsx` — public chrome (sticky header, mobile menu, footer); auto-applied to `pages/public/**`.
- `components/sections/` — `hero`, `page-header`, `feature-grid`, `feature-rows`, `stats-band`, `testimonial-band`, `pricing-table`, `faq`, `cta-band`, `card-grid`. Token-only; list-driven ones render `EmptyState` when empty.
- Shared pieces: `empty-state.tsx`, `loading-state.tsx`, `pagination.tsx`, `search-input.tsx`, `stat-card.tsx` (KPIs), `media-image.tsx`, `markdown-body.tsx`.
- List pages recipe: compose like `resources/js/pages/notes/index.tsx`. Loading convention: deferred props → skeleton, form submits → the form's `processing` state, everything else → spinner.

## Inertia v3 gotchas

- No axios — use the built-in XHR client. `Inertia::optional()` replaces `Inertia::lazy()`. Events are `httpException` / `networkError`; `router.cancelAll()` replaces `router.cancel()`.

## Reference modules

- **Notes** (`pages/notes`, `components/notes`, `NoteController`) — team-scoped app CRUD quality bar. **Posts** (`pages/public/posts`, `Public/PostController`) — public read-side content quality bar. Both are marked `REFERENCE MODULE` and deletable wholesale (removal guides in README).
- Just scaffolded without the wizard? Study the two modules, decide their fate (keep, or delete wholesale via the README — never leave half a module), then trim this file to what the project uses, moving durable knowledge into `.agents/skills/` or `.agents/docs/`.

## Misc

- Change dev port: `SERVER_PORT=8001 APP_URL=http://localhost:8001 composer run dev`.
- Frontend changes not showing, or a Vite "unable to locate file in manifest" error? The user needs `bun run dev` / `bun run build` — ask them.
