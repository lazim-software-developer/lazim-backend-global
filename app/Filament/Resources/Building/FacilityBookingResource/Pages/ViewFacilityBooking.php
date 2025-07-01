<?php

namespace App\Filament\Resources\Building\FacilityBookingResource\Pages;

use App\Filament\Resources\Building\FacilityBookingResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewFacilityBooking extends ViewRecord
{
    protected static string $resource = FacilityBookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
        ];
    }
}
