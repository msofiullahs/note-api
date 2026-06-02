<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates input for `POST /api/register`.
 *
 * `password_confirmation` must accompany `password` (Laravel's `confirmed`
 * rule). The `unique:users,email` rule rejects re-registration with an
 * email already in use.
 */
class RegisterRequest extends FormRequest
{
    /**
     * Always authorized — registration is a public endpoint.
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
