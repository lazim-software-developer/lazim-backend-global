<?php

namespace App\Observers;
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
        $notifyTo = User::where('owner_association_id',$guest->owner_association_id)->get();
        Notification::make()
        ->success()
        ->title("Guest created")
        ->icon('heroicon-o-document-text') 
        ->iconColor('warning') 
        ->body('New Guest has been created')
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
