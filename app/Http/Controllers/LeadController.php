<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Customer; // Added this
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Added this for transactions

class LeadController extends Controller
{
    /**
     * List the owner's leads (excludes customers).
     * Pass ?include_customers=1 to include them.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Lead::forOwner($request->user()->id);

        if (!$request->boolean('include_customers')) {
            $query->where('status', '!=', 'Customer');
        }

        if ($search = $request->query('search')) {
            $query->search($search);
        }
        if ($status = $request->query('status')) {
            $query->status($status);
        }
        if ($priority = $request->query('priority')) {
            $query->priority($priority);
        }

        return response()->json(
            $query->latest()->paginate($request->query('per_page', 100))
        );
    }

    /**
     * Create a new lead.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'first_name'  => ['required', 'string', 'max:100'],
            'last_name'   => ['required', 'string', 'max:100'],
            'email'       => ['nullable', 'email', 'max:255'],
            'phone'       => ['nullable', 'string', 'max:30'],
            'company'     => ['nullable', 'string', 'max:255'],
            'source'      => ['nullable', 'string', 'in:Referral,Website,Cold Call,Social Media,Walk-in'],
            'status'      => ['nullable', 'string', 'in:New,Quoted,Won,Lost'],
            'priority'    => ['nullable', 'string', 'in:High,Medium,Low'],
            'value'       => ['nullable', 'numeric', 'min:0'],
            'assigned_to' => ['nullable', 'string', 'max:255'],
            'notes'       => ['nullable', 'string'],
        ]);

        $lead = Lead::create(array_merge($data, [
            'owner_id' => $request->user()->id,
            'status'   => $data['status'] ?? 'New',
        ]));

        return response()->json($lead, 201);
    }

    /**
     * Show a single lead.
     */
    public function show(Request $request, Lead $lead): JsonResponse
    {
        $this->authorise($request, $lead);
        return response()->json($lead->load('jobs'));
    }

    /**
     * Update a lead.
     */
    public function update(Request $request, Lead $lead): JsonResponse
    {
        $this->authorise($request, $lead);

        $data = $request->validate([
            'first_name'  => ['sometimes', 'required', 'string', 'max:100'],
            'last_name'   => ['sometimes', 'required', 'string', 'max:100'],
            'email'       => ['nullable', 'email', 'max:255'],
            'phone'       => ['nullable', 'string', 'max:30'],
            'company'     => ['nullable', 'string', 'max:255'],
            'source'      => ['nullable', 'string', 'in:Referral,Website,Cold Call,Social Media,Walk-in'],
            'status'      => ['nullable', 'string', 'in:New,Quoted,Won,Lost,Customer'],
            'priority'    => ['nullable', 'string', 'in:High,Medium,Low'],
            'value'       => ['nullable', 'numeric', 'min:0'],
            'assigned_to' => ['nullable', 'string', 'max:255'],
            'notes'       => ['nullable', 'string'],
        ]);

        $lead->update($data);
        return response()->json($lead);
    }

    /**
     * Convert a lead to a customer.
     * Maps data to the new customers table and updates lead status.
     */
    public function convert(Request $request, Lead $lead): JsonResponse
    {
        $this->authorise($request, $lead);

        if ($lead->status === 'Customer') {
             return response()->json(['message' => 'Lead is already a customer.'], 422);
        }

        return DB::transaction(function () use ($lead) {
            // 1. Create the record in the dedicated customers table
            $customer = Customer::create([
                'user_id'        => $lead->owner_id,
                'first_name'     => $lead->first_name,
                'last_name'      => $lead->last_name,
                'email'          => $lead->email,
                'phone'          => $lead->phone,
                'company'        => $lead->company,
                'lifetime_value' => $lead->value ?? 0,
            ]);

            // 2. Mark the lead as 'Customer' so it moves out of the active lead lists
            $lead->update(['status' => 'Customer']);

            return response()->json([
                'message'  => "{$lead->first_name} has been successfully converted to a customer.",
                'customer' => $customer,
                'lead'     => $lead,
            ]);
        });
    }

    /**
     * Delete a lead.
     */
    public function destroy(Request $request, Lead $lead): JsonResponse
    {
        $this->authorise($request, $lead);
        $lead->delete();
        return response()->json(['message' => 'Lead deleted.']);
    }

    /**
     * Stats for the dashboard.
     */
    public function stats(Request $request): JsonResponse
    {
        $ownerId = $request->user()->id;

        return response()->json([
            'total'       => Lead::forOwner($ownerId)->where('status', '!=', 'Customer')->count(),
            'new'         => Lead::forOwner($ownerId)->status('New')->count(),
            'quoted'      => Lead::forOwner($ownerId)->status('Quoted')->count(),
            'won'         => Lead::forOwner($ownerId)->status('Won')->count(),
            'lost'        => Lead::forOwner($ownerId)->status('Lost')->count(),
            'customers'   => Lead::forOwner($ownerId)->customers()->count(),
            'total_value' => Lead::forOwner($ownerId)->sum('value'),
        ]);
    }

    // ─── Private ─────────────────────────────────────────────────────────────

    private function authorise(Request $request, Lead $lead): void
    {
        abort_if($lead->owner_id !== $request->user()->id, 403, 'Unauthorized.');
    }
}