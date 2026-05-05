<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'customer_id'      => ['nullable', 'exists:customers,id'],
            'title'            => ['required', 'string', 'max:255'],
            'type'             => ['nullable', 'string', 'max:100'],
            'status'           => ['nullable', 'string', 'in:Scheduled,In Progress,Completed,Cancelled'],
            'assigned_to'      => ['nullable', 'string', 'max:255'],
            'assigned_user_id' => ['nullable', 'exists:users,id'],
            'scheduled_date'   => ['nullable', 'date'],
            'due_date'         => ['nullable', 'date', 'after_or_equal:scheduled_date'],
            'value'            => ['nullable', 'numeric', 'min:0'],
            'progress'         => ['nullable', 'integer', 'min:0', 'max:100'],
            'address'          => ['nullable', 'string', 'max:500'],
            'notes'            => ['nullable', 'string'],
        ];
    }
}