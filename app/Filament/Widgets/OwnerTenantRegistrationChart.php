<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\User\User;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class OwnerTenantRegistrationChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Owners and Tenants Over Time';
    protected static ?string $maxHeight = '400px';
    // protected static ?string $maxWidth = '100%';
    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? null;

        $year = $startDate ? Carbon::createFromFormat('Y-m-d', $startDate)->year : now()->year;
        $buildingId = $this->filters['building'] ?? null; // Using building filter from the dashboard

        // Initialize arrays for storing counts per month
        $months = [];
        $ownerCounts = [];
        $tenantCounts = [];

        // Loop through each month of the current year
        for ($month = 1; $month <= 12; $month++) {
            // Format the month for display
            $monthName = Carbon::create()->month($month)->format('M');
            $months[] = $monthName;

            // Count distinct owners created in this month
            $ownerCount = DB::table('flat_tenants')
                ->where('role', 'Owner')
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->when($buildingId, function ($query) use ($buildingId) {
                    return $query->where('building_id', $buildingId);
                })
                ->count();
            $ownerCounts[] = $ownerCount;

            // Count distinct tenants created in this month
            $tenantCount = DB::table('flat_tenants')
                ->where('role', 'Tenant')
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->when($buildingId, function ($query) use ($buildingId) {
                    return $query->where('building_id', $buildingId);
                })
                ->count();
            $tenantCounts[] = $tenantCount;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Owners',
                    'data' => $ownerCounts,
                    'borderColor' => '#4DB6AC',
                    'borderWidth' => 2,
                    'fill' => false,
                ],
                [
                    'label' => 'Tenants',
                    'data' => $tenantCounts,
                    'borderColor' => '#fd7e14',
                    'borderWidth' => 2,
                    'fill' => false,
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Month',
                    ],
                ],
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Count',
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
