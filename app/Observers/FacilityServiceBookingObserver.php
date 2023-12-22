<?php

namespace App\Observers;

use App\Models\Building\Building;
use App\Models\Building\FacilityBooking;
use App\Models\Master\Facility;
use App\Models\Master\Service;
use App\Models\User\User;
use Filament\Notifications\Notification;

class FacilityServiceBookingObserver
{
    /**
     * Handle the FacilityBooking "created" event.
     */
    public function created(FacilityBooking $facilityBooking): void
    {
        $building = Building::where('id', $facilityBooking->building_id)->first();
        $notifyTo = User::where('owner_association_id',$building->owner_association_id)->where('role_id', 10)->get();
        if($facilityBooking->bookable_type == 'App\Models\Master\Facility'){
            $facilityName = Facility::where('id', $facilityBooking->bookable_id)->first();
            Notification::make()
            ->success()
            ->title("Facility Booking")
            ->icon('heroicon-o-document-text')
            ->iconColor('warning')
            ->body('A new '. $facilityName->name.' booking by '.auth()->user()->first_name)
            ->sendToDatabase($notifyTo);
        }
        else{
            $serviceName = Service::where('id', $facilityBooking->bookable_id)->first();
            Notification::make()
            ->success()
            ->title("Service Booking")
            ->icon('heroicon-o-document-text')
            ->iconColor('warning')
            ->body('A new '. $serviceName->name.' booking by '.auth()->user()->first_name)
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
