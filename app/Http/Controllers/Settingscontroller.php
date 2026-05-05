<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSettingsRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class SettingsController extends Controller
{
    /**
     * Get the current user's full profile + preferences.
     */
    public function show(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    /**
     * Update profile fields and/or preferences.
     *
     * All sections are optional — send only what changed.
     */
    public function update(UpdateSettingsRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        // Handle password change separately
        if (! empty($data['new_password'])) {
            if (! Hash::check($data['current_password'], $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => ['Current password is incorrect.'],
                ]);
            }
            $data['password'] = Hash::make($data['new_password']);
        }

        // Remove password-change keys from general update
        unset($data['current_password'], $data['new_password'], $data['new_password_confirmation']);

        $user->update($data);

        return response()->json([
            'message' => 'Settings updated.',
            'user'    => $user->fresh(),
        ]);
    }

    /**
     * List team members (all non-deleted users — extend later for multi-tenant).
     */
    public function team(Request $request): JsonResponse
    {
        $members = User::where('id', '!=', $request->user()->id)
            ->select('id', 'name', 'email', 'role', 'avatar', 'created_at')
            ->latest()
            ->get();

        return response()->json($members);
    }

    /**
     * Remove a team member (admin/owner only).
     */
    public function removeTeamMember(Request $request, User $member): JsonResponse
    {
        abort_if(! $request->user()->isAdmin(), 403, 'Only admins can remove members.');
        abort_if($member->isOwner(), 403, 'Cannot remove the account owner.');

        $member->delete();

        return response()->json(['message' => 'Team member removed.']);
    }

    /**
     * Update a team member's role (admin/owner only).
     */
    public function updateTeamMemberRole(Request $request, User $member): JsonResponse
    {
        abort_if(! $request->user()->isAdmin(), 403, 'Only admins can change roles.');

        $request->validate([
            'role' => ['required', 'string', 'in:admin,member'],
        ]);

        $member->update(['role' => $request->role]);

        return response()->json(['message' => 'Role updated.', 'member' => $member]);
    }

    /**
     * Delete the authenticated user's own account.
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (! Hash::check($request->password, $request->user()->password)) {
            throw ValidationException::withMessages([
                'password' => ['Password confirmation failed.'],
            ]);
        }

        // Revoke all tokens first
        $request->user()->tokens()->delete();
        $request->user()->delete();

        return response()->json(['message' => 'Account deleted.']);
    }
}