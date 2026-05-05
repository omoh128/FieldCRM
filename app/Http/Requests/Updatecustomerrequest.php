<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'    => ['sometimes', 'required', 'string', 'max:255'],
            'email'   => ['nullable', 'email', 'max:255'],
            'phone'   => ['nullable', 'string', 'max:30'],
            'company' => ['nullable', 'string', 'max:255'],
            'status'  => ['nullable', 'string', 'in:Active,Inactive,VIP'],
            'type'    => ['nullable', 'string', 'in:Residential,Commercial,Industrial'],
            'address' => ['nullable', 'string', 'max:500'],
            'tags'    => ['nullable', 'array'],
            'tags.*'  => ['string', 'max:50'],
            'notes'   => ['nullable', 'string'],
        ];
    }
}