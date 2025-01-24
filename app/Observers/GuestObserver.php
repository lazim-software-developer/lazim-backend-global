<?php

namespace App\Observers;

use App\Filament\Resources\GuestRegistrationResource;
use App\Models\Building\Building;
use App\Models\Forms\Guest;
use App\Models\Master\Role;
use App\Models\OwnerAssociation;
use App\Models\User\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;


class GuestObserver
{
    /**
     * Handle the Guest "created" event.
     */
    public function created(Guest $guest): void
    {
        $requiredPermissions = ['view_any_guest::registration'];
        $roles = Role::whereIn('name', ['Admin', 'Technician', 'Security', 'Tenant', 'Owner', 'Managing Director', 'Vendor','Staff','Facility Manager'])->pluck('id');
        $oa_ids = DB::table('building_owner_association')->where(['building_id'=> $guest->flatVisitor->building_id, 'active'=> true])
            ->pluck('owner_association_id');
        $pm = OwnerAssociation::whereIn('id', $oa_ids)->where('role', 'Property Manager')->first();
        foreach ($oa_ids as $oa_id) {
            $oa = OwnerAssociation::find($oa_id);
            $flatexists = DB::table('property_manager_flats')
                ->where(['flat_id' => $guest->flatVisitor->flat_id, 'active' => true, 'owner_association_id'=> $oa->role == 'OA' ? $pm?->id : $oa->id])
                ->exists();
            if($oa->role == 'OA' && !$flatexists || ($oa->role == 'Property Manager' && $flatexists)){
                $notifyTo = User::where('owner_association_id', $oa->id)->whereNotIn('role_id', $roles)->whereNot('id', auth()->user()?->id)->get()
                ->filter(function ($notifyTo) use ($requiredPermissions) {
                    return $notifyTo->can($requiredPermissions);
                });
                Notification::make()
                ->success()
                ->title("New Guest Registration Form Submission")
                ->icon('heroicon-o-document-text')
                ->iconColor('warning')
                ->body('New form submission by '.auth()->user()->first_name)
                ->actions([
                    Action::make('view')
                        ->button()
                        ->url(function() use ($guest, $oa){
                            $slug = $oa?->slug;
                            if($slug){
                                return GuestRegistrationResource::getUrl('edit', [$slug,$guest?->id]);
                            }
                            return url('/app/guest-registrations/' . $guest?->id.'/edit');
                        }),
                ])
                ->sendToDatabase($notifyTo);
            }
        }
    }

    /**
     * Handle the Guest "updated" event.
     */
    public function updated(Guest $guest): void
    {
        //
    }

    /**
     * Handle the Guest "deleted" event.
     */
    public function deleted(Guest $guest): void
    {
        //
    }

    /**
     * Handle the Guest "restored" event.
     */
    public function restored(Guest $guest): void
    {
        //
    }

    /**
     * Handle the Guest "force deleted" event.
     */
    public function forceDeleted(Guest $guest): void
    {
        //
    }
}
