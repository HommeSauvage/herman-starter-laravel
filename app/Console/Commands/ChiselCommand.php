<?php

declare(strict_types=1);

namespace App\Console\Commands;

use FilesystemIterator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use JsonException;
use Laravel\Chisel\Question;
use Laravel\Chisel\Script;
use RuntimeException;

use function Laravel\Prompts\multiselect;

class ChiselCommand extends Command
{
    /**
     * Directories the reference modules own. Removed when empty after chiseling.
     *
     * @var list<string>
     */
    private const array MODULE_DIRECTORIES = [
        'app/Http/Controllers/Notes',
        'app/Http/Controllers/Public',
        'app/Http/Requests/Notes',
        'app/Policies',
        'resources/js/components/notes',
        'resources/js/pages/notes',
        'resources/js/pages/public/posts',
        'tests/Feature/Notes',
        'tests/Feature/Posts',
    ];

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
     * would start red without this: run the repo's own formatters over the
     * result. Both are no-ops on a template that was already clean.
     */
    private function formatChangedFiles(): void
    {
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
     * @return list<string>
     */
    private function removeEmptyDirectories(): array
    {
        $removed = [];

        foreach (self::MODULE_DIRECTORIES as $directory) {
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
