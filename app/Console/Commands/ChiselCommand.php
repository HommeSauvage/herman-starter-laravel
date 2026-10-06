<?php

declare(strict_types=1);

namespace App\Console\Commands;

use FilesystemIterator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use JsonException;
use Laravel\Chisel\Question;
use Laravel\Chisel\Script;
use RuntimeException;

use function Laravel\Prompts\multiselect;

class ChiselCommand extends Command
{
    protected $signature = 'chisel
        {--answers= : JSON object of module answers, e.g. \'{"modules":["notes"]}\'}
        {--dry-run : Show the chisel questions without applying any changes}';

    protected $description = 'Drop the reference modules this app does not need (files, routes, tests, dependencies)';

    public function handle(): int
    {
        /** @var Script $script */
        $script = require base_path('chisel.php');

        if ($this->option('dry-run')) {
            foreach ($script->questions() as $question) {
                $this->line($question->name.': '.implode(', ', array_keys($question->options)));
            }

            return self::SUCCESS;
        }

        $provided = (string) $this->option('answers');

        try {
            $answers = $script->collectAnswers()
                ->interactive($provided === '')
                ->withAnswers($provided === '' ? [] : $this->decodeAnswers($provided))
                ->onQuestion(fn (Question $question): mixed => $this->askQuestion($question))
                ->toArray();
        } catch (JsonException $exception) {
            $this->components->error('The --answers option must be a valid JSON object: '.$exception->getMessage());

            return self::FAILURE;
        }

        $script->chisel($answers);

        /** @var list<string> $kept */
        $kept = array_values(array_filter((array) ($answers['modules'] ?? []), is_string(...)));

        $this->components->info('Kept modules: '.($kept === [] ? '(none)' : implode(', ', $kept)));

        if ($this->dropGeneratedOutput($answers)) {
            $this->components->twoColumnDetail('Rebuilt generated helpers', 'resources/js/{actions,routes}');
        }

        foreach ($this->removeEmptyDirectories() as $directory) {
            $this->components->twoColumnDetail('Removed empty directory', $directory);
        }

        $this->formatChangedFiles();

        if (! is_dir(base_path('node_modules')) && array_diff(['notes', 'posts'], $kept) !== []) {
            $this->components->warn('node_modules is missing, so optional JS dependencies were left in package.json. Run composer run setup, then re-run this command.');
        }

        return self::SUCCESS;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws JsonException
     */
    private function decodeAnswers(string $answers): array
    {
        $decoded = json_decode($answers, true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($decoded)) {
            throw new RuntimeException('The --answers option must be a JSON object.');
        }

        /** @var array<string, mixed> $decoded */
        return $decoded;
    }

    private function askQuestion(Question $question): mixed
    {
        return match ($question->type) {
            'multiselect' => multiselect(
                label: $question->label,
                options: $question->options,
                default: $question->default ?? [],
                required: $question->required,
                hint: $question->hint,
            ),
        };
    }

    /**
     * Chisel edits files line-by-line, so the first gate after scaffolding
     * would start red without this: settle the project the way its own tooling
     * would — regenerate the typed route helpers a dropped route invalidated,
     * then run the formatters. All three are no-ops on a template that was
     * already clean.
     */
    private function formatChangedFiles(): void
    {
        // The typed route helpers the drop invalidated (a no-op regeneration on
        // a template that was already current).
        if (is_dir(base_path('vendor'))) {
            $wayfinder = Process::path(base_path())->run(['php', 'artisan', 'wayfinder:generate', '--with-form']);

            if (! $wayfinder->successful()) {
                $this->components->warn('Wayfinder generation failed — run `php artisan wayfinder:generate --with-form` before committing.');
            }
        }

        $pint = Process::path(base_path())->run(['vendor/bin/pint', '--format', 'agent']);

        if (! $pint->successful()) {
            $this->components->warn('Pint failed — run `composer run lint` before committing.');
        }

        if (! is_dir(base_path('node_modules'))) {
            return;
        }

        if (! Process::path(base_path())->run(['bun', 'run', 'format'])->successful()) {
            $this->components->warn('Prettier failed — run `bun run format` before committing.');
        }
    }

    /**
     * Wayfinder output belongs to the routes that produced it, and it is
     * gitignored — nothing else prunes it. A stale helper is an importable
     * route to a controller that no longer exists, so a drop rebuilds the whole
     * generated tree (Wayfinder does not clean up after its own subdirectories:
     * the barrels keep importing what it removed).
     *
     * @param  array<string, mixed>  $answers
     */
    private function dropGeneratedOutput(array $answers): bool
    {
        /** @var list<string> $kept */
        $kept = array_values(array_filter((array) ($answers['modules'] ?? []), is_string(...)));

        $dropped = false;

        foreach ($this->contract()['modules'] as $module => $definition) {
            if (in_array($module, $kept, true)) {
                continue;
            }

            foreach ($definition['generated'] ?? [] as $path) {
                if (file_exists(base_path($path))) {
                    $dropped = true;
                }
            }
        }

        if (! $dropped) {
            return false;
        }

        File::deleteDirectory(base_path('resources/js/actions'));
        File::deleteDirectory(base_path('resources/js/routes'));

        return true;
    }

    /**
     * @return array{modules: array<string, array{owned: list<string>, generated?: list<string>}>}
     */
    private function contract(): array
    {
        /** @var array{modules: array<string, array{owned: list<string>, generated?: list<string>}>} $contract */
        $contract = require base_path('chisel.modules.php');

        return $contract;
    }

    /**
     * Directories the optional modules own outright, removed once chiseling
     * emptied them. Derived from chisel.modules.php so the contract, the
     * removal and this cleanup can never disagree about what a module owns.
     *
     * @return list<string>
     */
    private function moduleDirectories(): array
    {
        $directories = [];

        foreach ($this->contract()['modules'] as $module) {
            foreach ($module['owned'] as $file) {
                $directory = dirname($file);

                if ($directory !== '.' && ! in_array($directory, $directories, true)) {
                    $directories[] = $directory;
                }
            }
        }

        return $directories;
    }

    /**
     * @return list<string>
     */
    private function removeEmptyDirectories(): array
    {
        $removed = [];

        foreach ($this->moduleDirectories() as $directory) {
            $path = base_path($directory);

            if (! is_dir($path)) {
                continue;
            }

            if ((new FilesystemIterator($path))->valid()) {
                continue;
            }

            rmdir($path);
            $removed[] = $directory;
        }

        return $removed;
    }
}
