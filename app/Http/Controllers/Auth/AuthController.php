<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Lead::forOwner($request->user()->id)
            ->where('status', '!=', 'Customer');

        if ($search = $request->query('search'))   $query->search($search);
        if ($status = $request->query('status'))   $query->status($status);
        if ($priority = $request->query('priority')) $query->priority($priority);

        return response()->json(
            $query->latest()->paginate($request->query('per_page', 100))
        );
    }

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

    public function show(Request $request, Lead $lead): JsonResponse
    {
        $this->authorise($request, $lead);
        return response()->json($lead->load('jobs'));
    }

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

    public function convert(Request $request, Lead $lead): JsonResponse
    {
        $this->authorise($request, $lead);
        abort_if($lead->isCustomer(), 422, 'Lead is already a customer.');

        $lead->update(['status' => 'Customer']);

        return response()->json([
            'message' => "{$lead->full_name} has been converted to a customer.",
            'lead'    => $lead,
        ]);
    }

    public function destroy(Request $request, Lead $lead): JsonResponse
    {
        $this->authorise($request, $lead);
        $lead->delete();
        return response()->json(['message' => 'Lead deleted.']);
    }

    public function stats(Request $request): JsonResponse
    {
        $ownerId = $request->user()->id;
        return response()->json([
            'total'     => Lead::forOwner($ownerId)->where('status', '!=', 'Customer')->count(),
            'new'       => Lead::forOwner($ownerId)->status('New')->count(),
            'quoted'    => Lead::forOwner($ownerId)->status('Quoted')->count(),
            'won'       => Lead::forOwner($ownerId)->status('Won')->count(),
            'lost'      => Lead::forOwner($ownerId)->status('Lost')->count(),
            'customers' => Lead::forOwner($ownerId)->customers()->count(),
        ]);
    }

    private function authorise(Request $request, Lead $lead): void
    {
        abort_if($lead->owner_id !== $request->user()->id, 403, 'Unauthorized.');
    }
}
