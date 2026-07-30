<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.5
- inertiajs/inertia-laravel (INERTIA_LARAVEL) - v3
- laravel/fortify (FORTIFY) - v1
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- laravel/wayfinder (WAYFINDER) - v0
- larastan/larastan (LARASTAN) - v3
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- @inertiajs/react (INERTIA_REACT) - v3
- react (REACT) - v19
- tailwindcss (TAILWINDCSS) - v4
- @laravel/vite-plugin-wayfinder (WAYFINDER_VITE) - v0
- eslint (ESLINT) - v9
- prettier (PRETTIER) - v3

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `bun run build`, `bun run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== inertia-laravel/core rules ===

# Inertia

- Inertia creates fully client-side rendered SPAs without modern SPA complexity, leveraging existing server-side patterns.
- Components live in `resources/js/pages` (unless specified in `vite.config.js`). Use `Inertia::render()` for server-side routing instead of Blade views.
- ALWAYS use `search-docs` tool for version-specific Inertia documentation and updated code examples.
- IMPORTANT: Activate `inertia-react-development` when working with Inertia client-side patterns.

# Inertia v3

- Use all Inertia features from v1, v2, and v3. Check the documentation before making changes to ensure the correct approach.
- New v3 features: standalone HTTP requests (`useHttp` hook), optimistic updates with automatic rollback, layout props (`useLayoutProps` hook), instant visits, simplified SSR via `@inertiajs/vite` plugin, custom exception handling for error pages.
- Carried over from v2: deferred props, infinite scroll, merging props, polling, prefetching, once props, flash data.
- When using deferred props, add an empty state with a pulsing or animated skeleton.
- Axios has been removed. Use the built-in XHR client with interceptors, or install Axios separately if needed.
- `Inertia::lazy()` / `LazyProp` has been removed. Use `Inertia::optional()` instead.
- Prop types (`Inertia::optional()`, `Inertia::defer()`, `Inertia::merge()`) work inside nested arrays with dot-notation paths.
- SSR works automatically in Vite dev mode with `@inertiajs/vite` - no separate Node.js server needed during development.
- Event renames: `invalid` is now `httpException`, `exception` is now `networkError`.
- `router.cancel()` replaced by `router.cancelAll()`.
- The `future` configuration namespace has been removed - all v2 future options are now always enabled.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `bun run build` or ask the user to run `bun run dev` or `composer run dev`.

=== wayfinder/core rules ===

# Laravel Wayfinder

Use Wayfinder to generate TypeScript functions for Laravel routes. Import from `@/actions/` (controllers) or `@/routes/` (named routes).

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- The `{name}` argument should not include the test suite directory. Use `php artisan make:test --pest SomeFeatureTest` instead of `php artisan make:test --pest Feature/SomeFeatureTest`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

=== inertia-react/core rules ===

# Inertia + React

- IMPORTANT: Activate `inertia-react-development` when working with Inertia React client-side patterns.

</laravel-boost-guidelines>

<other-guidelines>

# Changing Ports

- To change the port from which the server is run, prepend the command with `SERVER_PORT=xxxx` and `APP_URL=http://localhost:xxxx`. Example: `SERVER_PORT=8001 APP_URL=http://localhost:8001 composer run dev`

# Public pages & layouts

- Public pages live in `resources/js/pages/public/` and get `PublicLayout` automatically (see the layout resolver in `resources/js/app.tsx`). **Never put a public page outside `public/`; never point `default:` away from AppLayout.**
- Public navigation lives in the nav registry (`resources/js/lib/nav.ts`, `publicNav`) — header, mobile menu, and footer all render from it. Add entries there, not in components.
- Every page renders `<Seo title … description? />` (`resources/js/components/seo.tsx`). Never use raw `<Head>` for titles — the app name is appended automatically.

# List pages recipe

Index pages compose the shared pieces instead of hand-rolling markup (see `resources/js/pages/notes/index.tsx` for the reference):

- `resources/js/components/empty-state.tsx` — icon + title + description + one action when a list/grid is empty.
- `resources/js/components/pagination.tsx` — prev/next pager driven by the `PaginatedData<T>` type; renders nothing on a single page.
- `resources/js/components/search-input.tsx` — icon input + GET form (query-string searches stay shareable).
- `resources/js/components/loading-state.tsx` — loading convention: **deferred props → skeleton, form submits → the form's `processing` state, everything else → spinner.**

# Routes & navigation

- `routes/web.php` is only a loader: it globs every `routes/web/*.php` file in sorted order. One file per domain (`public.php`, `app.php`, `settings.php`) — **add files, don't edit the loader.**
- After adding routes or controllers, regenerate Wayfinder helpers: `php artisan wayfinder:generate --with-form`. Files in `resources/js/actions` and `resources/js/routes` are generated — never hand-edit them.
- Sidebar nav comes from `appNav()` / `appFooterNav` in `resources/js/lib/nav.ts`.

