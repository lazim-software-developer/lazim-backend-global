<?php

namespace App\Filament\Widgets;

use App\Models\Accounting\WDA;
use App\Models\Building\Building;
use App\Models\Building\Flat;
use App\Models\Building\FlatTenant;
use App\Models\FlatOwners;
use App\Models\MollakTenant;
use Filament\Facades\Filament;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class StatsOverview extends BaseWidget
{
    use InteractsWithPageFilters;
    protected static ?int $sort = 0;
    // protected int | string | array $columnSpan = 4;

    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        $buildingId = $this->filters['building'] ?? null;

        $buildings = Building::where('owner_association_id', Filament::getTenant()->id)->count();

        // Initialize the query for owners
        $ownerQuery = FlatTenant::where('role', 'Owner')
            ->where('owner_association_id', Filament::getTenant()->id);

        // Initialize the query for tenants
        $tenantQuery = FlatTenant::where('role', 'Tenant')
            ->where('owner_association_id', Filament::getTenant()->id);

        // Apply building filter if selected
        if ($buildingId) {
            $ownerQuery->where('building_id', $buildingId);
            $tenantQuery->where('building_id', $buildingId);
        }

        // Apply date filters if provided
        if ($startDate) {
            $startOfDay = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
            $ownerQuery->where('created_at', '>=', $startOfDay);
            $tenantQuery->where('created_at', '>=', $startOfDay);
        }

        if ($endDate) {
            $endOfDay = Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay();
            $ownerQuery->where('created_at', '<=', $endOfDay);
            $tenantQuery->where('created_at', '<=', $endOfDay);
        }

        // Get the counts
        $ownerCount = $ownerQuery->count();
        $tenantCount = $tenantQuery->count();

        $wdaQuery = WDA::query()
            ->where('owner_association_id', Filament::getTenant()->id)
            ->where('status', 'pending');


        if ($startDate) {
            $startOfDay = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
            $wdaQuery->where('created_at', '>=', $startOfDay);
        }

        if ($endDate) {
            $endOfDay = Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay();
            $wdaQuery->where('created_at', '<=', $endOfDay);
        }

        if ($buildingId) {
            $wdaQuery->where('building_id', $buildingId);
        }

        $wdaCount = $wdaQuery->count();

        return [
            Stat::make('Total Buildings', $buildings)
                ->description('Buildings under management')
                ->icon('heroicon-s-building-office-2')
                ->color('blue')
                ->chart([12, 22, 32, 42, 52])
                ->extraAttributes(['style' => 'background: linear-gradient(135deg, #E0F2FF, #90CDF4); color: #1D4ED8;']),

            Stat::make('Total Owners', $ownerCount)
                ->description('Registered Property Owners')
                ->icon('heroicon-o-user-group')
                ->color('green')
                ->chart([10, 30, 50, 70, 90])
                ->extraAttributes(['style' => 'background: linear-gradient(135deg, #E6F4EA, #A7F3D0); color: #10B981;']),

            Stat::make('Total Tenants', $tenantCount)
                ->description('Tenants currently occupying Flats')
                ->icon('heroicon-o-users')
                ->color('orange')
                ->chart([15, 25, 35, 45, 55])
                ->extraAttributes(['style' => 'background: linear-gradient(135deg, #FFF7E0, #FED7AA); color: #F97316;']),

            Stat::make('WDA', $wdaCount)
                ->description('Pending WDA')
                ->icon('heroicon-o-chart-bar-square')
                ->color('purple')
                ->chart([15, 25, 35, 45, 55])
                ->extraAttributes(['style' => 'background: linear-gradient(135deg, #EDE9FE, #C4B5FD); color: #8B5CF6;']),

        ];
    }
}
