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
        $roles = Role::whereIn('name', ['Admin', 'Technician', 'Security', 'Tenant', 'Owner', 'Managing Director', 'Vendor','Staff','Facility Manager'])
            ->pluck('id');
        foreach ($ownerAssociationIds as $ownerAssociation) {
            $oa = OwnerAssociation::find($ownerAssociation);
            $flatexists = DB::table('property_manager_flats')
                ->where(['flat_id' => $userApproval->flat_id, 'active' => true, 'owner_association_id' => $oa->id])
                ->exists();
            if($oa->role == 'OA' && !$flatexists || ($oa->role == 'Property Manager' && $flatexists)){
                $notifyTo = User::where('owner_association_id', $oa->id)->whereNotIn('role_id', $roles)
                    ->whereNot('id', auth()->user()?->id)->distinct()->get()
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
                    ->url(function() use ($userApproval,$oa){
                        $slug = $oa?->slug;
                        if($slug){
                            return UserApprovalResource::getUrl('edit', [$slug,$userApproval?->id]);
                        }
                        return url('/app/user-approvals/' . $userApproval?->id.'/edit');
                    }),
                ])
                ->sendToDatabase($notifyTo);
            }
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
