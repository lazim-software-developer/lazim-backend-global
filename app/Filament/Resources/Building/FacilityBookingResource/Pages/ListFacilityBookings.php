<?php

namespace App\Filament\Resources\Building\FacilityBookingResource\Pages;

use App\Filament\Resources\Building\FacilityBookingResource;
use App\Models\Building\Building;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListFacilityBookings extends ListRecords
{
    protected static string $resource = FacilityBookingResource::class;
    protected function getTableQuery(): Builder
    {
        $buildings = Building::all()->where('owner_association_id',auth()->user()->owner_association_id)->pluck('id')->toArray();
        if(auth()->user()->id != 1) 
        {
            return parent::getTableQuery()->where('bookable_type','App\Models\Master\Facility')->whereIn('building_id',$buildings);
        }
        return parent::getTableQuery()->where('bookable_type','App\Models\Master\Facility');
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
