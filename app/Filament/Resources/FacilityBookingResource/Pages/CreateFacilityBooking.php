<?php

namespace App\Filament\Resources\FacilityBookingResource\Pages;

use App\Filament\Resources\FacilityBookingResource;
use App\Models\Building\FacilityBooking;
use Filament\Resources\Pages\CreateRecord;

class CreateFacilityBooking extends CreateRecord
{
    protected static string $resource = FacilityBookingResource::class;

    protected function afterCreate()
    {
        
        FacilityBooking::where('id', $this->record->id)

            ->update([
                'approved_by'          => $this->record->user_id,
                'owner_association_id' => auth()->user()?->owner_association_id,
            ]);

    }
}
