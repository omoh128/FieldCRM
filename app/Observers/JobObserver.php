<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Models\Job;

class JobObserver
{
    public function updated(Job $job): void
    {
        // Auto-create invoice when job is marked Completed
        if ($job->wasChanged('status') && $job->status === 'Completed') {
            // Only create if no invoice exists yet
            if (!Invoice::where('job_id', $job->id)->exists()) {
                Invoice::create([
                    'owner_id'       => $job->owner_id,
                    'job_id'         => $job->id,
                    'lead_id'        => $job->lead_id,
                    'invoice_number' => Invoice::generateNumber($job->owner_id),
                    'amount'         => $job->value,
                    'paid_amount'    => 0,
                    'status'         => 'unpaid',
                    'due_date'       => now()->addDays(30)->toDateString(),
                ]);
            }
        }
    }
}