<?php

namespace App\Observers;
use App\Models\Forms\MoveInOut;
use App\Models\User\User;
use Filament\Notifications\Notification;


class MoveInOutObserver
{
    /**
     * Handle the MoveInOut "created" event.
     */
    public function created(MoveInOut $moveInOut): void
    {
        $notifyTo = User::where('owner_association_id',$moveInOut->owner_association_id)->get();
        if($moveInOut->type == 'move-in'){
            Notification::make()
            ->success()
            ->title("MoveIn created")
            ->icon('heroicon-o-document-text')
            ->iconColor('warning')
            ->body('New MoveIn has been  registered')
            ->sendToDatabase($notifyTo);
        }
        else{
            Notification::make()
            ->success()
            ->title("MoveOut created")
            ->icon('heroicon-o-document-text')
            ->iconColor('warning')
            ->body('New MoveOut has been registered')
            ->sendToDatabase($notifyTo);
        }
    }

    /**
     * Handle the MoveInOut "updated" event.
     */
    public function updated(MoveInOut $moveInOut): void
    {
        //
    }

    /**
     * Handle the MoveInOut "deleted" event.
     */
    public function deleted(MoveInOut $moveInOut): void
    {
        //
    }

    /**
     * Handle the MoveInOut "restored" event.
     */
    public function restored(MoveInOut $moveInOut): void
    {
        //
    }

    /**
     * Handle the MoveInOut "force deleted" event.
     */
    public function forceDeleted(MoveInOut $moveInOut): void
    {
        //
    }
}
