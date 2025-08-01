<?php

namespace App\Observers;

use App\Models\User\User;
use App\Models\Master\Role;
use App\Models\Master\Service;
use App\Models\Master\Facility;
use App\Models\OwnerAssociation;
use App\Models\Building\Building;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Models\Building\FacilityBooking;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use App\Mail\NewServiceBookingNotification;
use App\Filament\Resources\Building\BuildingResource;
use App\Filament\Resources\Building\ServiceBookingResource;
use App\Filament\Resources\Building\FacilityBookingResource;
use App\Filament\Resources\Building\BuildingResource\RelationManagers\ServiceBookingsRelationManager;

class FacilityServiceBookingObserver
{
    /**
     * Handle the FacilityBooking "created" event.
     */
    public function created(FacilityBooking $facilityBooking): void
    {
       $requiredPermissions = ['view_any_contract'];
        $oam_ids = DB::table('building_owner_association')->where('building_id', $facilityBooking?->building_id)->where('active', true)->pluck('owner_association_id');
        $pm = OwnerAssociation::whereIn('id', $oam_ids)->where('role', 'Property Manager')->first();
        $roles = Role::whereIn('name', ['Admin', 'Technician', 'Security', 'Tenant', 'Owner', 'Managing Director', 'Vendor','Staff', 'Facility Manager'])->pluck('id');
        #TODO Make Necessary Changes
        // Send email notification
        if ($facilityBooking->bookable_type == 'App\Models\Master\Service') Mail::to('dev@ilaz.ae')->send(new NewServiceBookingNotification($facilityBooking));
        foreach ($oam_ids as $oam_id) {
            $oa = OwnerAssociation::find($oam_id);
            $flatexists = DB::table('property_manager_flats')
                ->where(['flat_id' => $facilityBooking?->flat_id, 'active' => true, 'owner_association_id' => $oa->role == 'OA' ? $pm?->id : $oa->id])
                ->exists();
            $notifyTo = User::where('owner_association_id',$oa->id)->whereNotIn('role_id', $roles)
                ->whereNot('id', auth()->user()?->id)->get();
            if($facilityBooking->bookable_type == 'App\Models\Master\Facility'){
                if($oa->role == 'OA' && !$flatexists || ($oa->role == 'Property Manager' && $flatexists)){
                    $requiredPermissions = ['view_any_building::facility::booking'];
                    $notifyTo->filter(function ($notifyTo) use ($requiredPermissions) {
                        return $notifyTo->can($requiredPermissions);
                    });
                    $facilityName = Facility::where('id', $facilityBooking->bookable_id)->first();
                    if($notifyTo->count() > 0){
                        foreach($notifyTo as $user){
                            if(!DB::table('notifications')->where('notifiable_id', $user->id)->where('custom_json_data->service_booking_id', $facilityBooking->id)->exists()){
                                $data=[];
                                $data['notifiable_type']='App\Models\User\User';
                                $data['notifiable_id']=$user->id;
                                $slug = $oa?->slug;
                                if($slug){
                                    $data['url']=FacilityBookingResource::getUrl('edit', [$slug,$facilityBooking?->id]);
                                }else{
                                    $data['url']=url('/app/building/facility-bookings/' . $facilityBooking?->id.'/edit');
                                }
                                $data['title']="Amenity Booking for Building:".$facilityBooking->building->name;
                                $data['body']='A new '. $facilityName->name.' booking by '.auth()->user()->first_name;
                                $data['building_id']=$facilityBooking->building_id;
                                $data['custom_json_data']=json_encode([
                                    'building_id' => $facilityBooking->building_id,
                                    'service_booking_id' => $facilityBooking->id,
                                    'user_id' => auth()->user()->id ?? null,
                                    'owner_association_id' => $oa->id,
                                    'type' => 'ServiceBooking',
                                    'priority' => 'Medium',
                                ]);
                                NotificationTable($data);
                            }
                        }
                    }
                    // Notification::make()
                    // ->success()
                    // ->title("Amenity Booking for Building:".$facilityBooking->building->name)
                    // ->icon('heroicon-o-document-text')
                    // ->iconColor('warning')
                    // ->body('A new '. $facilityName->name.' booking by '.auth()->user()->first_name)
                    // ->actions([
                    //     Action::make('view')
                    //         ->button()
                    //         ->url(function() use ($oa,$facilityBooking){
                    //             $slug = $oa?->slug;
                    //             if($slug){
                    //                 return FacilityBookingResource::getUrl('edit', [$slug,$facilityBooking?->id]);
                    //             }
                    //             return url('/app/building/facility-bookings/' . $facilityBooking?->id.'/edit');
                    //         }),
                    // ])
                    // ->sendToDatabase($notifyTo);
                }
            }
            if($facilityBooking->bookable_type == 'App\Models\Master\Service'){
                if($oa->role == 'OA' && !$flatexists || ($oa->role == 'Property Manager' && $flatexists)){
                    $requiredPermissions = ['view_any_building::service::booking'];
                    $notifyTo->filter(function ($notifyTo) use ($requiredPermissions) {
                        return $notifyTo->can($requiredPermissions);
                    });
                    $serviceName = Service::where('id', $facilityBooking->bookable_id)->first();
                    if($notifyTo->count() > 0){
                        foreach($notifyTo as $user){
                            if(!DB::table('notifications')->where('notifiable_id', $user->id)->where('custom_json_data->service_booking_id', $facilityBooking->id)->exists()){
                                $data=[];
                                $data['notifiable_type']='App\Models\User\User';
                                $data['notifiable_id']=$user->id;
                                $slug = $oa?->slug;
                                if($slug){
                                    $data['url']=ServiceBookingResource::getUrl('edit', [$slug,$facilityBooking?->id]);
                                }else{
                                    $data['url']=url('/app/building/service-bookings/' . $facilityBooking?->id.'/edit');
                                }
                                $data['title']="Personal Service Booking for Building :".$facilityBooking->building->name;
                                $data['body']='A new '. $serviceName->name.' booking by '.auth()->user()->first_name;
                                $data['building_id']=$facilityBooking->building_id;
                                $data['custom_json_data']=json_encode([
                                    'building_id' => $facilityBooking->building_id,
                                    'service_booking_id' => $facilityBooking->id,
                                    'user_id' => auth()->user()->id ?? null,
                                    'owner_association_id' => $oa->id,
                                    'type' => 'ServiceBooking',
                                    'priority' => 'Medium',
                                ]);
                                NotificationTable($data);
                            }
                        }
                    }
                    // Notification::make()
                    //     ->success()
                    //     ->title("Personal Service Booking for Building :".$facilityBooking->building->name)
                    //     ->icon('heroicon-o-document-text')
                    //     ->iconColor('warning')
                    //     ->body('A new '. $serviceName->name.' booking by '.auth()->user()->first_name)
                    //     ->actions([
                    //         Action::make('view')
                    //             ->button()
                    //             ->url(function() use ($oa,$facilityBooking){
                    //                 $slug = $oa?->slug;
                    //                 if($slug){
                    //                     return ServiceBookingResource::getUrl('edit', [$slug,$facilityBooking?->id]);
                    //                 }
                    //                 return url('/app/building/service-bookings/' . $facilityBooking?->id.'/edit');
                    //             }),
                    //     ])
                    //     ->sendToDatabase($notifyTo);
                }
            }
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
