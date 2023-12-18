<?php

namespace App\Observers;

use App\Models\User\User;
use Filament\Notifications\Notification;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $notifyTo = User::where('owner_association_id', $user->owner_association_id)->where('role_id',10)->get();
        if(auth()->user->role_id == 2){
            Notification::make()
            ->success()
            ->title("New Vendor")
            ->icon('heroicon-o-document-text')
            ->iconColor('warning')
            ->body('New vendor created '.$user->first_name)
            ->sendToDatabase($notifyTo);
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
