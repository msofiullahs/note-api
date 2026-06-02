<?php

namespace App\Models;

use Database\Factories\NoteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A user-owned note.
 *
 * Notes are scoped to their owning {@see User}. They are soft-deletable:
 * deletes set `deleted_at` instead of removing the row, and soft-deleted
 * notes are excluded from default queries (use `withTrashed()` to include
 * them).
 *
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $content
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read User $user
 */
#[Fillable(['title', 'content'])]
class Note extends Model
{
    /** @use HasFactory<NoteFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Owning user.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
