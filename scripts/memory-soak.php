<?php

declare(strict_types=1);

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Vite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite as ViteFacade;
use Illuminate\Support\HtmlString;

/**
 * memory-soak — does the request pipeline hold on to memory across requests?
 *
 * Runs N requests through the framework kernel inside ONE long-lived process,
 * the way Octane, a queue worker or a FrankenPHP worker would, and reports how
 * many bytes each request leaves behind:
 *
 *   composer run soak
 *   composer run soak -- --path=/login --iterations=500 --max-growth-kb-per-request=1
 *
 * Which number matters:
 *   - steady_slope_kb_per_request: the growth per request AFTER the first half
 *     of the run. This is the leak signal: bounded warm-up (route/container
 *     warm caches, once() with a stable argument) plateaus, a leak does not.
 *   - raw_slope_kb_per_request: over the whole run, kept for context.
 *
 * It is deliberately stricter than Octane: Octane additionally resets scoped
 * container instances and once() values between requests, and this harness does
 * not — so a pass here means "no growth a worker could not heal", not "Octane
 * would never leak". Runs with an array session/cache store and a stubbed Vite,
 * so it needs no build artefacts and writes nothing to the database.
 */

require __DIR__.'/../vendor/autoload.php';

$options = getopt('', ['path:', 'iterations:', 'warmup:', 'max-growth-kb-per-request:', 'json']);

$path = is_string($options['path'] ?? null) ? $options['path'] : '/';
$iterations = max(20, (int) ($options['iterations'] ?? 200));
$warmup = max(0, (int) ($options['warmup'] ?? 40));
$budgetKb = (float) ($options['max-growth-kb-per-request'] ?? 0.5);
$jsonOnly = array_key_exists('json', $options);

// Keep the run self-contained and free of influence from the harness itself:
// cookie sessions and a null cache keep no server-side state, so anything that
// grows between requests belongs to the request pipeline, not to my overrides.
// (.env is loaded immutably, so these stick.)
putenv('SESSION_DRIVER=cookie');
putenv('CACHE_STORE=null');
putenv('QUEUE_CONNECTION=sync');
putenv('MAIL_MAILER=array');

// Cookies are carried from each response into the next request, so the run
// looks like one returning visitor rather than N brand-new ones. Without this,
// the cookie session driver queues a cookies whose name IS the new session id
// (CookieSessionHandler::write) and the harness itself grows the jar.

/** @var Application $app */
$app = require __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);

// Boot the providers once, exactly like a worker start-up, BEFORE swapping
// Vite: a provider (laravel-vite-plugin's fonts, for one) binds Vite in the
// container during boot, and Container::bind() drops any instance we set first.
$kernel->bootstrap();

// Same trick as Laravel's testing helper withoutVite(): @vite compiles to
// app(Vite::class)(...), so swapping the instance renders pages with no build.
ViteFacade::clearResolvedInstance(Vite::class);
$app->instance(Vite::class, new class extends Vite
{
    /**
     * @param  string|string[]  $entrypoints
     */
    public function __invoke($entrypoints, $buildDirectory = null): HtmlString
    {
        return new HtmlString('');
    }

    /**
     * @param  array<int, mixed>  $parameters
     */
    public function __call($method, $parameters): string
    {
        return '';
    }

    public function __toString(): string
    {
        return '';
    }
});

$samples = [];
$statuses = [];
$cookies = [];

for ($i = 0; $i < $warmup + $iterations; $i++) {
    $request = Request::create($path, 'GET', [], $cookies);

    $response = $kernel->handle($request);

    $statuses[(string) $response->getStatusCode()] = ($statuses[(string) $response->getStatusCode()] ?? 0) + 1;

    $response->getContent();

    $kernel->terminate($request, $response);

    foreach ($response->headers->getCookies() as $cookie) {
        if ($cookie->getValue() === null) {
            unset($cookies[$cookie->getName()]);

            continue;
        }

        $cookies[$cookie->getName()] = $cookie->getValue();
    }

    unset($response, $request);

    if ($i >= $warmup - 1) {
        // Collect cycles before sampling, so the slope measures memory that is
        // still reachable — the kind a worker cannot reclaim on its own.
        gc_collect_cycles();

        $samples[] = memory_get_usage();
    }
}

/**
 * Least-squares slope in bytes per request over a slice of the samples.
 *
 * @param  array<int, int>  $slice
 */
$slope = static function (array $slice): float {
    $count = count($slice);

    if ($count < 2) {
        return 0.0;
    }

    $meanX = ($count - 1) / 2;
    $meanY = array_sum($slice) / $count;
    $numerator = 0.0;
    $denominator = 0.0;

    foreach ($slice as $x => $bytes) {
        $numerator += ($x - $meanX) * ($bytes - $meanY);
        $denominator += ($x - $meanX) ** 2;
    }

    return $denominator > 0.0 ? $numerator / $denominator : 0.0;
};

/** @param array<int, int> $slice */
$median = static function (array $slice): float {
    if ($slice === []) {
        return 0.0;
    }

    sort($slice);
    $middle = intdiv(count($slice), 2);

    return count($slice) % 2 === 1
        ? (float) $slice[$middle]
        : ($slice[$middle - 1] + $slice[$middle]) / 2;
};

$half = intdiv(count($samples), 2);
$firstHalf = array_slice($samples, 0, $half);
$secondHalf = array_slice($samples, $half);

$rawSlope = $slope($samples);
$steadySlope = $slope($secondHalf);
$failed = $steadySlope > $budgetKb * 1024 || $statuses !== [200 => $iterations + $warmup];

$report = [
    'tool' => 'memory-soak',
    'result' => $failed ? 'failed' : 'passed',
    'path' => $path,
    'iterations' => $iterations,
    'warmup' => $warmup,
    'steady_slope_kb_per_request' => round($steadySlope / 1024, 3),
    'raw_slope_kb_per_request' => round($rawSlope / 1024, 3),
    'budget_kb_per_request' => $budgetKb,
    'first_half_median_kb' => round($median($firstHalf) / 1024, 1),
    'second_half_median_kb' => round($median($secondHalf) / 1024, 1),
    'peak_mb' => round(memory_get_peak_usage(true) / 1048576, 1),
    'status_codes' => $statuses,
];

if ($jsonOnly) {
    echo json_encode($report, JSON_UNESCAPED_SLASHES).PHP_EOL;

    exit($failed ? 1 : 0);
}

echo 'memory soak: '.$report['iterations'].' requests to '.$report['path'].' ('.$report['warmup'].' warm-up)'.PHP_EOL;
echo '  steady-state growth: '.$report['steady_slope_kb_per_request'].' KB/request (budget '.$report['budget_kb_per_request'].')'.PHP_EOL;
echo '  whole-run growth:    '.$report['raw_slope_kb_per_request'].' KB/request'.PHP_EOL;
echo '  retained memory:     '.$report['first_half_median_kb'].' KB -> '.$report['second_half_median_kb'].' KB'.PHP_EOL;
echo '  peak:                '.$report['peak_mb'].' MB'.PHP_EOL;
echo '  status codes:        '.json_encode($statuses).PHP_EOL;
echo ($failed ? 'FAILED' : 'ok').PHP_EOL;

exit($failed ? 1 : 0);
