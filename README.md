# Herman Laravel Starter

<!-- @chisel-scaffolding -->
The Laravel template behind Herman's wizard. An agent turns it into one user's
product: keep the stack, delete what that product does not need, and replace this
file with the product's README.

It is not maintained for human readers and never deployed as-is.

<!-- @end-chisel-scaffolding -->
## What is already wired

- **Laravel 13 / PHP 8.5**, Inertia v3 + React 19, Tailwind v4, Pest 4
- **Fortify auth** — registration, email verification, password reset, 2FA, profile and security settings
- **Multi-tenant teams** — a personal team on registration, invitations, roles, `/{current_team}/…` routes
- **Typed routes** — Wayfinder helpers in `resources/js/{actions,routes}` (generated; never hand-edit)
- **Building blocks** — a typed sections library, shadcn primitives, empty/loading/pagination conventions
- **Gates** — `ci:check`, `agent:report`, `soak`, `preflight`, `modules`, `chisel` (see `composer.json`)

## The contract

`AGENTS.md` is the operating contract for coding agents — conventions, gates and
floors. Read it before changing anything; this file only explains the starter's
own machinery.

### 1. Resolve the optional modules first

Before the first `php artisan migrate` (a dropped module's migration is deleted,
not rolled back) and after `bun install`:

```bash
composer run chisel -- --answers='{"modules":["notes"]}'   # keep Notes, drop the rest
composer run chisel -- --answers='{"modules":[]}'          # drop all three
```

<!-- @chisel-notes -->
**Notes** — team-scoped CRUD behind auth: model, policy, form requests, resource
routes, list → detail → create/edit pages, markdown editor, delete confirmation,
Pest coverage. The quality bar for "a list with detail and create/edit".
<!-- @end-chisel-notes -->

<!-- @chisel-posts -->
**Posts** — public read-side content: `published()` scope, slug route binding,
card-grid index with pagination, markdown detail page, SEO heads, empty states,
Pest coverage. The quality bar for public pages.
<!-- @end-chisel-posts -->

<!-- @chisel-passkeys -->
**Passkeys** — WebAuthn sign-in and management on top of Fortify. The Composer
package stays (Fortify requires it); the feature, routes, table, components and
the `@laravel/passkeys` dependency go.
<!-- @end-chisel-passkeys -->

A module is either fully present or fully gone — never half. Hand-deleting leaves
residue (dead routes, dead UI, tests that no longer run, docs describing features
that are gone), and both `php artisan test` and `composer run modules` fail on
it. `chisel.modules.php` is the contract, `chisel.php` is the removal.

### 2. Replace this file

The product's README is written in the foundation milestone: the product name,
what it does, how to run it, the demo logins. The starter text is scaffolding —
once the optional modules are gone, the suite fails while this file is still the
starter's.

## Where things live

```
app/
├── Http/Controllers/     # Notes/, Public/, Settings/, Teams/, DashboardController
├── Models/               # User, Team, Membership, Note, Post
├── Policies/  Enums/     # TeamRole, TeamPermission
resources/js/
├── components/           # feature components + sections/ + ui/ (shadcn)
├── pages/                # Inertia pages (public/, auth, settings, teams, notes)
├── layouts/              # public-layout.tsx, app-layout.tsx, auth, settings
├── lib/nav.ts            # navigation registry (public + app sidebar)
└── actions/ | routes/    # Wayfinder-generated — do not hand-edit
routes/
└── web/                  # one file per domain; routes/web.php globs them
herman-docs/              # beginner docs written by the wizard's docs phase
```

Web routes live in `routes/web/*.php` — one file per domain (`public.php`,
`app.php`, `settings.php`). `routes/web.php` is only the loader: add a file and
its routes register automatically. Team-scoped routes live under `{current_team}`.

## Development

| Command | Purpose |
|---|---|
| `composer run dev` | PHP server, queue, logs, Vite |
| `composer run ci:check` | ESLint, Prettier, `tsc`, fallow, Pint, PHPStan, Pest — the gate |
| `composer run agent:report` | One JSON line: tests, soak, routes, boot memory |
| `composer run modules` | One JSON line: optional-module state and residue |
| `composer run chisel` | Remove optional modules |
| `composer run soak` | Prove requests retain no memory |
| `composer run preflight` | One line per environment fact |
| `composer setup` | Install everything, build assets, install git hooks |

`composer setup` installs [lefthook](https://lefthook.dev) git hooks: every
`git commit` runs `composer run ci:check` as a pre-commit gate. Bypass only in
emergencies with `--no-verify`.

Filter tests while iterating: `php artisan test --compact --filter=Note`.

## Runtime notes

- Default database is **SQLite** (`DB_CONNECTION=sqlite`); switch in `.env` for MySQL or PostgreSQL.
- Sessions, cache and queue default to the database driver — fine locally, tune for production.
- The repo ships a production `Dockerfile` (multi-stage: bun/vite build → FrankenPHP + `pdo_sqlite`) with `docker/entrypoint.sh` and `litestream.yml` for the SQLite data plane (restore-if-empty → migrate → replicate). Runtime configuration arrives as deployment env, never build args; the app trusts `X-Forwarded-*` so absolute URLs stay `https://` behind a TLS-terminating proxy.
<!-- @chisel-passkeys -->
- Passkeys use `APP_URL` as relying party and origin — keep `APP_URL` accurate in every environment.
<!-- @end-chisel-passkeys -->

## License

MIT — see [LICENSE](LICENSE).
