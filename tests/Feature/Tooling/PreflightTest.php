<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Preflight
|--------------------------------------------------------------------------
|
| `composer run setup` (and therefore every wizard scaffold) starts with
| scripts/preflight.php. If that script exits non-zero on a healthy checkout,
| setup dies before it installs anything — so keep it honest.
|
*/

test('preflight passes in a set-up checkout', function () {
    exec('cd '.escapeshellarg(dirname(__DIR__, 3)).' && php scripts/preflight.php 2>&1', $output, $status);

    $report = implode(PHP_EOL, $output);

    $this->assertSame(0, $status, 'preflight exited non-zero:'.PHP_EOL.$report);
    $this->assertStringContainsString('preflight: ok', $report);
    $this->assertStringContainsString('php', $report);
});
