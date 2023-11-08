<?php

namespace App\Observers;

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
        $notifyTo = User::where('owner_association_id',$residentialForm->owner_association_id)->get();
        Notification::make()
        ->success()
        ->title("ResidentialForm created")
        ->icon('heroicon-o-document-text') 
        ->iconColor('warning') 
        ->body('New ResidentialForm has been created')
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
