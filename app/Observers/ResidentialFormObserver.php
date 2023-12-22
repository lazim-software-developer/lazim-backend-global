<?php

namespace App\Observers;

use App\Models\Building\Building;
use App\Models\ResidentialForm;
use App\Models\User\User;
use Filament\Notifications\Notification;

class ResidentialFormObserver
{
    /**
     * Handle the ResidentialForm "created" event.
     */
    public function created(ResidentialForm $residentialForm): void
    {
        $notifyTo = User::where('owner_association_id', $residentialForm->owner_association_id)->where('role_id', 10)->get();
        Notification::make()
        ->success()
        ->title("New ResidentialForm Submission")
        ->icon('heroicon-o-document-text')
        ->iconColor('warning')
        ->body('New form submission by'.auth()->user()->first_name)
        ->sendToDatabase($notifyTo);
    }

    /**
     * Handle the ResidentialForm "updated" event.
     */
    public function updated(ResidentialForm $residentialForm): void
    {
        //
    }

    /**
     * Handle the ResidentialForm "deleted" event.
     */
    public function deleted(ResidentialForm $residentialForm): void
    {
        //
    }

    /**
     * Handle the ResidentialForm "restored" event.
     */
    public function restored(ResidentialForm $residentialForm): void
    {
        //
    }

    /**
     * Handle the ResidentialForm "force deleted" event.
     */
    public function forceDeleted(ResidentialForm $residentialForm): void
    {
        //
    }
}
