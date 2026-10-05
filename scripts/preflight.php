<?php

declare(strict_types=1);

/**
 * Preflight — one line per environment fact, for humans and agents.
 *
 * Runs before `composer install` (plain PHP, no framework boot) so a broken
 * environment is reported as a checklist instead of a stack trace:
 *
 *   composer run preflight
 *
 * Hard requirements (PHP, extensions, tooling) exit non-zero. Things that a
 * first run legitimately lacks (vendor/, .env, the SQLite file) are reported
 * as `todo` with the command that fixes them.
 */
$root = dirname(__DIR__);

/** @var list<string> $missing */
$missing = [];
$lines = [];

$required = static function (string $label, bool $ok, string $detail = '') use (&$missing, &$lines): void {
    $lines[] = sprintf('%-24s %s', $label, $ok ? 'ok' : 'MISSING'.($detail !== '' ? ' — '.$detail : ''));

    if (! $ok) {
        $missing[] = $label;
    }
};

$info = static function (string $label, bool $ok, string $detail = '') use (&$lines): void {
    $lines[] = sprintf('%-24s %s', $label, $ok ? 'ok' : 'todo'.($detail !== '' ? ' — '.$detail : ''));
};

$line = static function (string $label, string $value) use (&$lines): void {
    $lines[] = sprintf('%-24s %s', $label, $value);
};

$commandExists = static function (string $command): ?bool {
    if (! function_exists('shell_exec')) {
        return null;
    }

    return trim((string) shell_exec('command -v '.escapeshellarg($command).' 2>/dev/null')) !== '';
};

// ---- Runtime ----------------------------------------------------------
$line('php', PHP_VERSION);

if (PHP_VERSION_ID < 80500) {
    $lines[count($lines) - 1] .= ' (8.5 expected — CI, Dockerfile and the gate all run 8.5)';
}

$required('php >= 8.4', PHP_VERSION_ID >= 80400);

foreach ([
    'pdo_sqlite' => true, 'sqlite3' => true, 'mbstring' => true, 'openssl' => true,
    'tokenizer' => true, 'xml' => true, 'dom' => true, 'ctype' => true, 'fileinfo' => true,
    'curl' => true, 'session' => true, 'intl' => false, 'zip' => false,
] as $extension => $hard) {
    $hard
        ? $required('ext-'.$extension, extension_loaded($extension))
        : $info('ext-'.$extension, extension_loaded($extension), 'recommended');
}

// ---- Tooling ----------------------------------------------------------
// On a host where shell_exec() is disabled the answer is "unknown", not "missing".
foreach (['composer' => 'https://getcomposer.org', 'bun' => 'https://bun.sh', 'git' => 'https://git-scm.com'] as $tool => $url) {
    match ($commandExists($tool)) {
        true => $required($tool, true),
        false => $required($tool, false, $url),
        null => $info($tool, false, 'could not check (shell_exec disabled)'),
    };
}

// ---- Checkout state (fixable) -----------------------------------------
$info('vendor/', is_file($root.'/vendor/autoload.php'), 'composer install');
$info('node_modules/', is_dir($root.'/node_modules'), 'bun install');
$info('.env', is_file($root.'/.env'), 'cp .env.example .env && php artisan key:generate');
$info('database.sqlite', is_file($root.'/database/database.sqlite'), 'php artisan migrate');

// ---- Wayfinder output (gitignored, needed by tsc) ----------------------
$info('wayfinder output', is_dir($root.'/resources/js/actions') && is_dir($root.'/resources/js/routes'), 'php artisan wayfinder:generate --with-form');

echo implode(PHP_EOL, $lines).PHP_EOL;

if ($missing !== []) {
    echo PHP_EOL.'preflight: '.count($missing).' missing requirement(s): '.implode(', ', $missing).PHP_EOL;

    exit(1);
}

echo PHP_EOL.'preflight: ok'.PHP_EOL;