# Design tokens & retheming

The design phase (and any retheme) works through this surface and nothing else:

- **Token values** live in `resources/css/app.css` (`:root`, `.dark`, `--radius`). You may change **values**; never **rename** token variables or the `@theme` `--color-*` mapping.
- **Fonts** come from the `bunny(...)` declaration in `vite.config.ts` (+ `--font-sans` in `app.css`). Fonts are fetched at build time and self-hosted — **never add a runtime font CDN link.**
- **Never hardcode colors** (`#hex`, `rgb(`, `oklch(`) in components — use token utilities (`bg-background`, `text-muted-foreground`, `bg-primary`, …).
- **Never edit `components/ui/*` primitives** — compose them instead (see the sections library).

# Building blocks (compose, don't invent)

**Before writing new public UI, check this list and compose from it; extend a block rather than creating a near-duplicate.**

- `layouts/public-layout.tsx` — public chrome (sticky header, mobile menu, footer); auto-applied to `pages/public/**`.
- `components/seo.tsx` — `<Seo title description?>` on every page.
- `components/sections/` — `hero` (centered/split), `page-header`, `feature-grid`, `feature-rows`, `stats-band`, `testimonial-band`, `pricing-table`, `faq`, `cta-band`, `card-grid`. All token-only; list-driven ones render `EmptyState` when empty.
- `components/media-image.tsx` — fixed-aspect image with lazy loading + zero-network placeholder.
- `components/empty-state.tsx`, `loading-state.tsx`, `pagination.tsx`, `search-input.tsx` — shared state components for list pages.
- `components/stat-card.tsx` — KPI card for dashboards.
- `components/markdown-body.tsx` — shared markdown renderer (Notes + Posts).
- `lib/nav.ts` — nav registry (`publicNav`, `appNav()`, `appFooterNav`).
- `routes/web/*.php` — route-file convention: one file per domain, glob-loaded.
- Reference modules: **Notes** (`pages/notes`, `components/notes`, `NoteController`) teaches team-scoped app CRUD; **Posts** (`pages/public/posts`, `Public/PostController`) teaches public read-side content. Both are marked `REFERENCE MODULE` and are deletable in one pass (see README).

# REFERENCE MODULE — Notes (Herman quality bar)

This starter ships a polished **Notes** CRUD example so Herman's wizard has a concrete quality bar (list → detail → create/edit, markdown editor, empty states, delete confirm, team scoping, Pest coverage).

**If the product does not need Notes, delete the whole module and this section of this file** — do not leave half of it behind:

- Backend: `app/Models/Note.php`, `app/Policies/NotePolicy.php`, `app/Http/Controllers/Notes/`, `app/Http/Requests/Notes/`, migration `*_create_notes_table.php`, `database/factories/NoteFactory.php`, Notes seed block in `DatabaseSeeder` (the `Note::upsert(…)` call and the `$team` assignment it uses), `Route::resource('notes', …)` in `routes/web/app.php`, Notes props in `DashboardController.php`, `Team::notes()`, `tests/Feature/Notes/`, Notes-specific tests in `tests/Feature/DashboardTest.php`
- Frontend: `resources/js/pages/notes/`, `resources/js/components/notes/`, Notes entries in `resources/js/lib/nav.ts`, Notes widgets in `resources/js/pages/dashboard.tsx`, `resources/js/components/markdown-body.tsx` (shared with Posts — delete only if Posts is removed too)
- Optional deps only used by Notes: `@uiw/react-md-editor`; `react-markdown` is shared with Posts

Files are marked with `REFERENCE MODULE` comments so they are easy to find.

# REFERENCE MODULE — Posts (public content quality bar)

This starter ships a public **Posts** read-side example (index with card grid + pagination, markdown detail by slug, `published()` scope, 404 for unpublished) so public content has a concrete quality bar.

**If the product does not need Posts, delete the whole module and this section of this file** — do not leave half of it behind:

- Backend: `app/Models/Post.php`, `app/Http/Controllers/Public/`, migration `*_create_posts_table.php`, `database/factories/PostFactory.php`, Posts seed block in `DatabaseSeeder`, Posts routes in `routes/web/public.php`, `tests/Feature/Posts/`
- Frontend: `resources/js/pages/public/posts/`, Blog entry in `resources/js/lib/nav.ts` (`publicNav`), `resources/js/components/markdown-body.tsx` (shared with Notes — delete only if Notes is removed too)
- Optional deps only used by Posts: `react-markdown` (shared with Notes)

Files are marked with `REFERENCE MODULE` comments so they are easy to find.

</other-guidelines>
