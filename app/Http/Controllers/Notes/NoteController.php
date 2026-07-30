<?php

namespace App\Http\Controllers\Notes;

use App\Http\Controllers\Controller;
use App\Http\Requests\Notes\StoreNoteRequest;
use App\Http\Requests\Notes\UpdateNoteRequest;
use App\Models\Note;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

/**
 * REFERENCE MODULE — polished Notes CRUD for Herman wizard quality bar.
 * Delete this controller (and related Note files) when Notes is unused.
 */
class NoteController extends Controller
{
    /**
     * Display a listing of the team's notes.
     */
    public function index(Request $request, Team $current_team): Response
    {
        Gate::authorize('viewAny', Note::class);

        $search = $request->string('search')->trim()->toString();

        $notes = Note::query()
            ->forTeam($current_team)
            ->with('user:id,name')
            ->search($search !== '' ? $search : null)
            ->latest()
            ->paginate(12)
            ->withQueryString()
            ->through(fn (Note $note) => [
                'id' => $note->id,
                'title' => $note->title,
                'excerpt' => str($note->body)->stripTags()->limit(140)->toString(),
                'author' => $note->user->name,
                'updated_at' => $note->updated_at?->toIso8601String(),
            ]);

        return Inertia::render('notes/index', [
            'notes' => $notes,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    /**
     * Show the form for creating a new note.
     */
    public function create(Team $current_team): Response
    {
        Gate::authorize('create', Note::class);

        return Inertia::render('notes/create');
    }

    /**
     * Store a newly created note.
     */
    public function store(StoreNoteRequest $request, Team $current_team): RedirectResponse
    {
        $note = Note::query()->create([
            ...$request->validated(),
            'team_id' => $current_team->id,
            'user_id' => $request->user()->id,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Note created.')]);

        return to_route('notes.show', [
            'current_team' => $current_team,
            'note' => $note,
        ]);
    }

    /**
     * Display the specified note.
     */
    public function show(Team $current_team, Note $note): Response
    {
        $this->ensureNoteBelongsToTeam($note, $current_team);
        Gate::authorize('view', $note);

        $note->loadMissing('user:id,name');

        return Inertia::render('notes/show', [
            'note' => [
                'id' => $note->id,
                'title' => $note->title,
                'body' => $note->body,
                'author' => $note->user->name,
                'created_at' => $note->created_at?->toIso8601String(),
                'updated_at' => $note->updated_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified note.
     */
    public function edit(Team $current_team, Note $note): Response
    {
        $this->ensureNoteBelongsToTeam($note, $current_team);
        Gate::authorize('update', $note);

        return Inertia::render('notes/edit', [
            'note' => [
                'id' => $note->id,
                'title' => $note->title,
                'body' => $note->body,
            ],
        ]);
    }

    /**
     * Update the specified note.
     */
    public function update(UpdateNoteRequest $request, Team $current_team, Note $note): RedirectResponse
    {
        $this->ensureNoteBelongsToTeam($note, $current_team);

        $note->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Note updated.')]);

        return to_route('notes.show', [
            'current_team' => $current_team,
            'note' => $note,
        ]);
    }

    /**
     * Remove the specified note.
     */
    public function destroy(Team $current_team, Note $note): RedirectResponse
    {
        $this->ensureNoteBelongsToTeam($note, $current_team);
        Gate::authorize('delete', $note);

        $note->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Note deleted.')]);

        return to_route('notes.index', ['current_team' => $current_team]);
    }

    /**
     * Abort when the note is not part of the current team.
     */
    protected function ensureNoteBelongsToTeam(Note $note, Team $team): void
    {
        abort_unless($note->team_id === $team->id, 404);
    }
}
