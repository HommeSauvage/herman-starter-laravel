<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Agent context budget
|--------------------------------------------------------------------------
|
| Always-loaded context (AGENTS.md) is a tax on every agent step; skills are
| loaded on demand, so their cost only lands when they are actually useful.
| These budgets are guards against quiet growth, not targets — raise one only
| with a reason (and move detail into a skill first if it is conditional).
|
*/

test('the hand-written context stays small', function () {
    // AGENTS.md is the hand-written contract plus a Boost-generated block. Only
    // the hand-written part is ours to keep in check, so strip the generated
    // block first — otherwise its size hides growth in our own prose.
    $agents = (string) file_get_contents(dirname(__DIR__, 3).'/AGENTS.md');
    $agents = (string) preg_replace('/<laravel-boost-guidelines>.*?<\/laravel-boost-guidelines>/s', '', $agents);

    $this->assertLessThanOrEqual(
        5800,
        strlen($agents),
        'Hand-written AGENTS.md is '.strlen($agents).' chars (budget 5800, ~1.5k tokens). Move conditional detail into .agents/skills/ or Boost guidelines.',
    );
});

test('the on-demand skill set stays within budget', function () {
    $root = dirname(__DIR__, 3);

    /** @var list<string> $managed */
    $managed = json_decode((string) file_get_contents($root.'/boost.json'), true)['skills'] ?? [];

    $total = 0;
    $report = [];

    foreach (glob($root.'/.agents/skills/*/*.md') ?: [] as $skill) {
        $chars = strlen((string) file_get_contents($skill));
        $skillRoot = basename(dirname($skill));
        $isManaged = in_array($skillRoot, $managed, true);

        $total += $chars;
        $report[] = sprintf('%s %6d %s', $isManaged ? 'boost' : 'hand ', $chars, str_replace($root.'/', '', $skill));

        if (! $isManaged) {
            $this->assertLessThanOrEqual(
                12000,
                $chars,
                sprintf('%s is %d chars (hand-written skill budget 12000). Split it or trim it.', $skill, $chars),
            );
        }
    }

    sort($report);
    $detail = implode(PHP_EOL, $report);

    $this->assertGreaterThan(0, $total, 'No skills found — did .agents/skills/ move?');

    $this->assertLessThanOrEqual(
        110000,
        $total,
        sprintf('Skills total %d chars (budget 110000, ~27k tokens when all load).'."\n".'%s', $total, $detail),
    );
});
