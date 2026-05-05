<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    // ── Profile ───────────────────────────────────────────────────────────────

    public function getProfile(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'             => ['sometimes', 'required', 'string', 'max:255'],
            'email'            => ['sometimes', 'required', 'email', 'unique:users,email,' . $request->user()->id],
            'current_password' => ['required_with:password', 'string'],
            'password'         => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();

        if (isset($data['password'])) {
            abort_if(!Hash::check($data['current_password'], $user->password), 422, 'Current password is incorrect.');
            $data['password'] = Hash::make($data['password']);
        }

        unset($data['current_password']);
        $user->update($data);

        return response()->json($user->fresh());
    }

    // ── Preferences (key-value store) ─────────────────────────────────────────

    public function getPreferences(Request $request): JsonResponse
    {
        $prefs = $request->user()->settings()
            ->pluck('value', 'key');

        return response()->json($prefs);
    }

    public function updatePreferences(Request $request): JsonResponse
    {
        $data = $request->validate([
            'preferences' => ['required', 'array'],
        ]);

        $user = $request->user();

        foreach ($data['preferences'] as $key => $value) {
            $user->settings()->updateOrCreate(
                ['key' => $key],
                ['value' => is_array($value) ? json_encode($value) : $value]
            );
        }

        return response()->json(['message' => 'Preferences saved.']);
    }

    // ── Team members ──────────────────────────────────────────────────────────

    public function getTeam(Request $request): JsonResponse
    {
        return response()->json(
            TeamMember::forOwner($request->user()->id)->latest()->get()
        );
    }

    public function addTeamMember(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'   => ['required', 'string', 'max:255'],
            'email'  => ['nullable', 'email', 'max:255'],
            'role'   => ['nullable', 'string', 'in:technician,supervisor,manager'],
            'phone'  => ['nullable', 'string', 'max:30'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
        ]);

        $member = TeamMember::create(array_merge($data, [
            'owner_id' => $request->user()->id,
        ]));

        return response()->json($member, 201);
    }

    public function updateTeamMember(Request $request, TeamMember $member): JsonResponse
    {
        abort_if($member->owner_id !== $request->user()->id, 403, 'Unauthorized.');

        $data = $request->validate([
            'name'   => ['sometimes', 'required', 'string', 'max:255'],
            'email'  => ['nullable', 'email', 'max:255'],
            'role'   => ['nullable', 'string', 'in:technician,supervisor,manager'],
            'phone'  => ['nullable', 'string', 'max:30'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
        ]);

        $member->update($data);
        return response()->json($member);
    }

    public function deleteTeamMember(Request $request, TeamMember $member): JsonResponse
    {
        abort_if($member->owner_id !== $request->user()->id, 403, 'Unauthorized.');
        $member->delete();
        return response()->json(['message' => 'Team member removed.']);
    }
}