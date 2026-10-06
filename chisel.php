<?php

declare(strict_types=1);

use Laravel\Chisel\Chisel;
use Laravel\Chisel\Question;

/*
|--------------------------------------------------------------------------
| Chisel script — optional-module removal
|--------------------------------------------------------------------------
|
| The starter ships three optional modules (Notes, Posts, Passkeys) that exist
| for two reasons: they show the quality bar, and they can be dropped whole.
| This script removes them properly — files, marked blocks, routes, tests,
| docs and npm dependencies.
|
|   composer run chisel                                        # interactive
|   composer run chisel -- --answers='{"modules":["notes"]}'    # agent / wizard
|   composer run chisel -- --answers='{"modules":[]}'           # drop all three
|
| Kept module  → its @chisel-<tag> markers are stripped, the feature stays.
| Dropped      → its files are deleted, its marked blocks and imports are
|                removed, and its optional JS dependency is uninstalled.
|
| Run it before the first `php artisan migrate` (a dropped module's migration
| is deleted, not rolled back) and after `bun install` so JS dependencies can
| be pruned.
|
| What each module owns lives in `chisel.modules.php` — the same contract
| `composer run modules` (scripts/module-integrity.php) gates on, so the two
| can never disagree. Hand-deleting a module leaves residue and fails that gate.
*/

/** @var array{modules: array<string, array<string, mixed>>, last_module: array<string, mixed>} $contract */
$contract = require __DIR__.'/chisel.modules.php';
$modules = $contract['modules'];

$labels = [];

foreach ($modules as $tag => $module) {
    $labels[$tag] = $module['label'];
}

// Checked at call time, not at script-load time: a caller may install
// dependencies between loading the script and applying the answers.
$removePackages = static function (Chisel $chisel, string ...$packages): void {
    if (! is_dir(__DIR__.'/node_modules')) {
        return;
    }

    $chisel->npm()->remove(...$packages);
};

