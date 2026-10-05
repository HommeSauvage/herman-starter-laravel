<?php

declare(strict_types=1);

/**
 * agent:report — one JSON line of agent-relevant facts about this checkout.
 *
 * The wizard tracks what a change cost in tokens; this is the other half of
 * "correct code per token": proof that the change works, in a shape a harness
 * can store without parsing prose.
 *
 *   composer run agent:report
 *
 * {
 *   "tool": "agent-report", "result": "passed", "tests_result": "passed",
 *   "soak_result": "passed", "tests": 115, "passed": 115, "assertions": 542,
 *   "duration_ms": 2147, "routes": 42, "boot_peak_mb": 24.0,
 *   "soak_kb_per_request": 0.07, "container_memory_limit": "256M"
 * }
 */
$root = dirname(__DIR__);
$run = static fn (string $command): string => (string) shell_exec('cd '.escapeshellarg($root).' && '.$command.' 2>&1');

$report = ['tool' => 'agent-report'];

// Tests — PAO_FORCE makes laravel/pao emit its compact JSON even outside an agent.
$tests = null;

foreach (array_reverse(preg_split('/\R/', trim($run('PAO_FORCE=1 php artisan test --compact'))) ?: []) as $line) {
    $decoded = json_decode(trim($line), true);

    if (is_array($decoded) && array_key_exists('tests', $decoded)) {
        $tests = $decoded;

        break;
    }
}

$report['tests_result'] = ($tests['result'] ?? 'unknown') === 'passed' ? 'passed' : 'failed';
$report += array_intersect_key($tests ?? [], array_flip(['tests', 'passed', 'assertions', 'duration_ms']));

// Does the request pipeline hold on to memory between requests? The budget is
// deliberately lenient here; the strict guard is `composer run soak`.
$soak = json_decode(trim($run('php scripts/memory-soak.php --json --iterations=150 --warmup=30 --max-growth-kb-per-request=2')), true);
$report['soak_result'] = is_array($soak) && ($soak['result'] ?? null) === 'passed' ? 'passed' : 'failed';
$report['soak_kb_per_request'] = is_array($soak) ? ($soak['steady_slope_kb_per_request'] ?? null) : null;

$report['result'] = $report['tests_result'] === 'passed' && $report['soak_result'] === 'passed' ? 'passed' : 'failed';

// Route surface — the wizard's route sweep reads the same list.
$routes = json_decode($run('php artisan route:list --json'), true);
$report['routes'] = is_array($routes) ? count($routes) : null;

// Boot memory — the per-request budget the hosting contract depends on.
$peak = $run("php -d memory_limit=256M -r 'require \"vendor/autoload.php\"; \$app = require \"bootstrap/app.php\"; \$app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap(); echo memory_get_peak_usage(true) / 1048576;'");
$report['boot_peak_mb'] = is_numeric(trim($peak)) ? round((float) trim($peak), 1) : null;

// The hosting contract is the container budget, not this CLI process's limit.
preg_match('/^\s*memory_limit\s*=\s*(\S+)/mi', (string) file_get_contents($root.'/docker/php.ini'), $containerLimit);
$report['container_memory_limit'] = $containerLimit[1] ?? null;

echo json_encode($report, JSON_UNESCAPED_SLASHES).PHP_EOL;

exit($report['result'] === 'passed' ? 0 : 1);
