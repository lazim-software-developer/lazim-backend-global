<?php

namespace App\Filament\Widgets;

use App\Models\Vendor\Vendor;
use App\Models\Accounting\Proposal;
use App\Models\Building\Building;
use App\Models\Building\FacilityBooking;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class FacilityBookingOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    protected function getStats(): array
    {
        $buildingIds = Building::all()->where('owner_association_id',auth()->user()->owner_association_id)->pluck('id')->toArray();
        return [
            Stat::make('Total Facility Booking', FacilityBooking::query()->whereIn('building_id', $buildingIds)->count())
                ->descriptionIcon('heroicon-s-user-group')
                ->chart([60, 92, 33, 80, 31, 98, 70])
                ->color('info'),
            Stat::make('Approved Facility Booking', FacilityBooking::query()->whereIn('building_id', $buildingIds)->where('approved',1)->count())
                ->descriptionIcon('heroicon-s-user-group')
                ->chart([60, 92, 33, 80, 31, 98, 70])
                ->color('success'),
            Stat::make('Not Approved Facility Booking', FacilityBooking::query()->whereIn('building_id', $buildingIds)->where('approved',0)->count())
                ->descriptionIcon('heroicon-s-user-group')
                ->chart([60, 92, 33, 80, 31, 98, 70])
                ->color('danger'),
            
        ];
    }
}
