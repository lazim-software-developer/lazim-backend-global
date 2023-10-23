<?php

namespace App\Filament\Resources\Building\FacilityBookingResource\Pages;

use App\Filament\Resources\Building\FacilityBookingResource;
use App\Models\Building\FacilityBooking;
use App\Models\User\User;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateFacilityBooking extends CreateRecord
{
    protected static string $resource = FacilityBookingResource::class;
    protected function afterCreate(){
        // $tenant=Filament::getTenant();
        // FacilityBooking::where('id', $this->record->id)
        //     ->update([
        //         'building_id'=>$tenant->first()->id
        //     ]);
        // $user= User::where('id',$this->record->user_id)->first()->first_name;
        FacilityBooking::where('id', $this->record->id)

            ->update([
                'approved_by'=>$this->record->user_id
            ]);

    }
}
