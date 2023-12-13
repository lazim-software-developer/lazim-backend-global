<?php

namespace App\Observers;

use App\Models\Accounting\Invoice;
use App\Models\Building\Building;
use App\Models\User\User;
use Filament\Notifications\Notification;

class InvoiceObserver
{
    /**
     * Handle the Invoice "created" event.
     */
    public function created(Invoice $invoice): void
    {
        $building = Building::where('id', $invoice->building_id)->first();
        $notifyTo = User::where('owner_association_id', $building->owner_association_id)->where('role_id',10)->get();

            Notification::make()
            ->success()
            ->title("New WDA Form")
            ->icon('heroicon-o-document-text')
            ->iconColor('warning')
            ->body('New WDA form submitted by  '.auth()->user()->first_name)
            ->sendToDatabase($notifyTo);
    }

    /**
     * Handle the Invoice "updated" event.
     */
    public function updated(Invoice $invoice): void
    {
        //
    }

    /**
     * Handle the Invoice "deleted" event.
     */
    public function deleted(Invoice $invoice): void
    {
        //
    }

    /**
     * Handle the Invoice "restored" event.
     */
    public function restored(Invoice $invoice): void
    {
        //
    }

    /**
     * Handle the Invoice "force deleted" event.
     */
    public function forceDeleted(Invoice $invoice): void
    {
        //
    }
}
