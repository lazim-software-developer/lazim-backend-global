<?php

namespace App\Filament\Resources\Building\FacilityBookingResource\Pages;

use Filament\Actions;
use App\Models\Master\Role;
use App\Models\Building\Building;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\Building\FacilityBookingResource;

class ListFacilityBookings extends ListRecords
{
    protected static string $resource = FacilityBookingResource::class;
    protected function getTableQuery(): Builder
    {
        $buildings = Building::all()->where('owner_association_id',auth()->user()->owner_association_id)->pluck('id')->toArray();
        if(Role::where('id',auth()->user()->role_id)->first()->name != 'Admin') 
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
