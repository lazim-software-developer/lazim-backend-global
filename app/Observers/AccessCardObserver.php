<?php

namespace App\Observers;
use App\Models\Forms\AccessCard;
use App\Models\User\User;
use Filament\Notifications\Notification;


class AccessCardObserver
{
    /**
     * Handle the AccessCard "created" event.
     */
    public function created(AccessCard $accessCard): void
    {
        $notifyTo = User::where('owner_association_id',$accessCard->owner_association_id)->get();
        Notification::make()
        ->success()
        ->title("New AccessCard Submission")
        ->icon('heroicon-o-document-text')
        ->iconColor('warning')
        ->body('New form submission by'.auth()->user()->first_name)
        ->sendToDatabase($notifyTo);
    }

    /**
     * Handle the AccessCard "updated" event.
     */
    public function updated(AccessCard $accessCard): void
    {
        //
    }

    /**
     * Handle the AccessCard "deleted" event.
     */
    public function deleted(AccessCard $accessCard): void
    {
        //
    }

    /**
     * Handle the AccessCard "restored" event.
     */
    public function restored(AccessCard $accessCard): void
    {
        //
    }

    /**
     * Handle the AccessCard "force deleted" event.
     */
    public function forceDeleted(AccessCard $accessCard): void
    {
        //
    }
}
