<?php

namespace App\Observers;

use App\Filament\Resources\UserApprovalResource;
use App\Models\Master\Role;
use App\Models\OwnerAssociation;
use App\Models\User\User;
use App\Models\UserApproval;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;

class UserApprovalObserver
{
    /**
     * Handle the UserApproval "created" event.
     */
    public function created(UserApproval $userApproval): void
    {
        $requiredPermissions = ['view_any_user::approval'];
        $roles = Role::where('owner_association_id',$userApproval->owner_association_id)->whereIn('name', ['Admin', 'Technician', 'Security', 'Tenant', 'Owner', 'Managing Director', 'Vendor','Staff'])->pluck('id');
        $notifyTo = User::where('owner_association_id', $userApproval->owner_association_id)->whereNotIn('role_id', $roles)->whereNot('id', auth()->user()?->id)->get()
        ->filter(function ($notifyTo) use ($requiredPermissions) {
            return $notifyTo->can($requiredPermissions);
        });//MAKE AUTH USER ID IN USER WHERENOT-----------
        Notification::make()
        ->success()
        ->title('New Resident Approval')
        ->body('New Resident Approval is Received')
        ->icon('heroicon-o-document-text')
        ->iconColor('warning')
        ->actions([
            Action::make('View')
            ->button()
            ->url(fn () => UserApprovalResource::getUrl('edit', [OwnerAssociation::where('id',$userApproval->owner_association_id)->first()?->slug,$userApproval->id]))

        ])
        ->sendToDatabase($notifyTo);
    }

    /**
     * Handle the UserApproval "updated" event.
     */
    public function updated(UserApproval $userApproval): void
    {
        //
    }

    /**
     * Handle the UserApproval "deleted" event.
     */
    public function deleted(UserApproval $userApproval): void
    {
        //
    }

    /**
     * Handle the UserApproval "restored" event.
     */
    public function restored(UserApproval $userApproval): void
    {
        //
    }

    /**
     * Handle the UserApproval "force deleted" event.
     */
    public function forceDeleted(UserApproval $userApproval): void
    {
        //
    }
}
