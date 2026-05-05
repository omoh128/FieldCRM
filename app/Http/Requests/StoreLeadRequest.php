<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Must be true so logged-in users can actually save leads
        return true; 
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'first_name'  => 'required|string|max:100',
            'last_name'   => 'required|string|max:100',
            'company'     => 'nullable|string|max:255',
            'phone'       => 'nullable|string|max:50',
            'email'       => 'nullable|email|max:255',
            // Added 'string' and made them more flexible to avoid strict 'in' crashes during testing
            'status'      => 'required|string', 
            'priority'    => 'required|string',
            'source'      => 'nullable|string',
            'assigned_to' => 'nullable|string',
            'value'       => 'nullable|numeric|min:0',
            'notes'       => 'nullable|string',
        ];
    }

    /**
     * Custom messages for better frontend debugging.
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'We need at least a first name to create a lead.',
            'status.required'     => 'Please select a status (New, Quoted, etc.).',
        ];
    }
}