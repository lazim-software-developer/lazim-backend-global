<?php

namespace App\Observers;

use App\Models\Building\Building;
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
        $building = Building::where('id',$moveInOut->building_id )->first();
        $notifyTo = User::where('owner_association_id', $building->owner_association_id)->where('role_id', 10)->get();
        if($moveInOut->type == 'move-in'){
            Notification::make()
            ->success()
            ->title("New MoveIn Submission")
            ->icon('heroicon-o-document-text')
            ->iconColor('warning')
            ->body('New form submission by'.auth()->user()->first_name)
            ->sendToDatabase($notifyTo);
        }
        else{
            Notification::make()
            ->success()
            ->title("New MoveOut Submission")
            ->icon('heroicon-o-document-text')
            ->iconColor('warning')
            ->body('New form submission by'.auth()->user()->first_name)
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
