<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * List all users with the 'owner' role.
     */
    public function listOwners(): JsonResponse
    {
        $owners = User::where('role', 'owner')->get();
        return response()->json($owners);
    }

    /**
     * Create a new owner.
     */
    public function createOwner(RegisterRequest $request): JsonResponse
    {
        // Reusing your register logic to ensure consistency
        return $this->register($request);
    }

    /**
     * Delete an owner.
     */
    public function deleteOwner(User $owner): JsonResponse
    {
        // Prevent an owner from deleting themselves
        if (Auth::id() === $owner->id) {
            return response()->json(['message' => 'You cannot delete your own account.'], 403);
        }

        $owner->delete();
        return response()->json(['message' => 'Owner deleted successfully.']);
    }

    /**
     * Register a new user and return a token.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'company'  => $request->company,
            'phone'    => $request->phone,
            'role'     => 'owner',
        ]);

        $token = $user->createToken('fieldcrm')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ], 201);
    }

    /**
     * Login and return a new token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        if (! Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user  = Auth::user();
        $token = $user->createToken('fieldcrm')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ]);
    }

    /**
     * Logout (revoke current token).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    /**
     * Return the authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }
}