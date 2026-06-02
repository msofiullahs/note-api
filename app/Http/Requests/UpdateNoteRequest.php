<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates input for `PUT|PATCH /api/notes/{note}`.
 *
 * Both fields are optional via `sometimes`, but when present they must
 * still be non-empty — this allows partial updates without permitting a
 * client to blank out a required column.
 */
class UpdateNoteRequest extends FormRequest
{
    /**
     * Ownership of the target note is enforced by
     * {@see \App\Policies\NotePolicy::update()} in the controller.
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
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'content' => ['sometimes', 'required', 'string'],
        ];
    }
}
