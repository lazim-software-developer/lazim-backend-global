<?php

namespace App\Filament\Resources\Building\ServiceBookingResource\Pages;

use Filament\Actions;
use App\Models\Building\Building;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\Building\ServiceBookingResource;

class ListServiceBookings extends ListRecords
{
    protected static string $resource = ServiceBookingResource::class;
    protected function getTableQuery(): Builder
    {
        $buildings = Building::all()->where('owner_association_id',auth()->user()->owner_association_id)->pluck('id')->toArray();
        if(auth()->user()->id != 1) 
        {
            return parent::getTableQuery()->where('bookable_type','App\Models\Master\Service')->whereIn('building_id',$buildings);
        }
        return parent::getTableQuery()->where('bookable_type','App\Models\Master\Service');
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
