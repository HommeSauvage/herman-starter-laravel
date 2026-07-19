<?php

namespace Database\Seeders;

use App\Models\Note;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::query()->where('email', 'test@example.com')->first()
            ?? User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $user->forceFill(['name' => 'Test User'])->save();

        $team = $user->currentTeam;

        // REFERENCE MODULE — demo notes for the quality-bar Notes feature.
        // Requires unique(team_id, title) — see create_notes_table migration.
        Note::upsert(
            [
                [
                    'team_id' => $team->id,
                    'user_id' => $user->id,
                    'title' => 'Welcome to Notes',
                    'body' => <<<'MD'
# Welcome

This is a **reference Notes module** shipped with the Herman Laravel starter.

Use it as a quality bar for list → detail → create/edit flows, then **delete the whole Notes feature** if your product does not need it.
MD,
                ],
                [
                    'team_id' => $team->id,
                    'user_id' => $user->id,
                    'title' => 'Markdown tips',
                    'body' => <<<'MD'
## Writing tips

- Use headings to structure longer notes
- Keep titles short and searchable
- Link related ideas with markdown links
MD,
                ],
                [
                    'team_id' => $team->id,
                    'user_id' => $user->id,
                    'title' => 'Empty states matter',
                    'body' => 'When the list is empty, guide the user toward creating their first note — never leave a blank void.',
                ],
            ],
            uniqueBy: ['team_id', 'title'],
            update: ['user_id', 'body'],
        );
    }
}
