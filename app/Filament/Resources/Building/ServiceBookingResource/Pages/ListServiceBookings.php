<?php

namespace App\Filament\Resources\Building\ServiceBookingResource\Pages;

use App\Filament\Resources\Building\ServiceBookingResource;
use App\Models\Building\Building;
use App\Models\Master\Role;
use DB;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListServiceBookings extends ListRecords
{
    protected static string $resource = ServiceBookingResource::class;
    protected function getTableQuery(): Builder
    {
        $bookableType = 'App\Models\Master\Service';
        $pmBuildings = DB::table('building_owner_association')
            ->where('owner_association_id', auth()->user()?->owner_association_id)
            ->where('active', true)
            ->pluck('building_id');

        $buildings = Building::all()
            ->where('owner_association_id', auth()->user()?->owner_association_id)
            ->where('active', true)->pluck('id')->toArray();

        if (auth()->user()->role->name == 'Property Manager') {
            return parent::getTableQuery()
                ->where('bookable_type', $bookableType)
                ->whereIn('building_id', $pmBuildings);
        }

        if (Role::where('id', auth()->user()->role_id)->first()->name != 'Admin') {
            return parent::getTableQuery()
                ->where('bookable_type', $bookableType)
                ->whereIn('building_id', $buildings);
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
