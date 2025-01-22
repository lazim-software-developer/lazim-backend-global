<?php
namespace App\Filament\Resources\Building\FacilityBookingResource\Pages;

use App\Filament\Resources\Building\FacilityBookingResource;
use App\Models\Building\Building;
use App\Models\Master\Role;
use DB;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListFacilityBookings extends ListRecords
{
    protected static string $resource = FacilityBookingResource::class;
    protected function getTableQuery(): Builder
    {
        $bookableType = 'App\Models\Master\Facility';

        $pmBuildings = DB::table('building_owner_association')
            ->where('owner_association_id', auth()->user()?->owner_association_id)
            ->where('active', true)
            ->pluck('building_id');

        $pmFlats = DB::table('property_manager_flats')
            ->where('owner_association_id', auth()->user()?->owner_association_id)
            ->where('active', true)
            ->pluck('flat_id')
            ->toArray();

        if (auth()->user()->role->name == 'Property Manager') {
            return parent::getTableQuery()
                ->where('bookable_type', $bookableType)
                ->whereIn('flat_id', $pmFlats);
        }

        if (Role::where('id', auth()->user()->role_id)->first()->name != 'Admin') {
            return parent::getTableQuery()->where('bookable_type', $bookableType)->whereIn('building_id', $pmBuildings);
        }
        return parent::getTableQuery()->where('bookable_type', $bookableType);
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
