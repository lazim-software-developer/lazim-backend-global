<?php

namespace App\Filament\Resources\Building\FacilityBookingResource\Pages;

use App\Filament\Resources\Building\FacilityBookingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListFacilityBookings extends ListRecords
{
    protected static string $resource = FacilityBookingResource::class;
    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->where('bookable_type','App\Models\Master\Facility');
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
