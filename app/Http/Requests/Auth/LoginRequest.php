<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates input for `POST /api/login`.
 *
 * Only enforces shape (a syntactically valid email + non-empty password).
 * The actual credential check happens in
 * {@see \App\Http\Controllers\Api\AuthController::login()}.
 */
class LoginRequest extends FormRequest
{
    /**
     * Always authorized — login is a public endpoint.
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
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }
}
