<?php

namespace App\Filament\Widgets;

use App\Models\Building\Building;
use App\Models\Building\Flat;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BuildingOverview extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $buildings = Building::where('owner_association_id',Filament::getTenant()->id)->count();
        $flats = Flat::where('owner_association_id',Filament::getTenant()->id)->count();
        return [
            Stat::make('Buildings',$buildings)
            // ->description('Total Buildings')
            // ->descriptionIcon('heroicon-c-building-office-2')
            ->chart([60, 92, 33, 80, 31, 98, 70])
            ->color('info'),
            
            Stat::make('Flats',$flats)
            // ->description('Total Flats')
            // ->descriptionIcon('heroicon-s-home')
            ->chart([60, 92, 33, 80, 31, 98, 70])
            ->color('success'),

        ];
    }
}
