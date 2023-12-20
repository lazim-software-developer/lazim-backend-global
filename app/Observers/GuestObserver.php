<?php

namespace App\Observers;

use App\Models\Building\Building;
use App\Models\Forms\Guest;
use App\Models\User\User;
use Filament\Notifications\Notification;


class GuestObserver
{
    /**
     * Handle the Guest "created" event.
     */
    public function created(Guest $guest): void
    {
        $building = Building::where('id',$guest->building_id )->first();
        $notifyTo = User::where('owner_association_id', $building->owner_association_id)->where('role_id', 10)->get();
        Notification::make()
        ->success()
        ->title("New Guest registration form Submission")
        ->icon('heroicon-o-document-text')
        ->iconColor('warning')
        ->body('New form submission by '.auth()->user()->first_name)
        ->sendToDatabase($notifyTo);
    }

    /**
     * Handle the Guest "updated" event.
     */
    public function updated(Guest $guest): void
    {
        //
    }

    /**
     * Handle the Guest "deleted" event.
     */
    public function deleted(Guest $guest): void
    {
        //
    }

    /**
     * Handle the Guest "restored" event.
     */
    public function restored(Guest $guest): void
    {
        //
    }

    /**
     * Handle the Guest "force deleted" event.
     */
    public function forceDeleted(Guest $guest): void
    {
        //
    }
}
