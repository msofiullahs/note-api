<?php

namespace App\Policies;

use App\Models\Note;
use App\Models\User;

/**
 * Authorization rules for {@see Note} actions.
 *
 * The rule of the application is simple: each note is private to its
 * owner. `view`, `update`, and `delete` therefore all reduce to checking
 * `$user->id === $note->user_id`. Index/create are unconditionally
 * allowed for authenticated users because they cannot reference another
 * user's note.
 *
 * The policy is auto-discovered by Laravel via the model namespace
 * convention (App\Models\Note → App\Policies\NotePolicy) so no manual
 * registration is required.
 */
class NotePolicy
{
    /**
     * Any authenticated user may request their own listing.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Only the owner may view a specific note.
     */
    public function view(User $user, Note $note): bool
    {
        return $user->id === $note->user_id;
    }

    /**
     * Any authenticated user may create a note (it will be tied to them
     * automatically by the controller).
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Only the owner may update a note.
     */
    public function update(User $user, Note $note): bool
    {
        return $user->id === $note->user_id;
    }

    /**
     * Only the owner may (soft-)delete a note.
     */
    public function delete(User $user, Note $note): bool
    {
        return $user->id === $note->user_id;
    }
}
