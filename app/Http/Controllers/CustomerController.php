<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Job;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // Use the scope we defined in the Model
        $query = Customer::forUser($request->user()->id);

        // Optional: Only include withCount if the column exists
        //$query->withCount('jobs');

        if ($search = $request->query('search')) {
            $query->search($search);
        }

        if ($status = $request->query('status')) {
            $query->status($status);
        }

        return response()->json(
            $query->latest()->paginate($request->query('per_page', 100))
        );
    }

    public function stats(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        return response()->json([
            'total'       => Customer::forUser($userId)->count(),
            'total_value' => Customer::forUser($userId)->sum('lifetime_value'),
            // Using a simple where for safety
            'total_jobs'  => Job::where('user_id', $userId)->count(),
            'active_jobs' => Job::where('user_id', $userId)->where('status', 'In Progress')->count(),
        ]);
    }
}