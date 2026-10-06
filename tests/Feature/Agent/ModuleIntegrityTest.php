<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Optional-module integrity
|--------------------------------------------------------------------------
|
| scripts/module-integrity.php is the gate that keeps a half-removed module out
| of the product: files, routes, tests and docs leave together or not at all.
| `composer run modules` prints the same verdict for an agent, and `php artisan
| test` — one of the wizard's host checks — enforces it, so a half-removal fails
| the build instead of a review.
|
| Removing a module is `composer run chisel`; the scenarios below are the shapes
| a hand-deletion leaves behind.
|
*/

/**
 * Run the gate against a project root.
 *
 * @return array{status: int, report: array<string, mixed>}
 */
function moduleGate(string $root): array
{
    $output = [];

    exec(
        'php '.escapeshellarg(dirname(__DIR__, 3).'/scripts/module-integrity.php').' '.escapeshellarg($root).' 2>&1',
        $output,
        $status,
    );

    return ['status' => $status, 'report' => (array) json_decode(implode("\n", $output), true)];
}

/**
 * A throwaway project root holding only the files a scenario needs.
 *
 * @param  array<string, string>  $files
 */
function moduleFixture(array $files): string
{
    $root = sys_get_temp_dir().'/herman-modules-'.bin2hex(random_bytes(6));

    foreach ($files as $path => $contents) {
        if (! is_dir(dirname($root.'/'.$path))) {
            mkdir(dirname($root.'/'.$path), 0777, true);
        }

        file_put_contents($root.'/'.$path, $contents);
    }

    return $root;
}

function removeModuleFixture(string $root): void
{
    exec('rm -rf '.escapeshellarg($root));
}

test('this repository passes its own module gate', function () {
    // Holds in both states: the pristine template (every module present) and a
    // project that has resolved its modules with chisel.
    $result = moduleGate(dirname(__DIR__, 3));

    expect($result['status'])->toBe(0)
        ->and($result['report']['issues'])->toBe([])
        ->and($result['report']['modules'])->toBeArray();
});

test('a half-removed module fails the gate', function () {
    // The anchor survives, the rest of the module does not: what a hand-deletion
    // that stopped halfway looks like.
    $root = moduleFixture(['app/Models/Note.php' => "<?php\n"]);

    try {
        $result = moduleGate($root);

        expect($result['status'])->toBe(1)
            ->and(implode(' ', (array) $result['report']['issues']))
            ->toContain('half-removed')
            ->toContain('composer run chisel');
    } finally {
        removeModuleFixture($root);
    }
});

test('a deleted module that left its markers behind fails the gate', function () {
    $root = moduleFixture([
        'routes/web/app.php' => "<?php\n\n/* @chisel-notes */\nRoute::resource('notes', NoteController::class);\n/* @end-chisel-notes */\n",
    ]);

    try {
        $result = moduleGate($root);

        expect($result['status'])->toBe(1)
            ->and(implode(' ', (array) $result['report']['issues']))->toContain('deleted by hand');
    } finally {
        removeModuleFixture($root);
    }
});

test('residue and the starter README fail the gate once every module is gone', function () {
    $root = moduleFixture([
        'resources/js/lib/nav.ts' => "import { notesIndex } from '@/routes/notes';\n",
        'README.md' => "# Herman Laravel Starter\n",
    ]);

    try {
        $result = moduleGate($root);

        expect($result['status'])->toBe(1);

        $issues = implode(' ', (array) $result['report']['issues']);

        expect($issues)
            ->toContain('nav.ts')
            ->toContain('still contains')
            ->toContain('starter scaffolding');
    } finally {
        removeModuleFixture($root);
    }
});
