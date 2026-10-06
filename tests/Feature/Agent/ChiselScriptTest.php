<?php

declare(strict_types=1);

use Laravel\Chisel\Script;
use PHPUnit\Framework\Assert;

/*
|--------------------------------------------------------------------------
| Chisel script
|--------------------------------------------------------------------------
|
| `chisel.php` is how a reference module is dropped — the wizard calls it via
| `composer run chisel -- --answers='{"modules":[...]}'`. These tests run it on
| a throwaway copy of the repository, so a half-deleted module can never land.
|
*/

/**
 * Copy the repo (minus vendor/node_modules/caches) and load its chisel script.
 *
 * @return array{0: string, 1: Script}
 */
function chiselSandbox(): array
{
    $root = dirname(__DIR__, 3);
    $sandbox = sys_get_temp_dir().'/herman-chisel-'.bin2hex(random_bytes(6));

    mkdir($sandbox, 0777, true);

    exec(sprintf(
        'cd %s && tar -cf - --exclude=.git --exclude=vendor --exclude=node_modules --exclude=storage '
        .'--exclude=.fallow --exclude=.phpunit.cache --exclude=public/build . | (cd %s && tar -xf -)',
        escapeshellarg($root),
        escapeshellarg($sandbox),
    ), $output, $status);

    if ($status !== 0 || ! is_file($sandbox.'/chisel.php')) {
        throw new RuntimeException('Could not create the chisel sandbox.');
    }

    /** @var Script $script */
    $script = require $sandbox.'/chisel.php';

    return [$sandbox, $script];
}

function removeChiselSandbox(string $sandbox): void
{
    exec('rm -rf '.escapeshellarg($sandbox));
}

/**
 * The gate the chiselled project has to satisfy — `composer run modules`.
 * Chisel and the gate read the same contract (chisel.modules.php), so this is
 * what proves the two agree: every removal option leaves a project with no
 * residue for the gate to complain about.
 */
function assertChiselOutputPassesModuleGate(string $sandbox): void
{
    $output = [];

    exec('php '.escapeshellarg($sandbox.'/scripts/module-integrity.php').' '.escapeshellarg($sandbox).' 2>&1', $output, $status);

    Assert::assertSame(0, $status, 'The chiselled project fails the module gate: '.implode(' ', $output));
}

/**
 * @param  list<string>  $paths
 */
function assertChiselPhpLints(string $sandbox, array $paths): void
{
    foreach ($paths as $path) {
        exec('php -l '.escapeshellarg($sandbox.'/'.$path).' 2>&1', $output, $status);

        Assert::assertSame(0, $status, sprintf('php -l failed for %s: %s', $path, implode(' ', $output)));
    }
}

test('keeps both modules and only strips the chisel markers', function () {
    [$sandbox, $script] = chiselSandbox();

    try {
        $script->chisel(['modules' => ['notes', 'posts']]);

        foreach (['app/Models/Note.php', 'app/Models/Post.php', 'resources/js/components/markdown-body.tsx', 'tests/Feature/Notes/NoteTest.php'] as $path) {
            $this->assertFileExists($sandbox.'/'.$path, "{$path} must survive when its module is kept.");
        }

        foreach (['resources/js/lib/nav.ts', 'resources/js/pages/dashboard.tsx', 'AGENTS.md', 'routes/web/app.php', 'database/seeders/DatabaseSeeder.php'] as $path) {
            $this->assertStringNotContainsString('@chisel-', (string) file_get_contents($sandbox.'/'.$path), "{$path} still has chisel markers.");
        }

        $nav = (string) file_get_contents($sandbox.'/resources/js/lib/nav.ts');

        $this->assertStringContainsString('notesIndex', $nav);
        $this->assertStringContainsString('postsIndex', $nav);

        assertChiselOutputPassesModuleGate($sandbox);
    } finally {
        removeChiselSandbox($sandbox);
    }
});

test('drops notes without leaving references behind', function () {
    [$sandbox, $script] = chiselSandbox();

    try {
        $script->chisel(['modules' => ['posts']]);

        foreach ([
            'app/Models/Note.php',
            'app/Policies/NotePolicy.php',
            'app/Http/Controllers/Notes/NoteController.php',
            'app/Http/Requests/Notes/StoreNoteRequest.php',
            'app/Http/Requests/Notes/UpdateNoteRequest.php',
            'database/factories/NoteFactory.php',
            'resources/js/pages/notes/index.tsx',
            'resources/js/components/notes/note-list.tsx',
            'tests/Feature/Notes/NoteTest.php',
        ] as $path) {
            $this->assertFileDoesNotExist($sandbox.'/'.$path, "{$path} must be deleted with Notes.");
        }

        $this->assertFileExists($sandbox.'/resources/js/components/markdown-body.tsx', 'markdown-body is shared with Posts.');

        $touched = [
            'routes/web/app.php',
            'app/Models/Team.php',
            'app/Http/Controllers/DashboardController.php',
            'database/seeders/DatabaseSeeder.php',
            'tests/Feature/DashboardTest.php',
        ];

        assertChiselPhpLints($sandbox, $touched);

        foreach ($touched as $path) {
            $this->assertStringNotContainsString('@chisel-', (string) file_get_contents($sandbox.'/'.$path), "{$path} still has chisel markers.");
        }

        $this->assertStringNotContainsString('NoteController', (string) file_get_contents($sandbox.'/routes/web/app.php'));
        $this->assertStringNotContainsString('notes()', (string) file_get_contents($sandbox.'/app/Models/Team.php'));

        $seeder = (string) file_get_contents($sandbox.'/database/seeders/DatabaseSeeder.php');

        $this->assertStringNotContainsString('Note::upsert', $seeder);
        $this->assertStringContainsString('Post::upsert', $seeder);

        $dashboardTest = (string) file_get_contents($sandbox.'/tests/Feature/DashboardTest.php');

        $this->assertStringNotContainsString('stats.notes', $dashboardTest);
        $this->assertStringNotContainsString('App\Models\Note', $dashboardTest);

        $dashboard = (string) file_get_contents($sandbox.'/resources/js/pages/dashboard.tsx');

        $this->assertStringNotContainsString('NoteList', $dashboard);
        $this->assertStringNotContainsString('recentNotes', $dashboard);
        $this->assertStringNotContainsString('NotebookPen', $dashboard);

        $nav = (string) file_get_contents($sandbox.'/resources/js/lib/nav.ts');

        $this->assertStringNotContainsString('notesIndex', $nav);
        $this->assertStringContainsString('postsIndex', $nav);
        $this->assertStringNotContainsString('@chisel-', $nav);

        assertChiselOutputPassesModuleGate($sandbox);
    } finally {
        removeChiselSandbox($sandbox);
    }
});

