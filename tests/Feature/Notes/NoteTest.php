<?php

use App\Models\Note;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

/*
|--------------------------------------------------------------------------
| Notes — integration bar
|--------------------------------------------------------------------------
|
| The reference example for how a feature is tested here: drive the real HTTP
| stack (route → middleware → request → controller → Inertia) and assert what
| the user observes. One journey through the whole lifecycle, then the
| invariants that actually break: team scoping, authorization and validation.
|
| Copy this shape. See .agents/testing.md.
|
*/

test('guests are sent to login', function () {
    $team = User::factory()->create()->currentTeam;

    $this->get(route('notes.index', ['current_team' => $team]))
        ->assertRedirect(route('login'));
});

test('a member can take a note through its full lifecycle', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    // Open the create form.
    $this->get(route('notes.create'))->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('notes/create'));

    // Create.
    $this->post(route('notes.store'), ['title' => 'Q3 roadmap', 'body' => '## Goals'])
        ->assertRedirect();

    $note = Note::query()->sole();

    expect($note->team_id)->toBe($user->current_team_id)
        ->and($note->user_id)->toBe($user->id);

    // It shows up in the team index.
    $this->get(route('notes.index'))->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('notes/index')
            ->has('notes.data', 1)
            ->where('notes.data.0.title', 'Q3 roadmap'));

    // Read it, then open it for editing.
    $this->get(route('notes.show', $note))->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('notes/show')
            ->where('note.title', 'Q3 roadmap')
            ->where('note.body', '## Goals'));

    $this->get(route('notes.edit', $note))->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('notes/edit'));

    // Update…
    $this->put(route('notes.update', $note), ['title' => 'Q3 roadmap v2', 'body' => 'Updated'])
        ->assertRedirect(route('notes.show', $note));

    expect($note->fresh()->title)->toBe('Q3 roadmap v2');

    // …and delete, leaving the index empty.
    $this->delete(route('notes.destroy', $note))
        ->assertRedirect(route('notes.index'));

    $this->assertDatabaseMissing('notes', ['id' => $note->id]);

    $this->get(route('notes.index'))->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('notes.data', 0));
});

test('a note needs a title and a body', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('notes.store'), ['title' => '', 'body' => ''])
        ->assertSessionHasErrors(['title', 'body']);

    $this->assertDatabaseCount('notes', 0);
});

test('the index can be searched', function () {
    $user = User::factory()->create();

    Note::factory()->forTeam($user->currentTeam, $user)->create([
        'title' => 'Shipping checklist',
        'body' => 'Deploy steps',
    ]);
    Note::factory()->forTeam($user->currentTeam, $user)->create([
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
            ->where('filters.search', 'Shipping'));
});

test('the index only lists notes from the current team', function () {
    $user = User::factory()->create();
    Note::factory()->forTeam($user->currentTeam, $user)->create(['title' => 'Mine']);

    $stranger = User::factory()->create();
    Note::factory()->forTeam($stranger->currentTeam, $stranger)->create(['title' => 'Theirs']);

    $this->actingAs($user)
        ->get(route('notes.index', ['current_team' => $user->currentTeam]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('notes/index')
            ->has('notes.data', 1)
            ->where('notes.data.0.title', 'Mine'));
});

test('notes from another team are unreachable and unwritable', function () {
    $user = User::factory()->create();

    $stranger = User::factory()->create();
    $foreign = Note::factory()->forTeam($stranger->currentTeam, $stranger)->create(['title' => 'Secret']);

    $team = $user->currentTeam;

    $this->actingAs($user);

    $this->get(route('notes.show', ['current_team' => $team, 'note' => $foreign]))->assertNotFound();
    $this->get(route('notes.edit', ['current_team' => $team, 'note' => $foreign]))->assertNotFound();
    $this->delete(route('notes.destroy', ['current_team' => $team, 'note' => $foreign]))->assertNotFound();
    $this->put(route('notes.update', ['current_team' => $team, 'note' => $foreign]), [
        'title' => 'Hijacked',
        'body' => 'Hijacked',
    ])->assertForbidden();

    expect($foreign->fresh()->title)->toBe('Secret');
});

test('the index paginates notes', function () {
    $user = User::factory()->create();
    Note::factory()->forTeam($user->currentTeam, $user)->count(13)->create();

    $this->actingAs($user);

    $this->get(route('notes.index'))->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('notes.data', 12)
            ->where('notes.last_page', 2));

    $this->get(route('notes.index', ['page' => 2]))->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('notes.data', 1)
            ->where('notes.current_page', 2));
});
