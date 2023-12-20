<?php

namespace App\Observers;

use App\Models\Building\Building;
use App\Models\Forms\FitOutForm;
use App\Models\User\User;
use Filament\Notifications\Notification;


class FitOutFormObserver
{
    /**
     * Handle the FitOutForm "created" event.
     */
    public function created(FitOutForm $fitOutForm): void
    {
        $notifyTo = User::where('owner_association_id', $fitOutForm->owner_association_id)->where('role_id', 10)->get();
        Notification::make()
        ->success()
        ->title("New FitOut Form Submission")
        ->icon('heroicon-o-document-text')
        ->iconColor('warning')
        ->body('New form submission by '.auth()->user()->first_name)
        ->sendToDatabase($notifyTo);
    }

    /**
     * Handle the FitOutForm "updated" event.
     */
    public function updated(FitOutForm $fitOutForm): void
    {
        //
    }

    /**
     * Handle the FitOutForm "deleted" event.
     */
    public function deleted(FitOutForm $fitOutForm): void
    {
        //
    }

    /**
     * Handle the FitOutForm "restored" event.
     */
    public function restored(FitOutForm $fitOutForm): void
    {
        //
    }

    /**
     * Handle the FitOutForm "force deleted" event.
     */
    public function forceDeleted(FitOutForm $fitOutForm): void
    {
        //
    }
}
