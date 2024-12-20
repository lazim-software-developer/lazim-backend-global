<?php

namespace App\Filament\Widgets;

use App\Models\Bill;
use App\Models\Building\Flat;
use App\Models\Forms\MoveInOut;
use Carbon\Carbon;
use DB;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class UnitStatusOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?string $pollingInterval = '30s';
    protected static ?int $sort               = 1;

    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        $today = Carbon::today();
        $buildingId = $this->filters['building'] ?? null;

        $query = MoveInOut::query()
            ->whereHas('building', function ($query) {
                $query->where('owner_association_id', auth()->user()->owner_association_id)
                    ->whereHas('ownerAssociations', function ($query) {
                        $query->where('building_owner_association.owner_association_id', auth()->user()->owner_association_id)
                            ->where('building_owner_association.active', true);
                    });
            });

        $buildingIds = DB::table('building_owner_association')
            ->where('owner_association_id', auth()->user()->owner_association_id)
            ->where('active', true)
            ->pluck('building_id');

        // Apply building filter if selected
        if ($buildingId) {
            $buildingIds = [$buildingId];
        }

        $vacantUnits = (clone $query)
            ->where('type', 'move-out')
            ->where('moving_date', '<', $today)
            ->whereIn('building_id', $buildingIds)
            ->count();

        $upcomingUnits = (clone $query)
            ->where('type', 'move-in')
            ->where('moving_date', '>=', $today)
            ->whereIn('building_id', $buildingIds)
            ->count();

        $flatIds = Flat::whereIn('building_id', $buildingIds)
            ->pluck('id');

        $overdueBTUCount = Bill::where('type', '=', 'BTU')
            ->where('status', '=', 'Overdue')
            ->whereIn('flat_id', $flatIds)
            ->count();

        return [
            Stat::make('Vacant Units', $vacantUnits)
                ->description('Total vacant units')
                ->descriptionIcon('heroicon-m-home')
                ->color('danger')
                ->url('/app/unit-list?type=vacant')
                ->chart([3, 5, 2, 4, 7, $vacantUnits]),

            Stat::make('Upcoming Units', $upcomingUnits)
                ->description('Total upcoming units')
                ->descriptionIcon('heroicon-m-home')
                ->color('success')
                ->url('/app/unit-list?type=upcoming')
                ->chart([2, 4, 6, 8, 3, $upcomingUnits]),

            Stat::make('Overdue BTU Bills', $overdueBTUCount)
                ->description('Total overdue BTU bills')
                ->color('danger')
                ->color('orange')
                ->chart([12, 22, 32, 42, 52])
                ->extraAttributes([
                    'style' => ' color: #1D4ED8; min-height: 150px; max-height: 150px;',
                ])
                ->url('/app/bills?tableFilters[status][value]=Overdue'),
        ];
    }
}
