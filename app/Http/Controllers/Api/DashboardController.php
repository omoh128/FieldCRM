<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Job;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $ownerId = $request->user()->id;

        // Leads
        $totalLeads   = Lead::forOwner($ownerId)->where('status', '!=', 'Customer')->count();
        $wonLeads     = Lead::forOwner($ownerId)->status('Won')->count();
        $conversionRate = $totalLeads > 0 ? round(($wonLeads / $totalLeads) * 100) : 0;

        // Revenue
        $revenueWon   = Lead::forOwner($ownerId)->status('Won')->sum('value');
        $revenueJobs  = Invoice::forOwner($ownerId)->status('paid')->sum('paid_amount');

        // Jobs
        $jobsScheduled  = Job::forOwner($ownerId)->status('Scheduled')->count();
        $jobsInProgress = Job::forOwner($ownerId)->status('In Progress')->count();
        $jobsCompleted  = Job::forOwner($ownerId)->status('Completed')->count();

        // Customers
        $totalCustomers = Lead::forOwner($ownerId)->customers()->count();

        // Recent leads for table
        $recentLeads = Lead::forOwner($ownerId)
            ->where('status', '!=', 'Customer')
            ->latest()
            ->limit(7)
            ->get();

        // Pipeline counts
        $pipeline = [
            ['label' => 'New',    'count' => Lead::forOwner($ownerId)->status('New')->count(),    'color' => '#3b82f6'],
            ['label' => 'Quoted', 'count' => Lead::forOwner($ownerId)->status('Quoted')->count(), 'color' => '#f5a623'],
            ['label' => 'Won',    'count' => $wonLeads,                                            'color' => '#22c55e'],
            ['label' => 'Lost',   'count' => Lead::forOwner($ownerId)->status('Lost')->count(),   'color' => '#ef4444'],
        ];

        return response()->json([
            'kpis' => [
                'total_leads'      => $totalLeads,
                'revenue_won'      => $revenueWon,
                'revenue_invoiced' => $revenueJobs,
                'jobs_scheduled'   => $jobsScheduled + $jobsInProgress,
                'conversion_rate'  => $conversionRate,
                'total_customers'  => $totalCustomers,
            ],
            'jobs' => [
                'scheduled'   => $jobsScheduled,
                'in_progress' => $jobsInProgress,
                'completed'   => $jobsCompleted,
            ],
            'pipeline'     => $pipeline,
            'recent_leads' => $recentLeads,
        ]);
    }
}