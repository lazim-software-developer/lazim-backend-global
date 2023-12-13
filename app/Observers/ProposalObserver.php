<?php

namespace App\Observers;

use App\Models\Accounting\Proposal;
use App\Models\Accounting\Tender;
use App\Models\Building\Building;
use App\Models\User\User;
use Filament\Notifications\Notification;

class ProposalObserver
{
    /**
     * Handle the Proposal "created" event.
     */
    public function created(Proposal $proposal): void
    {
        $tenders = Tender::where('id', $proposal->tender_id)->first();
        $building = Building::where('id', $tenders->building_id)->first();
        $notifyTo = User::where('owner_association_id', $building->owner_association_id)->where('role_id',10)->get();
            Notification::make()
            ->success()
            ->title("New Proposal")
            ->icon('heroicon-o-document-text')
            ->iconColor('warning')
            ->body('New proposal by '.auth()->user()->first_name)
            ->sendToDatabase($notifyTo);
    }

    /**
     * Handle the Proposal "updated" event.
     */
    public function updated(Proposal $proposal): void
    {
        //
    }

    /**
     * Handle the Proposal "deleted" event.
     */
    public function deleted(Proposal $proposal): void
    {
        //
    }

    /**
     * Handle the Proposal "restored" event.
     */
    public function restored(Proposal $proposal): void
    {
        //
    }

    /**
     * Handle the Proposal "force deleted" event.
     */
    public function forceDeleted(Proposal $proposal): void
    {
        //
    }
}
