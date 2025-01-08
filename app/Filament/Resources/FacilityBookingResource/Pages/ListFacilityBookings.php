<?php

namespace App\Filament\Resources\FacilityBookingResource\Pages;

use App\Filament\Resources\FacilityBookingResource;
use App\Models\Master\Role;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

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
            ->where('active', true)
            ->pluck('building_id')->toArray();
        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Property Manager') {
            return parent::getTableQuery()
                ->where('bookable_type', 'App\Models\WorkPermit')->whereIn('building_id', $buildings);
        }
        if (Role::where('id', auth()->user()->role_id)->first()->name != 'Admin') {
            return parent::getTableQuery()
                ->where('bookable_type', 'App\Models\WorkPermit')
                ->whereIn('building_id', $buildings);
        }

        return parent::getTableQuery()
            ->where('bookable_type', 'App\Models\WorkPermit')
            ->latest()
            ->first();
    }
}
