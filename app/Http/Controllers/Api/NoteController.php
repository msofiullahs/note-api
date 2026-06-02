<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Models\Note;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

/**
 * CRUD endpoints for notes owned by the authenticated user.
 *
 * Authorization is enforced two ways:
 *   1. `auth:sanctum` middleware rejects unauthenticated requests with 401.
 *   2. {@see \App\Policies\NotePolicy} ensures users can only act on notes
 *      they own — cross-user access returns 403. The policy is bound to
 *      the model via the {@see \Illuminate\Database\Eloquent\Attributes\UsePolicy}
 *      attribute on {@see Note}.
 *
 * The search filter on `index` is implemented as the `whereTitleLike`
 * scope on {@see Note} rather than inline, so the same filter is reusable
 * (and testable) from anywhere a builder is available. Resources are
 * resolved through `toResource()` / `toResourceCollection()`, which read
 * the `#[UseResource]` attribute on the model.
 */
class NoteController extends Controller implements HasMiddleware
{
    /**
     * Route middleware applied to every action on this controller.
     *
     * @return array<int, Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
        ];
    }

    /**
     * List notes belonging to the authenticated user.
     *
     * Route: `GET /api/notes` (auth:sanctum)
     *
     * Query parameters:
     *   - `search`   (string, optional) Case-insensitive LIKE filter on title.
     *   - `per_page` (int, optional)    Page size, clamped to 1..100 (default 15).
     *   - `page`     (int, optional)    Page number (default 1).
     *
     * @return ResourceCollection Paginated collection wrapped in
     *                            `{data, links, meta}`.
     */
    public function index(Request $request): ResourceCollection
    {
        $perPage = min(max((int) $request->input('per_page', 15), 1), 100);

        return $request->user()
            ->notes()
            ->latest()
            ->whereTitleLike((string) $request->input('search', ''))
            ->paginate($perPage)
            ->toResourceCollection();
    }

    /**
     * Create a new note owned by the authenticated user.
     *
     * Route: `POST /api/notes` (auth:sanctum)
     *
     * @return JsonResponse 201 with the created note resource,
     *                      422 if validation fails.
     */
    public function store(StoreNoteRequest $request): JsonResponse
    {
        $note = $request->user()->notes()->create($request->validated());

        return $note->toResource()->response()->setStatusCode(201);
    }

    /**
     * Show a single note.
     *
     * Route: `GET /api/notes/{note}` (auth:sanctum)
     *
     * The concrete resource type is {@see \App\Http\Resources\NoteResource},
     * resolved via the `#[UseResource]` attribute on {@see Note}.
     *
     * @return JsonResource 200 with the note. 403 if the note belongs to
     *                      another user, 404 if it does not exist.
     */
    public function show(Note $note): JsonResource
    {
        $this->authorize('view', $note);

        return $note->toResource();
    }

    /**
     * Update fields on an existing note.
     *
     * Route: `PUT|PATCH /api/notes/{note}` (auth:sanctum)
     *
     * Both `title` and `content` are optional on update (validated with the
     * `sometimes` rule). Omitted fields are left untouched.
     *
     * @return JsonResource 200 with the updated note. 403 if owned by
     *                      another user, 404 if not found, 422 on invalid
     *                      input.
     */
    public function update(UpdateNoteRequest $request, Note $note): JsonResource
    {
        $this->authorize('update', $note);

        $note->update($request->validated());

        return $note->toResource();
    }

    /**
     * Soft-delete a note.
     *
     * Route: `DELETE /api/notes/{note}` (auth:sanctum)
     *
     * The row is preserved with a `deleted_at` timestamp. Subsequent reads
     * via the public API will return 404 because the soft-deleted row is
     * filtered out of default queries.
     *
     * @return Response 204 No Content on success, 403 if owned by another
     *                  user, 404 if not found.
     */
    public function destroy(Note $note): Response
    {
        $this->authorize('delete', $note);

        $note->delete();

        return response()->noContent();
    }
}
