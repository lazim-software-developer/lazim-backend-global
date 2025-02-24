<?php

namespace App\Console\Commands;

use App\Models\OwnerAssociationReceipt;
use App\Models\User\User;
use App\Notifications\OverdueReceiptNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckOverdueReceipts extends Command
{
    protected $signature = 'receipts:check-overdue';
    protected $description = 'Check for overdue receipts and send notifications';

    public function handle()
    {
        $today = Carbon::today();
        $overdueReceipts = OwnerAssociationReceipt::where('status', '!=', 'paid')
            ->where('date', '<=', $today)
            ->get();

        foreach ($overdueReceipts as $receipt) {
            $receipt->update(['status' => 'overdue']);

            $propertyManager = User::whereHas('role', function($query) {
                $query->where('name', 'Property Manager');
            })
            ->where('owner_association_id', $receipt->owner_association_id)
            ->first();

            if ($propertyManager) {
                try {
                    $propertyManager->notify(new OverdueReceiptNotification($receipt));
                } catch (\Exception $e) {
                    \Log::error('Failed to send overdue receipt notification: ' . $e->getMessage());
                }
            }
        }
    }
}
