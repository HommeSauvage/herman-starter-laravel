<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Memory guard
|--------------------------------------------------------------------------
|
| The wizard hosts every generated app as its own small container, so the
| request memory budget is a hosting contract, not a preference:
|
|   - classic FrankenPHP (shared-nothing) means a runaway request dies at
|     memory_limit; a long-lived worker would instead grow until the kernel
|     OOM-kills the whole container;
|   - if this repo ever moves to worker mode or ships a queue worker in the
|     container, the caps that bound work per process must come along.
|
| Two halves: the shipped container config, and the app's actual boot cost.
|
*/

test('the production container bounds request memory', function () {
    $root = dirname(__DIR__, 3);

    $ini = (string) file_get_contents($root.'/docker/php.ini');
    $dockerfile = (string) file_get_contents($root.'/Dockerfile');
    $entrypoint = (string) file_get_contents($root.'/docker/entrypoint.sh');

    preg_match('/^\s*memory_limit\s*=\s*(\d+)([MG])/mi', $ini, $memoryLimit);
    preg_match('/^\s*max_memory_limit\s*=\s*(\d+)([MG])/mi', $ini, $maxMemoryLimit);

    $mib = static fn (string $number, string $unit): int => ((int) $number) * ($unit === 'G' ? 1024 : 1);

    $this->assertNotEmpty($memoryLimit, 'docker/php.ini must set memory_limit explicitly.');
    $this->assertNotEmpty($maxMemoryLimit, 'docker/php.ini must set max_memory_limit (PHP 8.5) so runtime ini_set() cannot bypass the budget.');

    $limit = $mib($memoryLimit[1], strtoupper($memoryLimit[2]));
    $maxLimit = $mib($maxMemoryLimit[1], strtoupper($maxMemoryLimit[2]));

    $this->assertGreaterThanOrEqual(128, $limit, 'memory_limit is too tight for Laravel (min 128M).');
    $this->assertLessThanOrEqual(512, $limit, 'memory_limit above 512M weakens the per-tenant blast radius.');
    $this->assertGreaterThan($limit, $maxLimit, 'max_memory_limit must be strictly above memory_limit.');
    $this->assertLessThanOrEqual(1024, $maxLimit, 'max_memory_limit above 1G defeats the guard.');

    $this->assertStringContainsString(
        'COPY docker/php.ini /usr/local/etc/php/conf.d/',
        $dockerfile,
        'The runtime image must copy docker/php.ini into PHP conf.d.',
    );

    $this->assertStringContainsString('frankenphp run', $entrypoint, 'The container must serve via FrankenPHP.');

    if (str_contains($entrypoint, '--worker')) {
        $this->assertStringContainsString('--max-requests', $entrypoint, 'FrankenPHP worker mode must cap --max-requests.');
    }

    foreach (['docker/entrypoint.sh', 'Dockerfile', 'composer.json'] as $file) {
        $contents = (string) file_get_contents($root.'/'.$file);

        if (preg_match('/queue:work|octane:start/', $contents) === 1) {
            $this->assertMatchesRegularExpression(
                '/queue:work[^\n]*--max-jobs|octane:start[^\n]*--max-requests/',
                $contents,
                $file.' runs a long-lived PHP process without --max-jobs / --max-requests.',
            );
        }
    }

    $this->assertStringNotContainsString('php8.4', $dockerfile, 'Runtime and assets stages must match CI (PHP 8.5).');
});

test('the app boots within the per-request memory budget', function () {
    $root = dirname(__DIR__, 3);

    if (! is_file($root.'/vendor/autoload.php')) {
        $this->markTestSkipped('vendor/ is not installed.');
    }

    $budgetMb = 64;

    $code = 'require "vendor/autoload.php";'
        .' $app = require "bootstrap/app.php";'
        .' $app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap();'
        .' echo round(memory_get_peak_usage(true) / 1048576, 1);';

    exec(
        sprintf('cd %s && php -d memory_limit=256M -r %s 2>/dev/null', escapeshellarg($root), escapeshellarg($code)),
        $output,
        $status,
    );

    $peak = (float) trim(implode('', $output));

    $this->assertSame(0, $status, 'Booting the framework failed.');
    $this->assertGreaterThan(0.0, $peak, 'Could not read the boot peak memory.');
    $this->assertLessThanOrEqual(
        $budgetMb,
        $peak,
        sprintf('Framework boot peaked at %.1f MB (budget %d MB) — something eager (a service provider, a config, a package) is loading too much.', $peak, $budgetMb),
    );
});
