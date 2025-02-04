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
use Illuminate\Support\Facades\Artisan;

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
        $subContracts = SubContractor::whereBetween('expiry_date', [$today,$today->copy()->addDays(30)])->with('services')
            ->get();

        foreach ($subContracts as $contract) {
            $daysLeft = $today->diffInDays(Carbon::parse($contract->end_date), false);

            if ($daysLeft > 15 && $daysLeft <= 60) {
                // Weekly reminder
                if ($contract->last_reminded_at === null || $today->isSameDay($contract->last_reminded_at->addWeek())) {
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
        Artisan::call('optimize:clear');
    }
}
