<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Job;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Lead::forOwner($request->user()->id)
            ->customers()
            ->withCount('jobs');

        if ($search = $request->query('search')) {
            $query->search($search);
        }

        return response()->json(
            $query->latest()->paginate($request->query('per_page', 100))
        );
    }

    public function show(Request $request, Lead $customer): JsonResponse
    {
        abort_if($customer->owner_id !== $request->user()->id, 403, 'Unauthorized.');
        abort_if(!$customer->isCustomer(), 404, 'Not a customer.');

        return response()->json(
            $customer->load(['jobs' => fn($q) => $q->latest()])
        );
    }

    public function stats(Request $request): JsonResponse
    {
        $ownerId = $request->user()->id;

        return response()->json([
            'total'       => Lead::forOwner($ownerId)->customers()->count(),
            'total_value' => Lead::forOwner($ownerId)->customers()->sum('value'),
            'total_jobs'  => Job::forOwner($ownerId)->count(),
            'active_jobs' => Job::forOwner($ownerId)->status('In Progress')->count(),
        ]);
    }
}
