<?php

namespace App\Filament\Widgets;

use App\Models\Master\Role;
use App\Models\Vendor\Vendor;
use App\Models\Building\Building;
use App\Models\Accounting\Proposal;
use App\Models\Building\FacilityBooking;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Facades\Log;

class FacilityBookingOverview extends BaseWidget
{
    use InteractsWithPageFilters;
    
    protected static ?int $sort = 4;
    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $query = FacilityBooking::query()->where('owner_association_id', Filament::getTenant()->id);

        if ($startDate) {
            $startOfDay = Carbon::createFromFormat('Y-m-d', $startDate)->format('Y-m-d');
            $query->where('date', '>=', $startOfDay);
        }
        
        if ($endDate) {
            $endOfDay = Carbon::createFromFormat('Y-m-d', $endDate)->format('Y-m-d');
            $query->where('date', '<=', $endOfDay);
        }

        $approvedQuery = clone $query;
        $pendingQuery = clone $query;

        $approvedFacilityBookings = $approvedQuery->where('approved',true);
        $pendingFacilityBookings = $pendingQuery->where('approved',false);
        
            return [
                Stat::make('Total Facility Booking', FacilityBooking::where('owner_association_id', Filament::getTenant()->id)->count())
                    ->descriptionIcon('heroicon-s-user-group')
                    // ->chart([60, 92, 33, 80, 31, 98, 70])
                    ->color('info'),
                Stat::make('Approved Facility Booking', $approvedFacilityBookings->count())
                    ->descriptionIcon('heroicon-s-user-group')
                    // ->chart([60, 92, 33, 80, 31, 98, 70])
                    ->color('success'),
                Stat::make('Not Approved Facility Booking',$pendingFacilityBookings->count())
                    ->descriptionIcon('heroicon-s-user-group')
                    // ->chart([60, 92, 33, 80, 31, 98, 70])
                    ->color('danger'),

            ];
        
    }
}
