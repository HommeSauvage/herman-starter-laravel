# Herman Laravel Starter

A production-minded Laravel starter for multi-tenant SaaS apps — teams, auth, and a polished React UI out of the box.

Built on **Laravel 13**, **Inertia v3**, **React 19**, and **Tailwind CSS v4**, with typed Wayfinder routes, Fortify auth (including passkeys & 2FA), and a reference Notes CRUD so new features have a clear quality bar.

[![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![Inertia](https://img.shields.io/badge/Inertia-v3-9553E9?style=flat-square)](https://inertiajs.com)
[![React](https://img.shields.io/badge/React-19-61DAFB?style=flat-square&logo=react&logoColor=black)](https://react.dev)
[![PHP](https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg?style=flat-square)](LICENSE)

---

## Why this starter

Most Laravel React kits give you login and a dashboard. Herman goes further:

- **Modern auth** — registration, email verification, password reset, 2FA, and WebAuthn passkeys
- **Type-safe frontend** — Wayfinder-generated TypeScript for controller actions and routes
- **Reference CRUD** — a full Notes module (list → show → create/edit, markdown, policies, Pest tests) you can keep or delete
- **Ship-ready DX** — Pest, Pint, Larastan, ESLint, Prettier, React Compiler, Laravel Boost
- **Teams included** — create teams, invite members, roles & permissions, team-scoped URLs

Use it as the base for an AI-assisted product (or a traditional one) and start building features instead of scaffolding.

---

## Features

### Authentication

- Email/password registration & login ([Laravel Fortify](https://laravel.com/docs/fortify))
- Email verification & password reset
- Two-factor authentication (TOTP)
- Passkeys (WebAuthn)
- Profile, password, and security settings UI

### Teams

- Personal team on registration
- Create & switch teams
- Invite members by email with roles (`owner`, `admin`, `member`)
- Team-scoped routes: `/{current_team}/…`
- Policies and permission enums ready to extend

### Frontend

- Inertia.js v3 + React 19 + TypeScript
- Tailwind CSS v4 + [shadcn/ui](https://ui.shadcn.com) (New York style)
- Lucide icons, Sonner toasts, light/dark appearance
- SSR-capable Vite setup (`bun run build:ssr`)

### Reference module: Notes

A complete team-scoped Notes CRUD that demonstrates Herman’s quality bar:

| Concern        | Included                                      |
|----------------|-----------------------------------------------|
| Backend        | Model, policy, form requests, resource routes |
| UI             | Index, show, create, edit + markdown editor   |
| UX             | Empty states, delete confirmation             |
| Tests          | Pest feature coverage                         |

**Don’t need Notes?** Delete the whole module — see [Removing the Notes module](#removing-the-notes-module). Files are tagged with `REFERENCE MODULE` comments.

### Quality & tooling

- [Pest](https://pestphp.com) feature tests
- [Laravel Pint](https://laravel.com/docs/pint) + ESLint + Prettier
- [Larastan](https://github.com/larastan/larastan) / PHPStan
- [Laravel Wayfinder](https://github.com/laravel/wayfinder) typed route helpers
- [Laravel Boost](https://github.com/laravel/boost) MCP + agent skills for AI-assisted development

---

## Tech stack

| Layer        | Choice                                      |
|--------------|---------------------------------------------|
| Backend      | Laravel 13, PHP 8.3+                        |
| Auth         | Fortify (+ passkeys)                        |
| SPA bridge   | Inertia Laravel / React v3                  |
| UI           | React 19, Tailwind CSS v4, shadcn/ui        |
| Routes (TS)  | Wayfinder                                   |
| Bundler      | Vite 8 + Bun (or npm/pnpm/yarn)             |
| Tests        | Pest 4                                      |
| Static analysis | Larastan / PHPStan                       |

---

## Requirements

- PHP 8.3+ (8.5 recommended)
- [Composer](https://getcomposer.org)
- [Bun](https://bun.sh) (preferred) or Node.js 20+
- SQLite (default) or MySQL / PostgreSQL

---

## Quick start

```bash
git clone https://github.com/HommeSauvage/herman-starter-laravel.git
cd herman-starter-laravel

composer setup
```

`composer setup` installs PHP deps, copies `.env`, generates the app key, runs migrations, installs JS deps, and builds assets.

Then start the full local stack:

```bash
composer run dev
```

Visit [http://localhost:8000](http://localhost:8000).

### Seed demo data

```bash
php artisan db:seed
```

Creates `test@example.com` / `password` with sample Notes on their current team.

### Manual setup (alternative)

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
bun install
bun run build
composer run dev
```

### Custom port

```bash
SERVER_PORT=8001 APP_URL=http://localhost:8001 composer run dev
```

---

## Project structure

```
app/
├── Http/Controllers/
│   ├── Notes/              # REFERENCE MODULE
│   ├── Settings/
│   └── Teams/
├── Models/                 # User, Team, Membership, Note, …
├── Policies/
└── Enums/                  # TeamRole, TeamPermission

resources/js/
├── components/             # UI + feature components
├── pages/                  # Inertia pages (auth, teams, notes, …)
├── layouts/
└── actions/ | routes/      # Wayfinder-generated (do not hand-edit)

tests/Feature/              # Pest tests (incl. Notes, Teams, Auth)
```

Team-scoped app routes live under `{current_team}` in `routes/web.php`. Settings and invitations are registered separately in `routes/settings.php` / the web routes file.

---

## Frontend conventions

- Pages: `resources/js/pages/**`
- Prefer Wayfinder imports over hardcoded URLs:

  ```ts
  import { index } from '@/actions/App/Http/Controllers/Notes/NoteController'
  // or named routes from `@/routes`
  ```

- After adding routes or controllers, regenerate Wayfinder helpers (usually via Vite / `php artisan wayfinder:generate` as configured in your workflow).
- UI primitives live under `resources/js/components/ui` (shadcn).

---

## Development scripts

| Command                    | Purpose                                      |
|----------------------------|----------------------------------------------|
| `composer run dev`         | PHP server, queue, logs, Vite                |
| `composer test`            | Pint check + PHPStan + Pest                  |
| `composer run ci:check`    | Frontend lint/format/types + `composer test` |
| `vendor/bin/pint`          | Format PHP                                   |
| `bun run lint` / `format`  | ESLint / Prettier                            |
| `bun run types:check`      | TypeScript                                   |
| `php artisan test`         | Pest only                                    |

Filter tests while iterating:

```bash
php artisan test --compact --filter=Note
```

---

## Removing the Notes module

If your product does not need Notes, remove the full feature — do not leave half of it behind.

**Backend**

- `app/Models/Note.php`
- `app/Policies/NotePolicy.php`
- `app/Http/Controllers/Notes/`
- `app/Http/Requests/Notes/`
- `database/migrations/*_create_notes_table.php`
- `database/factories/NoteFactory.php`
- Notes seed block in `DatabaseSeeder`
- `Route::resource('notes', …)` in `routes/web.php`
- `Team::notes()` relation
- `tests/Feature/Notes/`

**Frontend**

- `resources/js/pages/notes/`
- `resources/js/components/notes/`
- Notes nav item in `resources/js/components/app-sidebar.tsx`

**Optional deps used only by Notes**

- `@uiw/react-md-editor`
- `react-markdown`

Also remove the Notes section from `AGENTS.md` if you keep that file for AI agents.

---

## Configuration notes

- Default database is **SQLite** (`DB_CONNECTION=sqlite`). Switch in `.env` for production.
- Sessions, cache, and queue default to the database driver — fine for local; tune for production.
- Passkeys use your `APP_URL` as relying party / origin — keep `APP_URL` accurate in every environment.

Deploy easily on [Laravel Cloud](https://cloud.laravel.com/) or any standard Laravel host.

---

## Contributing

Issues and pull requests are welcome. Please:

1. Keep changes focused and consistent with existing conventions
2. Add or update Pest tests for behavioral changes
3. Run `composer run ci:check` (or the relevant subset) before opening a PR

---

## License

This project is open-sourced software licensed under the [MIT license](LICENSE).
