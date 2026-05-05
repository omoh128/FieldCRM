<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            // Profile
            'name'     => ['sometimes', 'required', 'string', 'max:255'],
            'email'    => ['sometimes', 'required', 'email', Rule::unique('users')->ignore($userId)],
            'phone'    => ['nullable', 'string', 'max:30'],
            'company'  => ['nullable', 'string', 'max:255'],
            'timezone' => ['nullable', 'string', 'timezone'],

            // Password change
            'current_password'      => ['required_with:new_password', 'string'],
            'new_password'          => ['nullable', 'string', 'min:8', 'confirmed'],

            // Preferences (JSON blobs)
            'notification_preferences' => ['nullable', 'array'],
            'appearance_preferences'   => ['nullable', 'array'],
        ];
    }
}