<?php

namespace App\Console\Commands;

use App\Models\OwnerAssociationInvoice;
use App\Models\User\User;
use App\Notifications\OverdueInvoiceNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckOverdueInvoices extends Command
{
    protected $signature = 'invoices:check-overdue';
    protected $description = 'Check for overdue invoices and send notifications';

    public function handle()
    {
        $this->info('Starting overdue invoice check...');

        $today = Carbon::today();

        $query = OwnerAssociationInvoice::where('status', '!=', 'paid')
            ->where('due_date', '<=', $today);


        $overdueInvoices = $query->get();


        if ($overdueInvoices->isEmpty()) {
            $this->info('No overdue invoices found');
            return;
        }

        $this->info("Found {$overdueInvoices->count()} overdue invoices");

        foreach ($overdueInvoices as $invoice) {
            $this->info("Processing invoice #{$invoice->invoice_number}");

            $invoice->update(['status' => 'overdue']);

            $propertyManager = User::whereHas('role', function($query) {
                $query->where('name', 'Property Manager');
            })
            ->where('owner_association_id', $invoice->owner_association_id)
            ->first();

            if ($propertyManager) {
                $this->info("Sending notification to property manager #{$propertyManager->id} ({$propertyManager->email})");
                try {
                    // Remove test mail connection since we'll verify with actual invoice
                    $propertyManager->notify(new OverdueInvoiceNotification($invoice));
                    $this->info('Notification sent successfully');
                } catch (\Exception $e) {
                    $this->error("Failed to send notification: {$e->getMessage()}");
                }
            } else {
                $this->warn("No property manager found for invoice #{$invoice->invoice_number}");
            }
        }

        $this->info('Overdue invoice check completed.');
    }
}
