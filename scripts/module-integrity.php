<?php

declare(strict_types=1);

/**
 * module-integrity — the optional-module gate.
 *
 *   composer run modules
 *   php scripts/module-integrity.php [repo-root]
 *
 * A reference module is either fully present or fully gone. Half-removed means
 * residue the app still serves: routes pointing at deleted controllers, dead
 * UI, tests that no longer run, docs describing features that are not there.
 *
 * Dropping a module is `composer run chisel` — never a hand-edit. This check
 * reads the same contract chisel does (`chisel.modules.php`), so the two can
 * never disagree about what a module owns.
 *
 * Exit code 1 with a JSON issue list. One JSON line either way:
 *
 * {"tool":"module-integrity","result":"passed","modules":{"notes":true,...},"issues":[]}
 */
$root = rtrim($argv[1] ?? dirname(__DIR__), '/');

/** @var array{modules: array<string, array<string, mixed>>, last_module: array<string, mixed>, scaffolding: array<string, mixed>} $contract */
$contract = require __DIR__.'/../chisel.modules.php';

$read = static function (string $file) use ($root): ?string {
    $path = $root.'/'.$file;

    return is_file($path) ? (string) file_get_contents($path) : null;
};

$exists = static fn (string $file): bool => file_exists($root.'/'.$file);

/** @var list<string> $issues */
$issues = [];
$state = [];

// Dependencies can only be verified once a package manager has run — a fresh
// checkout has no node_modules and still lists every dependency.
$dependenciesCheckable = is_dir($root.'/node_modules');

$residueCheck = static function (array $pairs, string $owner) use ($read, $dependenciesCheckable, &$issues): void {
    foreach ($pairs as [$file, $needle]) {
        if ($file === 'package.json' && ! $dependenciesCheckable) {
            continue;
        }

        $contents = $read($file);

        if ($contents !== null && str_contains($contents, $needle)) {
            $issues[] = "{$owner} is gone but {$file} still contains \"{$needle}\".";
        }
    }
};

foreach ($contract['modules'] as $tag => $module) {
    $owned = $module['owned'];
    $kept = array_values(array_filter($owned, $exists));
    $anchor = $exists($module['anchor']);
    $markers = array_values(array_filter(
        $module['shared'],
        static fn (string $file): bool => str_contains($read($file) ?? '', '@chisel-'.$tag),
    ));

    $state[$tag] = $anchor;

    if ($kept !== [] && count($kept) !== count($owned)) {
        $missing = array_values(array_diff($owned, $kept));
        $issues[] = sprintf(
            '%s is half-removed: %d of %d files are left (missing %s). Run "composer run chisel" instead of deleting files by hand.',
            $tag,
            count($kept),
            count($owned),
            implode(', ', array_slice($missing, 0, 3)).(count($missing) > 3 ? ', …' : ''),
        );

        continue;
    }

    if ($markers !== [] && ! $anchor) {
        $issues[] = sprintf(
            '%s was deleted by hand but %s still carry its @chisel-%s markers. Run "composer run chisel" — it removes the module, its routes, tests and docs together.',
            $tag,
            implode(', ', array_slice($markers, 0, 3)).(count($markers) > 3 ? ', …' : ''),
            $tag,
        );

        continue;
    }

    if (! $anchor) {
        $residueCheck($module['residue'], "The {$tag} module");
    }
}

// Pieces that only exist for a set of modules stay only while one of their
// owners does.
foreach ($contract['shared_pieces'] as $piece) {
    if (array_intersect($piece['owners'], array_keys(array_filter($state))) !== []) {
        continue;
    }

    foreach ($piece['owned'] as $file) {
        if ($exists($file)) {
            $issues[] = sprintf(
                '%s is gone, but %s still exists — \"composer run chisel\" removes it with the last module that used it.',
                implode(' and ', $piece['owners']),
                $file,
            );
        }
    }

    $residueCheck($piece['residue'], 'The last reference module');
}

// Nothing optional is left: the project must also have replaced the starter's
// own scaffolding (its README describes modules that no longer exist).
if ($state !== [] && ! in_array(true, $state, true)) {
    $residueCheck($contract['scaffolding']['residue'], 'The starter scaffolding');
}

$result = ['tool' => 'module-integrity', 'result' => $issues === [] ? 'passed' : 'failed'];
$result['modules'] = $state;
$result['issues'] = $issues;

echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL;

exit($issues === [] ? 0 : 1);
