<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * JSON shape for a {@see \App\Models\Note}.
 *
 * Only the fields safe to expose are returned. The owning `user_id` is
 * intentionally omitted: the API only ever serves the requester's own
 * notes, so the ownership column is redundant in responses and surfacing
 * it would leak an internal id.
 */
class NoteResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
