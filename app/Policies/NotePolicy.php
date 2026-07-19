<?php

namespace App\Policies;

use App\Models\Note;
use App\Models\User;

/**
 * REFERENCE MODULE — delete with Notes when unused.
 */
class NotePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->currentTeam !== null
            && $user->belongsToTeam($user->currentTeam);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Note $note): bool
    {
        return $user->belongsToTeam($note->team);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->currentTeam !== null
            && $user->belongsToTeam($user->currentTeam);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Note $note): bool
    {
        return $user->belongsToTeam($note->team);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Note $note): bool
    {
        return $user->belongsToTeam($note->team);
    }
}
