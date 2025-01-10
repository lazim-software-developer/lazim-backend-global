<?php

namespace App\Observers;

use App\Filament\Resources\UserApprovalResource;
use App\Models\Master\Role;
use App\Models\OwnerAssociation;
use App\Models\User\User;
use App\Models\UserApproval;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class UserApprovalObserver
{
    /**
     * Handle the UserApproval "created" event.
     */
    public function created(UserApproval $userApproval): void
    {
        $requiredPermissions = ['view_any_user::approval'];
        $ownerAssociationIds = DB::table('building_owner_association')
            ->where(['building_id'=> $userApproval->flat?->building?->id, 'active' => true])
            ->pluck('owner_association_id');
        $roles = Role::whereIn('owner_association_id',$ownerAssociationIds)->whereIn('name', ['Admin', 'Technician', 'Security', 'Tenant', 'Owner', 'Managing Director', 'Vendor','Staff','Facility Manager'])->pluck('id');
        $notifyTo = User::whereIn('owner_association_id', $ownerAssociationIds)->whereNotIn('role_id', $roles)->whereNot('id', auth()->user()?->id)->get()
        ->filter(function ($notifyTo) use ($requiredPermissions) {
            return $notifyTo->can($requiredPermissions);
        });//MAKE AUTH USER ID IN USER WHERENOT-----------
        foreach ($ownerAssociationIds as $ownerAssociation) {
            Notification::make()
            ->success()
            ->title('New Resident Approval')
            ->body('New Resident Approval is Received')
            ->icon('heroicon-o-document-text')
            ->iconColor('warning')
            ->actions([
                Action::make('View')
                ->button()
                ->url(function() use ($userApproval,$ownerAssociation){
                    $slug = OwnerAssociation::where('id',$ownerAssociation)->first()?->slug;
                    if($slug){
                        return UserApprovalResource::getUrl('edit', [$slug,$userApproval?->id]);
                    }
                    return url('/app/user-approvals/' . $userApproval?->id.'/edit');
                }),
            ])
            ->sendToDatabase($notifyTo);
        }
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
