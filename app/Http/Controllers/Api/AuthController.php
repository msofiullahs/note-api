<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Authentication endpoints for the API.
 *
 * Issues Sanctum personal access tokens on register/login and revokes the
 * current token on logout. The `me` endpoint echoes the authenticated user.
 */
class AuthController extends Controller
{
    /**
     * Register a new user and return an API token.
     *
     * Route: `POST /api/register` (public)
     *
     * @return JsonResponse 201 with `{user, token}` on success.
     *                      Returns 422 if validation fails (email taken,
     *                      password mismatch, missing fields).
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    /**
     * Authenticate with email + password and return an API token.
     *
     * Route: `POST /api/login` (public)
     *
     * Invalid credentials are reported as a validation error (422) on the
     * `email` field rather than a generic 401, so existing client-side
     * validation handlers can display the message uniformly.
     *
     * @return JsonResponse 200 with `{user, token}` on success, 422 on
     *                      invalid credentials.
     *
     * @throws ValidationException When the email is unknown or the password
     *                             does not match.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Revoke the access token used for the current request.
     *
     * Route: `POST /api/logout` (auth:sanctum)
     *
     * Only the token presented in the `Authorization: Bearer …` header is
     * deleted. Other tokens issued to the same user remain valid.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out.']);
    }

    /**
     * Return the currently authenticated user.
     *
     * Route: `GET /api/me` (auth:sanctum)
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }
}
