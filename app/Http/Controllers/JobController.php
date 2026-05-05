<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Job::forOwner($request->user()->id)
            ->with('lead:id,first_name,last_name,company');

        if ($search = $request->query('search'))   $query->search($search);
        if ($status = $request->query('status'))   $query->status($status);
        if ($leadId = $request->query('lead_id'))  $query->where('lead_id', $leadId);

        return response()->json(
            $query->latest()->paginate($request->query('per_page', 100))
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'lead_id'        => ['required', 'exists:leads,id'],
            'title'          => ['required', 'string', 'max:255'],
            'type'           => ['nullable', 'string', 'max:100'],
            'status'         => ['nullable', 'string', 'in:Scheduled,In Progress,Completed,Cancelled'],
            'assigned_to'    => ['nullable', 'string', 'max:255'],
            'scheduled_date' => ['nullable', 'date'],
            'due_date'       => ['nullable', 'date'],
            'value'          => ['nullable', 'numeric', 'min:0'],
            'progress'       => ['nullable', 'integer', 'min:0', 'max:100'],
            'address'        => ['nullable', 'string', 'max:500'],
            'notes'          => ['nullable', 'string'],
        ]);

        $lead = Lead::find($data['lead_id']);
        abort_if($lead->owner_id !== $request->user()->id, 403, 'Unauthorized.');
        abort_if(!$lead->isCustomer(), 422, 'You can only create jobs for customers. Convert the lead first.');

        $job = Job::create(array_merge($data, [
            'owner_id' => $request->user()->id,
            'status'   => $data['status'] ?? 'Scheduled',
        ]));

        return response()->json($job->load('lead:id,first_name,last_name,company'), 201);
    }

    public function show(Request $request, Job $job): JsonResponse
    {
        $this->authorise($request, $job);
        return response()->json($job->load('lead'));
    }

    public function update(Request $request, Job $job): JsonResponse
    {
        $this->authorise($request, $job);

        $data = $request->validate([
            'title'          => ['sometimes', 'required', 'string', 'max:255'],
            'type'           => ['nullable', 'string', 'max:100'],
            'status'         => ['nullable', 'string', 'in:Scheduled,In Progress,Completed,Cancelled'],
            'assigned_to'    => ['nullable', 'string', 'max:255'],
            'scheduled_date' => ['nullable', 'date'],
            'due_date'       => ['nullable', 'date'],
            'value'          => ['nullable', 'numeric', 'min:0'],
            'progress'       => ['nullable', 'integer', 'min:0', 'max:100'],
            'address'        => ['nullable', 'string', 'max:500'],
            'notes'          => ['nullable', 'string'],
        ]);

        $job->update($data);
        return response()->json($job->load('lead:id,first_name,last_name,company'));
    }

    public function destroy(Request $request, Job $job): JsonResponse
    {
        $this->authorise($request, $job);
        $job->delete();
        return response()->json(['message' => 'Job deleted.']);
    }

    public function kanban(Request $request): JsonResponse
    {
        $jobs = Job::forOwner($request->user()->id)
            ->with('lead:id,first_name,last_name,company')
            ->latest()->get()->groupBy('status');

        return response()->json([
            'Scheduled'   => $jobs->get('Scheduled',   collect()),
            'In Progress' => $jobs->get('In Progress', collect()),
            'Completed'   => $jobs->get('Completed',   collect()),
            'Cancelled'   => $jobs->get('Cancelled',   collect()),
        ]);
    }

    public function stats(Request $request): JsonResponse
    {
        $ownerId = $request->user()->id;
        return response()->json([
            'total'         => Job::forOwner($ownerId)->count(),
            'scheduled'     => Job::forOwner($ownerId)->status('Scheduled')->count(),
            'in_progress'   => Job::forOwner($ownerId)->status('In Progress')->count(),
            'completed'     => Job::forOwner($ownerId)->status('Completed')->count(),
            'total_revenue' => Job::forOwner($ownerId)->status('Completed')->sum('value'),
        ]);
    }

    private function authorise(Request $request, Job $job): void
    {
        abort_if($job->owner_id !== $request->user()->id, 403, 'Unauthorized.');
    }
}