test('drops posts without leaving references behind', function () {
    [$sandbox, $script] = chiselSandbox();

    try {
        $script->chisel(['modules' => ['notes']]);

        foreach ([
            'app/Models/Post.php',
            'app/Http/Controllers/Public/PostController.php',
            'database/factories/PostFactory.php',
            'resources/js/pages/public/posts/index.tsx',
            'tests/Feature/Posts/PostTest.php',
        ] as $path) {
            $this->assertFileDoesNotExist($sandbox.'/'.$path, "{$path} must be deleted with Posts.");
        }

        assertChiselPhpLints($sandbox, ['routes/web/public.php', 'database/seeders/DatabaseSeeder.php']);

        $seeder = (string) file_get_contents($sandbox.'/database/seeders/DatabaseSeeder.php');

        $this->assertStringNotContainsString('Post::upsert', $seeder);
        $this->assertStringContainsString('Note::upsert', $seeder);

        $this->assertStringNotContainsString('PostController', (string) file_get_contents($sandbox.'/routes/web/public.php'));

        $nav = (string) file_get_contents($sandbox.'/resources/js/lib/nav.ts');

        $this->assertStringNotContainsString('postsIndex', $nav);
        $this->assertStringContainsString('notesIndex', $nav);

        $this->assertStringNotContainsString('**Posts**', (string) file_get_contents($sandbox.'/AGENTS.md'));

        assertChiselOutputPassesModuleGate($sandbox);
    } finally {
        removeChiselSandbox($sandbox);
    }
});

test('uninstalls the optional JS dependency when node_modules is present', function () {
    [$sandbox, $script] = chiselSandbox();
    $originalPath = getenv('PATH');

    try {
        mkdir($sandbox.'/node_modules');
        mkdir($sandbox.'/bin');
        file_put_contents($sandbox.'/bin/bun', "#!/bin/sh\necho \"\$@\" >> ".escapeshellarg($sandbox.'/bun-args.log')."\n");
        chmod($sandbox.'/bin/bun', 0755);

        putenv('PATH='.$sandbox.'/bin:'.($originalPath === false ? '' : $originalPath));

        $script->chisel(['modules' => ['posts']]);

        $args = (string) file_get_contents($sandbox.'/bun-args.log');

        $this->assertStringContainsString('remove', $args, 'bun remove was never invoked.');
        $this->assertStringContainsString('@uiw/react-md-editor', $args, 'The Notes-only editor dependency must be pruned.');
    } finally {
        putenv('PATH='.($originalPath === false ? '' : $originalPath));
        removeChiselSandbox($sandbox);
    }
});

test('drops both modules and the shared markdown pieces', function () {
    [$sandbox, $script] = chiselSandbox();

    try {
        $script->chisel(['modules' => []]);

        foreach ([
            'resources/js/components/markdown-body.tsx',
            'app/Models/Note.php',
            'app/Models/Post.php',
            'tests/Feature/Notes/NoteTest.php',
            'tests/Feature/Posts/PostTest.php',
        ] as $path) {
            $this->assertFileDoesNotExist($sandbox.'/'.$path, "{$path} must be gone when both modules are dropped.");
        }

        $seeder = (string) file_get_contents($sandbox.'/database/seeders/DatabaseSeeder.php');

        $this->assertStringNotContainsString('Note::upsert', $seeder);
        $this->assertStringNotContainsString('Post::upsert', $seeder);
        $this->assertStringContainsString('User::factory()', $seeder);

        assertChiselPhpLints($sandbox, ['database/seeders/DatabaseSeeder.php']);

        $nav = (string) file_get_contents($sandbox.'/resources/js/lib/nav.ts');

        $this->assertStringNotContainsString('notesIndex', $nav);
        $this->assertStringNotContainsString('postsIndex', $nav);

        $agents = (string) file_get_contents($sandbox.'/AGENTS.md');

        $this->assertStringNotContainsString('**Notes**', $agents);
        $this->assertStringNotContainsString('**Posts**', $agents);
        $this->assertStringNotContainsString('@chisel-', $agents);

        assertChiselOutputPassesModuleGate($sandbox);
    } finally {
        removeChiselSandbox($sandbox);
    }
});
