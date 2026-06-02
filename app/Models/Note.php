<?php

namespace App\Models;

use App\Http\Resources\NoteResource;
use App\Policies\NotePolicy;
use Database\Factories\NoteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Attributes\UseResource;
use Illuminate\Database\Eloquent\Builder;
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
 * Wiring is declared with Laravel 13 class attributes:
 *   - {@see Fillable}    mass-assignable columns
 *   - {@see UseFactory}  the factory class (skips namespace auto-discovery)
 *   - {@see UsePolicy}   the authorization policy (skips auto-discovery)
 *   - {@see UseResource} the default API resource for `$note->toResource()`
 *
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $content
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read User $user
 *
 * @method static Builder<static>|static whereTitleLike(string $term)
 */
#[Fillable(['title', 'content'])]
#[UseFactory(NoteFactory::class)]
#[UsePolicy(NotePolicy::class)]
#[UseResource(NoteResource::class)]
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

    /**
     * Filter notes whose title contains the given term (case-insensitive
     * on SQLite, case-folded on MySQL/Postgres by default).
     *
     * Marked with the {@see Scope} attribute so it can be called by its
     * bare name on a query builder, e.g. `Note::query()->whereTitleLike('foo')`,
     * instead of relying on the legacy `scopeWhereTitleLike` naming
     * convention.
     *
     * Empty/whitespace input is a no-op so callers can safely pass
     * unsanitised user input from a `?search=` query parameter.
     */
    #[Scope]
    protected function whereTitleLike(Builder $query, ?string $term): void
    {
        $term = trim((string) $term);

        if ($term === '') {
            return;
        }

        $query->where('title', 'like', '%'.$term.'%');
    }
}
