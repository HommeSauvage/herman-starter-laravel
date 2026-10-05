<?php

declare(strict_types=1);

use Laravel\Chisel\Chisel;
use Laravel\Chisel\Question;

/*
|--------------------------------------------------------------------------
| Chisel script — optional-feature removal
|--------------------------------------------------------------------------
|
| The Herman starter ships two reference modules (Notes, Posts) that set the
| quality bar and are meant to be kept or dropped wholesale. This script is
| the single source of truth for dropping them: files, marked sections,
| imports and optional JS dependencies.
|
|   composer run chisel                       # interactive
|   composer run chisel -- --answers='{"modules":["notes"]}'   # agent / wizard
|
| Selected module  → its @chisel-<tag> markers are stripped; the feature stays.
| Dropped module   → its files are deleted, marked sections and imports are
|                    removed, and its optional JS dependency is uninstalled.
|
| Run it before the first `php artisan migrate` (a dropped module's migration
| is deleted, not rolled back). Run it after `composer run setup` so
| node_modules exists and JS dependencies can be pruned.
*/

// Checked at call time, not at script-load time: a caller may install
// dependencies between loading the script and applying the answers.
$removePackages = static function (Chisel $chisel, string ...$packages): void {
    if (! is_dir(__DIR__.'/node_modules')) {
        return;
    }

    $chisel->npm()->remove(...$packages);
};

$moduleFiles = [
    'notes' => [
        'routes/web/app.php',
        'app/Models/Team.php',
        'app/Http/Controllers/DashboardController.php',
        'database/seeders/DatabaseSeeder.php',
        'resources/js/lib/nav.ts',
        'resources/js/pages/dashboard.tsx',
        'tests/Feature/DashboardTest.php',
        'AGENTS.md',
    ],
    'posts' => [
        'routes/web/public.php',
        'database/seeders/DatabaseSeeder.php',
        'resources/js/lib/nav.ts',
        'AGENTS.md',
    ],
];

return Chisel::script(__DIR__)
    ->questions([
        Question::multiselect(
            name: 'modules',
            label: 'Which reference modules should this app keep?',
            options: [
                'notes' => 'Notes — team-scoped CRUD reference (list, detail, create/edit, policies, tests)',
                'posts' => 'Posts — public content reference (index, detail, markdown body)',
            ],
            default: ['notes', 'posts'],
            hint: 'Unselected modules are removed wholesale. Space toggles, enter confirms.',
        ),
    ])
    ->selected('modules', 'notes',
        then: fn (Chisel $chisel) => $chisel->files(...$moduleFiles['notes'])->removeSectionMarkers('notes'),
        else: function (Chisel $chisel) use ($moduleFiles, $removePackages): void {
            $chisel->files(
                'app/Http/Controllers/Notes/NoteController.php',
                'app/Http/Requests/Notes/StoreNoteRequest.php',
                'app/Http/Requests/Notes/UpdateNoteRequest.php',
                'app/Models/Note.php',
                'app/Policies/NotePolicy.php',
                'database/factories/NoteFactory.php',
                'database/migrations/2026_07_19_041832_create_notes_table.php',
                'resources/js/components/notes/delete-note-dialog.tsx',
                'resources/js/components/notes/markdown-editor.tsx',
                'resources/js/components/notes/note-breadcrumbs.ts',
                'resources/js/components/notes/note-form.tsx',
                'resources/js/components/notes/note-list.tsx',
                'resources/js/pages/notes/create.tsx',
                'resources/js/pages/notes/edit.tsx',
                'resources/js/pages/notes/index.tsx',
                'resources/js/pages/notes/show.tsx',
                'tests/Feature/Notes/NoteTest.php',
            )->delete();

            $chisel->files(...$moduleFiles['notes'])->removeSection('notes');

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
            $chisel->php('app/Models/Team.php')->removeImport('App\Models\Note');
            $chisel->php('app/Http/Controllers/DashboardController.php')->removeImport('App\Models\Note');
            $chisel->php('database/seeders/DatabaseSeeder.php')->removeImport('App\Models\Note');
            $chisel->php('tests/Feature/DashboardTest.php')->removeImport('App\Models\Note');

            $removePackages($chisel, '@uiw/react-md-editor');
        },
    )
    ->selected('modules', 'posts',
        then: fn (Chisel $chisel) => $chisel->files(...$moduleFiles['posts'])->removeSectionMarkers('posts'),
        else: function (Chisel $chisel) use ($moduleFiles): void {
            $chisel->files(
                'app/Http/Controllers/Public/PostController.php',
                'app/Models/Post.php',
                'database/factories/PostFactory.php',
                'database/migrations/2026_07_30_034924_create_posts_table.php',
                'resources/js/pages/public/posts/index.tsx',
                'resources/js/pages/public/posts/show.tsx',
                'tests/Feature/Posts/PostTest.php',
            )->delete();

            $chisel->files(...$moduleFiles['posts'])->removeSection('posts');

            $chisel->file('resources/js/lib/nav.ts')->removeLinesContaining('@/routes/posts');
            $chisel->php('routes/web/public.php')->removeImport('App\Http\Controllers\Public\PostController');
            $chisel->php('database/seeders/DatabaseSeeder.php')->removeImport('App\Models\Post');
        },
    )
    ->apply(function (Chisel $chisel, array $answers) use ($removePackages): void {
        $modules = (array) ($answers['modules'] ?? []);

        // markdown-body.tsx and react-markdown are shared by both modules.
        if (! in_array('notes', $modules, true) && ! in_array('posts', $modules, true)) {
            $chisel->file('resources/js/components/markdown-body.tsx')->delete();

            $removePackages($chisel, 'react-markdown');
        }
    });
