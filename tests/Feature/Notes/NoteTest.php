<?php

use App\Models\Note;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests cannot visit notes', function () {
    $user = User::factory()->create();

    $this->get(route('notes.index'))->assertRedirect(route('login'));
});

test('team members can view the notes index', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;
    Note::factory()->forTeam($team, $user)->count(2)->create();

    $this->actingAs($user)
        ->get(route('notes.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('notes/index')
            ->has('notes.data', 2)
            ->where('filters.search', ''),
        );
});

test('notes index can be filtered by search', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;

    Note::factory()->forTeam($team, $user)->create([
        'title' => 'Shipping checklist',
        'body' => 'Deploy steps',
    ]);
    Note::factory()->forTeam($team, $user)->create([
        'title' => 'Unrelated',
        'body' => 'Something else',
    ]);

    $this->actingAs($user)
        ->get(route('notes.index', ['search' => 'Shipping']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('notes/index')
            ->has('notes.data', 1)
            ->where('notes.data.0.title', 'Shipping checklist')
            ->where('filters.search', 'Shipping'),
        );
});

test('team members can create a note', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('notes.store'), [
            'title' => 'Kickoff notes',
            'body' => '## Agenda\n\n- Goals',
        ])
        ->assertRedirect();

    $note = Note::query()->first();

    expect($note)->not->toBeNull()
        ->and($note->title)->toBe('Kickoff notes')
        ->and($note->team_id)->toBe($user->current_team_id)
        ->and($note->user_id)->toBe($user->id);

    $this->actingAs($user)
        ->get(route('notes.show', $note))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('notes/show')
            ->where('note.title', 'Kickoff notes'),
        );
});

test('team members can update a note', function () {
    $user = User::factory()->create();
    $note = Note::factory()->forTeam($user->currentTeam, $user)->create([
        'title' => 'Draft',
        'body' => 'Old body',
    ]);

    $this->actingAs($user)
        ->put(route('notes.update', $note), [
            'title' => 'Published',
            'body' => 'Updated body',
        ])
        ->assertRedirect(route('notes.show', $note));

    expect($note->fresh())
        ->title->toBe('Published')
        ->body->toBe('Updated body');
});

test('team members can delete a note', function () {
    $user = User::factory()->create();
    $note = Note::factory()->forTeam($user->currentTeam, $user)->create();

    $this->actingAs($user)
        ->delete(route('notes.destroy', $note))
        ->assertRedirect(route('notes.index'));

    $this->assertDatabaseMissing('notes', ['id' => $note->id]);
});

test('notes are scoped to the current team', function () {
    $user = User::factory()->create();
    $otherOwner = User::factory()->create();
    $foreignNote = Note::factory()
        ->forTeam($otherOwner->currentTeam, $otherOwner)
        ->create(['title' => 'Secret note']);

    // Pin current_team to the acting user's team so URL defaults from the
    // other factory user do not change which team slug is requested.
    $this->actingAs($user)
        ->get(route('notes.show', [
            'current_team' => $user->currentTeam,
            'note' => $foreignNote,
        ]))
        ->assertNotFound();
});

test('note validation requires title and body', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('notes.store'), [
            'title' => '',
            'body' => '',
        ])
        ->assertSessionHasErrors(['title', 'body']);
});
