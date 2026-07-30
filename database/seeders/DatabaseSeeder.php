<?php

namespace Database\Seeders;

use App\Models\Note;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::query()->where('email', 'test@example.com')->first()
            ?? User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $user->forceFill(['name' => 'Test User'])->save();

        $team = $user->currentTeam;

        // REFERENCE MODULE — demo notes for the quality-bar Notes feature.
        // Requires unique(team_id, title) — see create_notes_table migration.
        Note::upsert(
            [
                [
                    'team_id' => $team->id,
                    'user_id' => $user->id,
                    'title' => 'Welcome to Notes',
                    'body' => <<<'MD'
# Welcome

This is a **reference Notes module** shipped with the Herman Laravel starter.

Use it as a quality bar for list → detail → create/edit flows, then **delete the whole Notes feature** if your product does not need it.
MD,
                ],
                [
                    'team_id' => $team->id,
                    'user_id' => $user->id,
                    'title' => 'Markdown tips',
                    'body' => <<<'MD'
## Writing tips

- Use headings to structure longer notes
- Keep titles short and searchable
- Link related ideas with markdown links
MD,
                ],
                [
                    'team_id' => $team->id,
                    'user_id' => $user->id,
                    'title' => 'Empty states matter',
                    'body' => 'When the list is empty, guide the user toward creating their first note — never leave a blank void.',
                ],
            ],
            uniqueBy: ['team_id', 'title'],
            update: ['user_id', 'body'],
        );

        // REFERENCE MODULE — demo posts for the public content quality-bar
        // Posts feature. Delete this block with the Posts module when unused.
        Post::upsert(
            [
                [
                    'title' => 'Design tokens are a contract, not a theme',
                    'slug' => 'design-tokens-are-a-contract',
                    'excerpt' => 'A retheme should be a handful of variable edits, not a refactor. Here is how a strict token boundary makes that true.',
                    'body' => <<<'MD'
## The problem with "just change the colors"

Every redesign starts the same way: someone changes a button, then hunts down forty near-identical grays scattered across components. The fix is not discipline — it is a boundary.

In this starter, every component reads from the design tokens defined in `resources/css/app.css`. Backgrounds come from `--background`, muted text from `--muted-foreground`, radii from `--radius`. Nothing hardcodes a hex value.

## What that buys you

- A full retheme is a single file edit
- Dark mode is a token swap, not a second design
- New components inherit the system for free

```css
:root {
    --primary: oklch(0.205 0 0);
    --radius: 0.625rem;
}
```

Change the values, keep the names, rebuild — every page follows.
MD,
                    'image_path' => null,
                    'published_at' => now()->subWeeks(1),
                ],
                [
                    'title' => 'Empty states that guide instead of scold',
                    'slug' => 'empty-states-that-guide',
                    'excerpt' => 'A blank void is a dead end. The best empty states answer three questions: what is this, why is it empty, and what do I do next.',
                    'body' => <<<'MD'
## The three questions

When a list renders nothing, the user is usually confused, not careless. A good empty state answers, in order:

1. **What lives here?** Name the thing plainly — "No notes yet".
2. **Why is it empty?** Context matters — a fresh account is different from a search with no matches.
3. **What next?** Offer exactly one obvious action.

## Search results are not first-run states

A common mistake is reusing the same empty state everywhere. "No notes yet" is wrong when the user just searched for something. Say "No notes match your search" and suggest a different term instead of pushing creation.

> Rule of thumb: an empty state without a next step is a dead end, and dead ends get closed tabs.

The shared `EmptyState` component in this starter encodes exactly this shape: icon, title, description, one action.
MD,
                    'image_path' => null,
                    'published_at' => now()->subWeeks(2),
                ],
                [
                    'title' => 'Ship the boring parts once, then never again',
                    'slug' => 'ship-the-boring-parts-once',
                    'excerpt' => 'Auth, teams, pagination, and error pages are not what makes your product interesting — but they are where weeks disappear.',
                    'body' => <<<'MD'
## Where the weeks go

Ask a team what delayed their launch and the answer is rarely the headline feature. It is the accumulation: invitation emails, slug routing, 404 pages, mobile menus, form validation states.

None of these differentiate a product, but every one of them can sink a weekend.

## The starter kit philosophy

A good starter does not try to predict your product. It removes the parts that every product shares:

- **Authentication** with passkeys and two-factor, already wired
- **Teams** with roles and invitations, already enforced
- **Reference modules** that show the quality bar instead of describing it

## Keep the bar visible

The Notes and Posts modules exist to be copied and then deleted. When every new feature starts from a working example, consistency stops depending on who wrote the code.
MD,
                    'image_path' => null,
                    'published_at' => now()->subWeeks(3),
                ],
                [
                    'title' => 'What we verify before every merge',
                    'slug' => 'what-we-verify-before-every-merge',
                    'excerpt' => 'Tests, static analysis, linting, and formatting as one command — the gate is only useful if it is boring to run.',
                    'body' => <<<'MD'
## One command, no excuses

Quality gates fail in two ways: they check too little, or they are too annoying to run. The fix for both is a single command:

```bash
composer run ci:check
```

That runs frontend linting and formatting, TypeScript checks, Pint, PHPStan, and the Pest suite. If it passes, the change is ready.

## Write the test with the feature

Every behavioral change ships with a test in the same commit. Not because coverage is a trophy, but because the test is the cheapest place to discover the API is awkward.

### The rule we actually follow

> Run the minimum number of tests needed to prove the change — but always prove it.

A filtered `php artisan test --compact --filter=...` while iterating, the full gate before pushing. Fast enough that nobody skips it.
MD,
                    'image_path' => null,
                    'published_at' => now()->subWeeks(4),
                ],
            ],
            uniqueBy: ['slug'],
            update: ['title', 'excerpt', 'body', 'image_path', 'published_at'],
        );
    }
}
