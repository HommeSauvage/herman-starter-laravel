<?php

namespace Database\Factories;

use App\Models\Note;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * REFERENCE MODULE — delete with Notes when unused.
 *
 * @extends Factory<Note>
 */
class NoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'body' => '## '.fake()->sentence(3)."\n\n".fake()->paragraph()."\n\n".fake()->paragraph(),
        ];
    }

    /**
     * Ensure every note has a team and author that belong together.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (Note $note): void {
            if ($note->team_id && $note->user_id) {
                return;
            }

            $user = User::factory()->create();

            $note->team_id ??= $user->current_team_id;
            $note->user_id ??= $user->id;
        });
    }

    /**
     * Attach the note to an existing team and author.
     */
    public function forTeam(Team $team, ?User $author = null): static
    {
        return $this->state(fn () => [
            'team_id' => $team->id,
            'user_id' => $author->id ?? $team->owner()?->id,
        ]);
    }
}
