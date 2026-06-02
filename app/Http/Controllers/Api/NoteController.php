<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Http\Resources\NoteResource;
use App\Models\Note;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

/**
 * CRUD endpoints for notes owned by the authenticated user.
 *
 * Authorization is enforced two ways:
 *   1. `auth:sanctum` middleware rejects unauthenticated requests with 401.
 *   2. {@see \App\Policies\NotePolicy} ensures users can only act on notes
 *      they own — cross-user access returns 403.
 *
 * Listing endpoints are paginated and support a `search` filter on the
 * title. Deletes are soft (handled by the `SoftDeletes` trait on
 * {@see Note}).
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
     *   - `search` (string, optional)  Case-insensitive LIKE filter on title.
     *   - `per_page` (int, optional)   Page size, clamped to 1..100 (default 15).
     *   - `page` (int, optional)       Page number (default 1).
     *
     * @return AnonymousResourceCollection Paginated collection wrapped in
     *                                     `{data, links, meta}`.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min(max((int) $request->input('per_page', 15), 1), 100);

        $query = $request->user()->notes()->latest();

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where('title', 'like', '%'.$search.'%');
        }

        return NoteResource::collection($query->paginate($perPage));
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

        return (new NoteResource($note))->response()->setStatusCode(201);
    }

    /**
     * Show a single note.
     *
     * Route: `GET /api/notes/{note}` (auth:sanctum)
     *
     * @return NoteResource 200 with the note. 403 if the note belongs to
     *                      another user, 404 if it does not exist.
     */
    public function show(Note $note): NoteResource
    {
        $this->authorize('view', $note);

        return new NoteResource($note);
    }

    /**
     * Update fields on an existing note.
     *
     * Route: `PUT|PATCH /api/notes/{note}` (auth:sanctum)
     *
     * Both `title` and `content` are optional on update (validated with the
     * `sometimes` rule). Omitted fields are left untouched.
     *
     * @return NoteResource 200 with the updated note. 403 if owned by
     *                      another user, 404 if not found, 422 on invalid
     *                      input.
     */
    public function update(UpdateNoteRequest $request, Note $note): NoteResource
    {
        $this->authorize('update', $note);

        $note->update($request->validated());

        return new NoteResource($note);
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
