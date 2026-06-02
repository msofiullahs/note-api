<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates input for `POST /api/notes`.
 *
 * Both `title` and `content` are required on create. Ownership is set
 * implicitly to the authenticated user in the controller, so it is not
 * accepted as input here.
 */
class StoreNoteRequest extends FormRequest
{
    /**
     * Authorization is delegated to the route's auth:sanctum middleware
     * and {@see \App\Policies\NotePolicy::create()}; this request only
     * validates the payload shape.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ];
    }
}
