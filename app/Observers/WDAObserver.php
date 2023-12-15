<?php

namespace App\Observers;

use App\Models\Accounting\WDA;
use App\Models\Building\Building;
use App\Models\User\User;
use Filament\Notifications\Notification;

class WDAObserver
{
    /**
     * Handle the WDA "created" event.
     */
    public function created(WDA $wDA): void
    {
        $building = Building::where('id', $wDA->building_id)->first();
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
     * Handle the WDA "updated" event.
     */
    public function updated(WDA $wDA): void
    {
        //
    }

    /**
     * Handle the WDA "deleted" event.
     */
    public function deleted(WDA $wDA): void
    {
        //
    }

    /**
     * Handle the WDA "restored" event.
     */
    public function restored(WDA $wDA): void
    {
        //
    }

    /**
     * Handle the WDA "force deleted" event.
     */
    public function forceDeleted(WDA $wDA): void
    {
        //
    }
}
