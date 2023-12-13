<?php

namespace App\Observers;
use App\Models\Building\Complaint;
use App\Models\Master\Role;
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
                ->title("Happiness center Complaint")
                ->icon('heroicon-o-document-text')
                ->iconColor('warning')
                ->body(`Complaint has been created by `.auth()->user()->first_name)
                ->sendToDatabase($notifyTo);
        }
        elseif ($complaint->complaint_type == 'enquiries') {
            Notification::make()
                ->success()
                ->title("New Enquiry Received")
                ->icon('heroicon-o-document-text')
                ->iconColor('warning')
                ->body('A enquiry has been received raised by '.auth()->user()->first_name)
                ->sendToDatabase($notifyTo);
        }
        elseif ($complaint->complaint_type == 'suggestions') {
            Notification::make()
                ->success()
                ->title("New Suggestion Received")
                ->icon('heroicon-o-document-text')
                ->iconColor('warning')
                ->body('A suggestion made by '.auth()->user()->first_name)
                ->sendToDatabase($notifyTo);
        }
        else{
            Notification::make()
                ->success()
                ->title("Help Desk Ticket ")
                ->icon('heroicon-o-document-text')
                ->iconColor('warning')
                ->body('A new Ticket is raised by '.auth()->user()->first_name)
                ->sendToDatabase($notifyTo);
        }
    }

    /**
     * Handle the Complaint "updated" event.
     */
    public function updated(Complaint $complaint): void
    {
        $role = Role::where('id', auth()->user()->role_id)->first();
        $notifyTo = User::where('owner_association_id',$complaint->owner_association_id)->get();
        if ($complaint->complaint_type == 'help_desk') {
            Notification::make()
            ->success()
            ->title("Help Desk Complaint Resolution ")
            ->icon('heroicon-o-document-text')
            ->iconColor('warning')
            ->body('Complaint has been resolved by  a '.$role->name. ' '.auth()->user()->first_name)
            ->sendToDatabase($notifyTo);
        } else {
            Notification::make()
            ->success()
            ->title("Complaints Resolved")
            ->icon('heroicon-o-document-text')
            ->iconColor('warning')
            ->body('Complaint has been resolved by  a '.$role->name. ' '.auth()->user()->first_name)
            ->sendToDatabase($notifyTo);
        }


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
