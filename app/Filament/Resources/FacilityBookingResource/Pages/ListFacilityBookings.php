<?php

namespace App\Filament\Resources\FacilityBookingResource\Pages;

use App\Filament\Resources\FacilityBookingResource;
use App\Models\Building\Building;
use App\Models\Master\Role;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListFacilityBookings extends ListRecords
{
    protected static string $resource = FacilityBookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $buildings = Building::all()->where('owner_association_id',
            auth()->user()?->owner_association_id)->pluck('id')->toArray();
        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Property Manager') {
            return parent::getTableQuery()
                ->where('bookable_type', 'App\Models\WorkPermit')->whereIn('building_id', $buildings);
        }
        return parent::getTableQuery()->where('bookable_type', 'App\Models\WorkPermit');
    }
}
