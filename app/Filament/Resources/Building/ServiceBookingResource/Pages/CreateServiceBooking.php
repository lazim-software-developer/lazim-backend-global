<?php

namespace App\Filament\Resources\Building\ServiceBookingResource\Pages;

use App\Filament\Resources\Building\ServiceBookingResource;
use App\Models\Building\FacilityBooking;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateServiceBooking extends CreateRecord
{
    protected static string $resource = ServiceBookingResource::class;
    protected function afterCreate(){
        // $tenant=Filament::getTenant();
        // FacilityBooking::where('id', $this->record->id)
        //     ->update([
        //         'building_id'=>$tenant->first()->id
        //     ]);
        // $user= User::where('id',$this->record->user_id)->first()->first_name;
        FacilityBooking::where('id', $this->record->id)

            ->update([
                // 'approved_by'=>$this->record->user_id,
                'owner_association_id'=>auth()->user()->owner_association_id
            ]);

    }
}
