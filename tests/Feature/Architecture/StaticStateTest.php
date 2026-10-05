<?php

declare(strict_types=1);

use Symfony\Component\Finder\Finder;

/*
|--------------------------------------------------------------------------
| No mutable static state in app/
|--------------------------------------------------------------------------
|
| A static array or property that grows per request is harmless in classic
| per-request hosting (every request gets a fresh process) but a real leak in
| anything long-lived: Octane workers, queue workers, the scheduler. It is the
| canonical leak example in Laravel's own Octane documentation.
|
| Enforced with the tokenizer rather than a regex, so static::, static
| function, static fn and the static return type are not false positives. A
| genuine exemption needs a @static-safe comment on the same line, which makes
| it a reviewable decision instead of a silent one.
|
*/

/** @return list<array{file: string, line: int, code: string}> */
function mutableStaticStateViolations(string $directory): array
{
    $violations = [];
    $typeTokens = [T_STRING, T_ARRAY, T_CALLABLE, T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED, T_NAME_RELATIVE, T_NS_SEPARATOR];

    foreach (Finder::create()->files()->in($directory)->name('*.php') as $file) {
        $code = (string) $file->getContents();
        $lines = explode("\n", $code);
        $tokens = PhpToken::tokenize($code);
        $count = count($tokens);

        foreach ($tokens as $index => $token) {
            if ($token->id !== T_STATIC) {
                continue;
            }

            $isMutableStatic = false;

            for ($cursor = $index + 1; $cursor < $count; $cursor++) {
                $candidate = $tokens[$cursor];

                if ($candidate->isIgnorable()) {
                    continue;
                }

                if ($candidate->id === T_VARIABLE) {
                    $isMutableStatic = true;

                    break;
                }

                if (in_array($candidate->id, $typeTokens, true) || in_array($candidate->text, ['?', '|', '&'], true)) {
                    continue;
                }

                break;
            }

            if (! $isMutableStatic) {
                continue;
            }

            $line = $lines[$token->line - 1] ?? '';

            if (str_contains($line, '@static-safe')) {
                continue;
            }

            $violations[] = [
                'file' => str_replace(dirname($directory).'/', '', $file->getPathname()),
                'line' => $token->line,
                'code' => trim($line),
            ];
        }
    }

    return $violations;
}

test('app code holds no mutable static state', function () {
    $violations = mutableStaticStateViolations(dirname(__DIR__, 3).'/app');

    $report = implode(PHP_EOL, array_map(
        static fn (array $violation): string => sprintf('  %s:%d  %s', $violation['file'], $violation['line'], $violation['code']),
        $violations,
    ));

    $this->assertSame(
        [],
        $violations,
        'Mutable static state in app/ leaks in every long-lived process (Octane, queue workers, the scheduler): the app boots once and statics never reset. Move the data into Cache, a scoped() service or a request-local variable, or annotate the line with @static-safe and say why.'
        .PHP_EOL.PHP_EOL.$report
    );
});
