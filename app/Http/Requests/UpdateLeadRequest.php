<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeadRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'           => ['sometimes', 'required', 'string', 'max:255'],
            'email'          => ['nullable', 'email', 'max:255'],
            'phone'          => ['nullable', 'string', 'max:30'],
            'company'        => ['nullable', 'string', 'max:255'],
            'source'         => ['nullable', 'string', 'in:Website,Referral,Google Ads,Facebook,Instagram,Manual,Cold Call,Other'],
            'status'         => ['nullable', 'string', 'in:New,Contacted,Qualified,Proposal,Negotiation,Won,Lost'],
            'priority'       => ['nullable', 'string', 'in:Low,Medium,High'],
            'value'          => ['nullable', 'numeric', 'min:0'],
            'address'        => ['nullable', 'string', 'max:500'],
            'service_type'   => ['nullable', 'string', 'max:255'],
            'follow_up_date' => ['nullable', 'date'],
            'notes'          => ['nullable', 'string'],
            'tags'           => ['nullable', 'array'],
            'tags.*'         => ['string', 'max:50'],
        ];
    }
}