return Chisel::script(__DIR__)
    ->questions([
        Question::multiselect(
            name: 'modules',
            label: 'Which optional modules should this app keep?',
            options: $labels,
            default: array_keys($modules),
            hint: 'Unselected modules are removed wholesale. Space toggles, enter confirms.',
        ),
    ])
    ->selected('modules', 'notes',
        then: fn (Chisel $chisel) => $chisel->files(...$modules['notes']['shared'])->removeSectionMarkers('notes'),
        else: function (Chisel $chisel) use ($modules, $removePackages): void {
            $chisel->files(...$modules['notes']['owned'])->delete();
            $chisel->files(...$modules['notes']['shared'])->removeSection('notes');

            $chisel->file('resources/js/lib/nav.ts')
                ->removeLinesContaining('@/routes/notes')
                ->replace('{ Globe, LayoutGrid, NotebookPen }', '{ Globe, LayoutGrid }');

            $chisel->file('resources/js/pages/dashboard.tsx')
                ->removeLinesContaining("import EmptyState from '@/components/empty-state';")
                ->removeLinesContaining('@/components/notes/note-list')
                ->removeLinesContaining('@/routes/notes')
                ->removeLinesContaining('notes: number;')
                ->removeLinesContaining('recentNotes: NoteListItem[];')
                ->removeLinesContaining('recentNotes,')
                ->replace('{ MailPlus, NotebookPen, Plus, Users }', '{ MailPlus, Users }');

            $chisel->php('routes/web/app.php')->removeImport('App\Http\Controllers\Notes\NoteController');
            $chisel->file('app/Models/Team.php')
                ->removeLinesContaining('@property-read Collection<int, Note> $notes');

            $chisel->php('app/Models/Team.php')->removeImport('App\Models\Note');
            $chisel->php('app/Http/Controllers/DashboardController.php')->removeImport('App\Models\Note');
            $chisel->php('database/seeders/DatabaseSeeder.php')->removeImport('App\Models\Note');
            $chisel->php('tests/Feature/DashboardTest.php')->removeImport('App\Models\Note');

            $removePackages($chisel, '@uiw/react-md-editor');
        },
    )
    ->selected('modules', 'posts',
        then: fn (Chisel $chisel) => $chisel->files(...$modules['posts']['shared'])->removeSectionMarkers('posts'),
        else: function (Chisel $chisel) use ($modules): void {
            $chisel->files(...$modules['posts']['owned'])->delete();
            $chisel->files(...$modules['posts']['shared'])->removeSection('posts');

            $chisel->file('resources/js/lib/nav.ts')->removeLinesContaining('@/routes/posts');
            $chisel->php('routes/web/public.php')->removeImport('App\Http\Controllers\Public\PostController');
            $chisel->php('database/seeders/DatabaseSeeder.php')->removeImport('App\Models\Post');
        },
    )
    ->selected('modules', 'passkeys',
        then: fn (Chisel $chisel) => $chisel->files(...$modules['passkeys']['shared'])->removeSectionMarkers('passkeys'),
        else: function (Chisel $chisel) use ($modules, $removePackages): void {
            $chisel->files(...$modules['passkeys']['owned'])->delete();
            $chisel->files(...$modules['passkeys']['shared'])->removeSection('passkeys');

            $chisel->php('app/Models/User.php')
                ->removeInterface('PasskeyUser')
                ->removeTrait('PasskeyAuthenticatable')
                ->removeImport('Laravel\Fortify\Contracts\PasskeyUser')
                ->removeImport('Laravel\Fortify\PasskeyAuthenticatable');

            $chisel->php('app/Providers/FortifyServiceProvider.php')
                ->removeImport('App\Http\Responses\PasskeyLoginResponse')
                ->removeImport('Laravel\Passkeys\Contracts\PasskeyLoginResponse');

            $chisel->file('app/Providers/FortifyServiceProvider.php')
                ->removeLinesContaining('PasskeyLoginResponseContract::class');

            $chisel->file('config/fortify.php')->removeLinesContaining("'passkeys' => 'passkeys',");

            $chisel->file('resources/js/pages/settings/security.tsx')
                ->removeLinesContaining("from '@/components/manage-passkeys';")
                ->replace("} & ManagePasskeysProps &\n    ManageTwoFactorProps;", '} & ManageTwoFactorProps;');

            $chisel->file('resources/js/pages/auth/login.tsx')->removeLinesContaining('PasskeyVerify');

            $chisel->file('tests/Feature/Auth/AuthenticationTest.php')
                ->removeLinesContaining('use Illuminate\Http\Request;');

            $chisel->php('tests/Feature/Auth/AuthenticationTest.php')
                ->removeImport('Laravel\Passkeys\Contracts\PasskeyLoginResponse');

            $chisel->file('tests/Feature/Settings/SecurityTest.php')
                ->removeLinesContaining('canManagePasskeys')
                ->removeLinesContaining("'passkeys', []");

            $removePackages($chisel, '@laravel/passkeys');
        },
    )
    ->apply(function (Chisel $chisel, array $answers) use ($contract, $removePackages): void {
        $kept = (array) ($answers['modules'] ?? []);

        // The tests that prove this script works only mean something on a
        // pristine checkout — a project that has used it is done with them.
        if (array_diff(array_keys($contract['modules']), $kept) !== []) {
            $chisel->files(...$contract['template_tooling'])->delete();
        }

        // Pieces that only exist for a set of modules go with the last of their
        // owners (Notes and Posts share the markdown renderer).
        foreach ($contract['shared_pieces'] as $piece) {
            if (array_intersect($piece['owners'], $kept) !== []) {
                continue;
            }

            $chisel->files(...$piece['owned'])->delete();

            $removePackages($chisel, ...$piece['packages']);
        }

        // The starter's own README is scaffolding either way: markers stripped
        // when modules stay, a placeholder to replace when none do (composer run
        // modules fails while the starter title still stands).
        if ($kept !== []) {
            $chisel->file('README.md')->removeSectionMarkers('scaffolding');

            return;
        }

        $chisel->file('README.md')
            ->removeSection('scaffolding')
            ->replace('# Herman Laravel Starter', '# <product name>');
    });
