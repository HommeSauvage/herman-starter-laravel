<?php

declare(strict_types=1);

use Symfony\Component\Finder\Finder;

/*
|--------------------------------------------------------------------------
| Octane readiness
|--------------------------------------------------------------------------
|
| Octane keeps the app in memory between requests, so a template that runs it
| inherits a different memory contract: workers must recycle, scoped services
| must be scoped, and the leak-prone reset listeners must be on. These checks
| are skipped until laravel/octane is actually installed in the template.
|
| Measured floor in this repo (scripts/memory-soak.php): 0.07 KB/request on a
| warm worker. Anything that keeps growing is a leak the worker cannot heal.
|
*/

test('an Octane template ships the guards that bound worker memory', function () {
    $root = dirname(__DIR__, 3);
    $octaneConfig = $root.'/config/octane.php';

    if (! is_file($octaneConfig)) {
        $this->markTestSkipped('laravel/octane is not installed in this template.');
    }

    $octane = (string) file_get_contents($octaneConfig);

    // Everything a deploy could start the worker with.
    $deployment = '';
    foreach (['Dockerfile', 'docker/entrypoint.sh', 'docker/Caddyfile', 'Caddyfile', 'composer.json', 'package.json', 'Procfile'] as $file) {
        $deployment .= is_file($root.'/'.$file) ? "\n// {$file}\n".file_get_contents($root.'/'.$file) : '';
    }

    if (preg_match('/octane:(frankenphp|start|swoole|roadrunner)/', $deployment) === 1) {
        preg_match_all('/--max-requests[=\s]+(\d+)/', $deployment, $matches);

        $this->assertNotEmpty(
            $matches[1],
            'Octane must be started with an explicit --max-requests: the default (500) is too generous for agent-written code. 100-250 turns a slow leak into a millisecond worker restart.'
        );

        $this->assertLessThanOrEqual(
            250,
            max(array_map(intval(...), $matches[1])),
            'Octane --max-requests must stay at or below 250 for generated apps.'
        );

        $this->assertDoesNotMatchRegularExpression(
            '/--workers[=\s]+auto/',
            $deployment,
            'Pin --workers in containers; auto scales to the host CPU count and multiplies the per-worker memory budget.'
        );

        $this->assertMatchesRegularExpression(
            '/--workers[=\s]+\d+/',
            $deployment,
            'Octane must pin --workers to a number in a container.'
        );
    }

    // Laravel ships both listeners commented out in the published config.
    $this->assertDoesNotMatchRegularExpression(
        '/\/\/[^\n]*\bDisconnectFromDatabases\b/',
        $octane,
        'Enable DisconnectFromDatabases in config/octane.php: a worker that holds connections forever is the most common slow-growth source.'
    );

    $this->assertDoesNotMatchRegularExpression(
        '/\/\/[^\n]*\bCollectGarbage\b/',
        $octane,
        'Enable CollectGarbage in config/octane.php: a cycle collection per request is cheap insurance against agent-written object graphs.'
    );

    $this->assertDoesNotMatchRegularExpression(
        "/'max_execution_time'\\s*=>\\s*0\\b/",
        $octane,
        'Never disable max_execution_time under Octane: one runaway handler would hold a worker forever.'
    );

    // The log Context repository is a container singleton that nothing resets
    // per request. If the app writes to it, the template must flush it.
    $appSource = '';
    foreach (Finder::create()->files()->in($root.'/app')->name('*.php') as $file) {
        $appSource .= $file->getContents();
    }

    if (preg_match('/withContext\(|Context::(add|push)\(/', $appSource) === 1) {
        $this->assertTrue(
            str_contains($octane, 'Illuminate\\Log\\Context\\Repository::class') || str_contains($appSource, 'Context::flush('),
            'app code logs request context (Log::withContext / Context::add), which is a container singleton under Octane. Add Illuminate\\Log\\Context\\Repository::class to the octane.flush array, or call Context::flush() on RequestTerminated.'
        );
    }

    // FrankenPHP worker mode configured through a Caddyfile needs the same cap.
    foreach (['Caddyfile', 'docker/Caddyfile'] as $caddyfile) {
        if (! is_file($root.'/'.$caddyfile)) {
            continue;
        }

        $caddy = (string) file_get_contents($root.'/'.$caddyfile);

        if (preg_match('/\bworker\b/', $caddy) === 1) {
            $this->assertMatchesRegularExpression(
                '/max_requests|MAX_REQUESTS/',
                $caddy,
                $caddyfile.' runs FrankenPHP worker mode without max_requests, its own restart knob.'
            );
        }
    }
});
