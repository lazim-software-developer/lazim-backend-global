<?php

namespace App\Observers;

use App\Models\Building\Building;
use App\Models\User\User;
use App\Models\Vendor\Contract;
use Filament\Notifications\Notification;

class ContractObserver
{
    /**
     * Handle the Contract "created" event.
     */
    public function created(Contract $contract): void
    {
        $building = Building::where('id', $contract->building_id)->first();
        $notifyTo = User::where('owner_association_id', $building->owner_association_id)->where('role_id',10)->get();
            Notification::make()
            ->success()
            ->title("New Contract")
            ->icon('heroicon-o-document-text')
            ->iconColor('warning')
            ->body('New contract is created')
            ->sendToDatabase($notifyTo);
    }

    /**
     * Handle the Contract "updated" event.
     */
    public function updated(Contract $contract): void
    {
        //
    }

    /**
     * Handle the Contract "deleted" event.
     */
    public function deleted(Contract $contract): void
    {
        //
    }

    /**
     * Handle the Contract "restored" event.
     */
    public function restored(Contract $contract): void
    {
        //
    }

    /**
     * Handle the Contract "force deleted" event.
     */
    public function forceDeleted(Contract $contract): void
    {
        //
    }
}
