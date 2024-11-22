<?php

namespace App\Filament\Resources\FacilityBookingResource\Pages;

use Filament\Actions;
use App\Models\Master\Role;
use App\Models\Building\Building;
use Illuminate\Support\Facades\DB;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\FacilityBookingResource;

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
        $buildings = DB::table('building_owner_association')
            ->where('owner_association_id', auth()->user()->owner_association_id)
            ->pluck('building_id')->toArray();
        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Property Manager') {
            return parent::getTableQuery()
                ->where('bookable_type', 'App\Models\WorkPermit')->whereIn('building_id', $buildings);
        }
        return parent::getTableQuery()->where('bookable_type', 'App\Models\WorkPermit');
    }
}
