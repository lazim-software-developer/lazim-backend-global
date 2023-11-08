<?php

namespace App\Observers;
use App\Models\Building\Complaint;
use App\Models\User\User;
use Filament\Notifications\Notification;


class ComplaintObserver
{
    /**
     * Handle the Complaint "created" event.
     */
    public function created(Complaint $complaint): void
    {
        $notifyTo = User::where('owner_association_id',$complaint->owner_association_id)->get();
        if($complaint->complaint_type == 'tenant_complaint'){

            Notification::make()
                ->success()
                ->title("Complaint created")
                ->icon('heroicon-o-document-text') 
                ->iconColor('warning') 
                ->body('New Complaint has been created')
                ->sendToDatabase($notifyTo);
        }
        elseif ($complaint->complaint_type == 'enquiries') {
            Notification::make()
                ->success()
                ->title("Enquire created")
                ->icon('heroicon-o-document-text') 
                ->iconColor('warning') 
                ->body('New Enquire has been created')
                ->sendToDatabase($notifyTo);
        }
        elseif ($complaint->complaint_type == 'suggestions') {
            Notification::make()
                ->success()
                ->title("Suggestion created")
                ->icon('heroicon-o-document-text') 
                ->iconColor('warning') 
                ->body('New Suggestion has been created')
                ->sendToDatabase($notifyTo);
        }
        else{
            Notification::make()
                ->success()
                ->title("Complaint created")
                ->icon('heroicon-o-document-text') 
                ->iconColor('warning') 
                ->body('New Complaint has been created')
                ->sendToDatabase($notifyTo);
        }
    }

    /**
     * Handle the Complaint "updated" event.
     */
    public function updated(Complaint $complaint): void
    {
        //
    }

    /**
     * Handle the Complaint "deleted" event.
     */
    public function deleted(Complaint $complaint): void
    {
        //
    }

    /**
     * Handle the Complaint "restored" event.
     */
    public function restored(Complaint $complaint): void
    {
        //
    }

    /**
     * Handle the Complaint "force deleted" event.
     */
    public function forceDeleted(Complaint $complaint): void
    {
        //
    }
}
