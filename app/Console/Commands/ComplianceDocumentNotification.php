<?php

namespace App\Console\Commands;

use App\Mail\ComplianceDocumentReminder;
use App\Models\ComplianceDocument;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class ComplianceDocumentNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:compliance-document-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get today's date
        $today = Carbon::today();

        // Fetch all contracts nearing expiry within 60 days
        $complianceDocument = ComplianceDocument::whereDate('expiry_date', '>=', $today)
            ->whereDate('expiry_date', '<=', $today->copy()->addDays(30))
            ->get();

        foreach ($complianceDocument as $document) {
            $daysLeft = $today->diffInDays(Carbon::parse($document->expiry_date), false);

            if ($daysLeft > 7 && $daysLeft <= 30) {
                // Weekly reminder
                if ($today->isSameDay($document->last_reminded_at->addWeek())) {
                    // Send email
                    Mail::to($document->vendor->user->email)->send(new ComplianceDocumentReminder($document));
                    // Update reminder timestamp
                    $document->update(['last_reminded_at' => $today]);
                }
            } elseif ($daysLeft <= 7) {
                // Daily reminder
                // Send email
                Mail::to($document->vendor->user->email)->send(new ComplianceDocumentReminder($document));
                // Update reminder timestamp
                $document->update(['last_reminded_at' => $today]);
            }
        }

    }
}
