<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Job;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Return all KPIs and chart data for the main dashboard.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        return response()->json([
            'kpis'     => $this->kpis($userId),
            'pipeline' => $this->pipeline($userId),
            'charts'   => $this->charts($userId),
            'recent'   => $this->recentActivity($userId),
        ]);
    }

    // ─── Private helpers ─────────────────────────────────────────────────────

    private function kpis(int $userId): array
    {
        return [
            'total_leads'     => Lead::forUser($userId)->count(),
            'new_leads'       => Lead::forUser($userId)->status('New')->count(),
            'total_customers' => Customer::forUser($userId)->count(),
            'active_jobs'     => Job::forUser($userId)->status('In Progress')->count(),
            'monthly_revenue' => Job::forUser($userId)
                ->status('Completed')
                ->whereMonth('updated_at', now()->month)
                ->sum('value'),
            'total_revenue'   => Job::forUser($userId)
                ->status('Completed')
                ->sum('value'),
        ];
    }

    private function pipeline(int $userId): array
    {
        $statuses  = ['New', 'Contacted', 'Qualified', 'Proposal', 'Negotiation'];
        $pipeline  = [];

        foreach ($statuses as $status) {
            $pipeline[] = [
                'status' => $status,
                'count'  => Lead::forUser($userId)->status($status)->count(),
                'value'  => Lead::forUser($userId)->status($status)->sum('value'),
            ];
        }

        return $pipeline;
    }

    private function charts(int $userId): array
    {
        // Monthly revenue for the last 6 months
        $revenue = Job::forUser($userId)
            ->status('Completed')
            ->where('updated_at', '>=', now()->subMonths(6))
            ->selectRaw("DATE_FORMAT(updated_at, '%Y-%m') as month, SUM(value) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        // Leads by source
        $leadsBySource = Lead::forUser($userId)
            ->selectRaw('source, count(*) as count')
            ->groupBy('source')
            ->pluck('count', 'source');

        return [
            'monthly_revenue' => $revenue,
            'leads_by_source' => $leadsBySource,
        ];
    }

    private function recentActivity(int $userId): array
    {
        return [
            'leads'     => Lead::forUser($userId)->latest()->limit(5)->get(['id', 'name', 'status', 'value', 'created_at']),
            'jobs'      => Job::forUser($userId)->with('customer:id,name')->latest()->limit(5)->get(['id', 'title', 'status', 'value', 'customer_id', 'created_at']),
            'customers' => Customer::forUser($userId)->latest()->limit(5)->get(['id', 'name', 'status', 'lifetime_value', 'created_at']),
        ];
    }
}
