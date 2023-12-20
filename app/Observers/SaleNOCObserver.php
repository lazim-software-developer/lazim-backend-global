<?php

namespace App\Observers;

use App\Models\Building\Building;
use App\Models\Forms\SaleNOC;
use App\Models\User\User;
use Filament\Notifications\Notification;


class SaleNOCObserver
{
    /**
     * Handle the SaleNOC "created" event.
     */
    public function created(SaleNOC $saleNOC): void
    {
        $building = Building::where('id',$saleNOC->building_id )->first();
        $notifyTo = User::where('owner_association_id', $building->owner_association_id)->where('role_id', 10)->get();
        Notification::make()
        ->success()
        ->title("New SaleNoc Submission")
        ->icon('heroicon-o-document-text')
        ->iconColor('warning')
        ->body('New form submission by'.auth()->user()->first_name)
        ->sendToDatabase($notifyTo);
    }

    /**
     * Handle the SaleNOC "updated" event.
     */
    public function updated(SaleNOC $saleNOC): void
    {
        //
    }

    /**
     * Handle the SaleNOC "deleted" event.
     */
    public function deleted(SaleNOC $saleNOC): void
    {
        //
    }

    /**
     * Handle the SaleNOC "restored" event.
     */
    public function restored(SaleNOC $saleNOC): void
    {
        //
    }

    /**
     * Handle the SaleNOC "force deleted" event.
     */
    public function forceDeleted(SaleNOC $saleNOC): void
    {
        //
    }
}
