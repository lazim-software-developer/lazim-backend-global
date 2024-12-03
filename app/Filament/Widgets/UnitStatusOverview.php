<?php

namespace App\Filament\Widgets;

use App\Models\Bill;
use App\Models\Forms\MoveInOut;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UnitStatusOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';
    protected static ?int $sort               = 1;

    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        $today = Carbon::today();

        $query = MoveInOut::query()
            ->whereHas('building', function ($query) {
                $query->where('owner_association_id', auth()->user()->owner_association_id)
                      ->whereHas('ownerAssociations', function ($query) {
                          $query->where('building_owner_association.owner_association_id', auth()->user()->owner_association_id)
                                ->where('building_owner_association.active', true);
                      });
            });

        $vacantUnits = (clone $query)
            ->where('type', 'move-out')
            ->where('moving_date', '<', $today)
            ->count();

            $upcomingUnits = (clone $query)
            ->where('type', 'move-in')
            ->where('moving_date', '>=', $today)
            ->count();

        $overdueBTUCount = Bill::where('type', 'BTU')
            ->where('status', 'Overdue')
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
