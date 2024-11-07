<?php

namespace App\Observers;

use App\Filament\Resources\Building\BuildingResource;
use App\Filament\Resources\Building\BuildingResource\RelationManagers\ServiceBookingsRelationManager;
use App\Filament\Resources\Building\FacilityBookingResource;
use App\Filament\Resources\Building\ServiceBookingResource;
use App\Models\Building\Building;
use App\Models\Building\FacilityBooking;
use App\Models\Master\Facility;
use App\Models\Master\Role;
use App\Models\Master\Service;
use App\Models\OwnerAssociation;
use App\Models\User\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class FacilityServiceBookingObserver
{
    /**
     * Handle the FacilityBooking "created" event.
     */
    public function created(FacilityBooking $facilityBooking): void
    {   $requiredPermissions = ['view_any_contract'];
        $building = Building::where('id', $facilityBooking->building_id)->first();
        $oam_id = DB::table('building_owner_association')->where('building_id', $facilityBooking?->building_id)->where('active', true)->first();
        $roles = Role::where('owner_association_id',$building->owner_association_id)->whereIn('name', ['Admin', 'Technician', 'Security', 'Tenant', 'Owner', 'Managing Director', 'Vendor','Staff', 'Facility Manager'])->pluck('id');
        $notifyTo = User::where('owner_association_id',$building->owner_association_id)->whereNotIn('role_id', $roles)->whereNot('id', auth()->user()?->id)->get();
        if($facilityBooking->bookable_type == 'App\Models\Master\Facility'){
            $requiredPermissions = ['view_any_building::facility::booking'];
            $notifyTo->filter(function ($notifyTo) use ($requiredPermissions) {
                return $notifyTo->can($requiredPermissions);
            });
            $facilityName = Facility::where('id', $facilityBooking->bookable_id)->first();
            Notification::make()
            ->success()
            ->title("Amenity Booking")
            ->icon('heroicon-o-document-text')
            ->iconColor('warning')
            ->body('A new '. $facilityName->name.' booking by '.auth()->user()->first_name)
            ->actions([
                Action::make('view')
                    ->button()
                    ->url(function() use ($oam_id,$facilityBooking){
                        $slug = OwnerAssociation::where('id',$oam_id->owner_association_id)->first()?->slug;
                        if($slug){
                            return FacilityBookingResource::getUrl('edit', [$slug,$facilityBooking?->id]);
                        }
                        return url('/app/building/facility-bookings/' . $facilityBooking?->id.'/edit');
                    }),
            ])
            ->sendToDatabase($notifyTo);
        }
        if($facilityBooking->bookable_type == 'App\Models\Master\Service'){
            $requiredPermissions = ['view_any_building::service::booking'];
            $notifyTo->filter(function ($notifyTo) use ($requiredPermissions) {
                return $notifyTo->can($requiredPermissions);
            });
            $serviceName = Service::where('id', $facilityBooking->bookable_id)->first();
            Notification::make()
            ->success()
            ->title("Personal Service Booking")
            ->icon('heroicon-o-document-text')
            ->iconColor('warning')
            ->body('A new '. $serviceName->name.' booking by '.auth()->user()->first_name)
            ->actions([
                Action::make('view')
                    ->button()
                    ->url(function() use ($oam_id,$facilityBooking){
                        $slug = OwnerAssociation::where('id',$oam_id->owner_association_id)->first()?->slug;
                        if($slug){
                            return ServiceBookingResource::getUrl('edit', [$slug,$facilityBooking?->id]);
                        }
                        return url('/app/building/service-bookings/' . $facilityBooking?->id.'/edit');
                    }),
            ])
            ->sendToDatabase($notifyTo);
        }
    }

    /**
     * Handle the FacilityBooking "updated" event.
     */
    public function updated(FacilityBooking $facilityBooking): void
    {
        //
    }

    /**
     * Handle the FacilityBooking "deleted" event.
     */
    public function deleted(FacilityBooking $facilityBooking): void
    {
        //
    }

    /**
     * Handle the FacilityBooking "restored" event.
     */
    public function restored(FacilityBooking $facilityBooking): void
    {
        //
    }

    /**
     * Handle the FacilityBooking "force deleted" event.
     */
    public function forceDeleted(FacilityBooking $facilityBooking): void
    {
        //
    }
}
