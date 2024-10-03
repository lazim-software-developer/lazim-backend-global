<?php

namespace App\Jobs;

use App\Mail\ContractRenewalReminder;
use App\Models\SubContractor;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendContractRenewalReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Get today's date
        $today = Carbon::today();

        // Fetch all contracts nearing expiry within 60 days
        $subContracts = SubContractor::whereDate('end_date', '>=', $today)
            ->whereDate('end_date', '<=', $today->copy()->addDays(60))
            ->get();

        foreach ($subContracts as $contract) {
            $daysLeft = $today->diffInDays(Carbon::parse($contract->end_date), false);

            if ($daysLeft > 15 && $daysLeft <= 60) {
                // Weekly reminder
                if ($today->isSameDay($contract->last_reminded_at->addWeek())) {
                    // Send email
                    Mail::to($contract->email)->send(new ContractRenewalReminder($contract));
                    // Update reminder timestamp
                    $contract->update(['last_reminded_at' => $today]);
                }
            } elseif ($daysLeft <= 15) {
                // Daily reminder
                // Send email
                Mail::to($contract->email)->send(new ContractRenewalReminder($contract));
                // Update reminder timestamp
                $contract->update(['last_reminded_at' => $today]);
            }
        }
    }
}
